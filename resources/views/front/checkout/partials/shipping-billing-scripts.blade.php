<script>
document.addEventListener('DOMContentLoaded', function () {
  const tribunoSyncUrl = @json(route('api.tribuno.subscription.sync'));
  const cartSummaryWrap = document.getElementById('checkout-cart-summary');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  let lastTribunoDniDigits = null;
  let tribunoSyncAbort = null;

  function extractDigits(value) {
    return String(value || '').replace(/\D/g, '');
  }

  function maybeSyncTribuno(rawValue) {
    const digits = extractDigits(rawValue);
    if (digits === lastTribunoDniDigits) return;
    if (digits !== '' && digits.length !== 8) return;

    lastTribunoDniDigits = digits;
    if (!tribunoSyncUrl || !csrfToken) return;

    if (tribunoSyncAbort) tribunoSyncAbort.abort();
    tribunoSyncAbort = new AbortController();

    if (cartSummaryWrap) cartSummaryWrap.style.opacity = '0.6';

    fetch(tribunoSyncUrl, {
      method: 'POST',
      credentials: 'same-origin',
      signal: tribunoSyncAbort.signal,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({ dni: rawValue || '' }),
    })
      .then(async (res) => {
        const contentType = res.headers.get('content-type') || '';
        const payload = contentType.includes('application/json')
          ? await res.json().catch(() => ({}))
          : { ok: false, message: await res.text().catch(() => '') };
        if (!res.ok || payload.ok !== true) throw payload;
        return payload;
      })
      .then((data) => {
        if (cartSummaryWrap && typeof data.html === 'string') {
          cartSummaryWrap.innerHTML = data.html;
        }
        document.dispatchEvent(new Event('cart:updated'));
      })
      .catch((err) => {
        if (err && err.name === 'AbortError') return;
        console.error('Tribuno sync error:', err);
      })
      .finally(() => {
        if (cartSummaryWrap) cartSummaryWrap.style.opacity = '';
      });
  }

  const modifyBillingToggle = document.getElementById('modify_billing');
  const billingSection = document.getElementById('billing-section');

  function setBillingOpen(isOpen) {
    if (!billingSection) return;
    billingSection.classList.toggle('d-none', !isOpen);

    billingSection.querySelectorAll('input, select, textarea').forEach(function (el) {
      el.disabled = !isOpen;
    });
  }

  if (modifyBillingToggle && billingSection) {
    setBillingOpen(!!modifyBillingToggle.checked);
    modifyBillingToggle.addEventListener('change', function () {
      setBillingOpen(!!this.checked);
    });
  }

  const provinceSelect = document.getElementById('province_id');
  const localitySelect = document.getElementById('locality_id');

  const hiddenProvinceInput = document.getElementById('province');
  const hiddenCityInput     = document.getElementById('city');

  const localitiesUrlTemplate = @json(url('/api/localities/__PROVINCE__'));

  const selectedProvinceId = provinceSelect && provinceSelect.dataset.selected ? parseInt(provinceSelect.dataset.selected, 10) : null;
  const selectedLocalityId = localitySelect && localitySelect.dataset.selected ? parseInt(localitySelect.dataset.selected, 10) : null;

  function loadLocalities(provinceId, callback) {
    if (!localitySelect) return;
    if (!provinceId) {
      localitySelect.innerHTML = '<option value="">Seleccioná una provincia primero</option>';
      if (hiddenProvinceInput) hiddenProvinceInput.value = '';
      if (hiddenCityInput) hiddenCityInput.value = '';
      return;
    }

    localitySelect.innerHTML = '<option value="">Cargando localidades...</option>';

    const url = localitiesUrlTemplate.replace('__PROVINCE__', encodeURIComponent(provinceId));
    fetch(url)
      .then(response => response.json())
      .then(data => {
        localitySelect.innerHTML = '<option value="">Seleccionar ciudad / localidad</option>';

        (data || []).forEach(function (loc) {
          const opt = document.createElement('option');
          opt.value = loc.id;
          opt.textContent = loc.name;
          localitySelect.appendChild(opt);
        });

        if (typeof callback === 'function') callback();
      })
      .catch(err => {
        console.error('Error cargando localidades', err);
        localitySelect.innerHTML = '<option value="">Error al cargar localidades</option>';
      });
  }

  if (provinceSelect) {
    provinceSelect.addEventListener('change', function () {
      const provId = this.value;
      const provText = this.options[this.selectedIndex]?.text || '';

      if (hiddenProvinceInput) hiddenProvinceInput.value = provText;

      if (hiddenCityInput) hiddenCityInput.value = '';
      loadLocalities(provId);
    });
  }

  if (localitySelect) {
    localitySelect.addEventListener('change', function () {
      const locText = this.options[this.selectedIndex]?.text || '';
      if (hiddenCityInput) hiddenCityInput.value = locText;
    });
  }

  // Inicialización
  const initialProvinceId = selectedProvinceId || (provinceSelect ? provinceSelect.value : null);
  if (provinceSelect && initialProvinceId) {
    provinceSelect.value = initialProvinceId;
    const provText = provinceSelect.options[provinceSelect.selectedIndex]?.text || '';
    if (hiddenProvinceInput) hiddenProvinceInput.value = provText;

    loadLocalities(initialProvinceId, function () {
      if (localitySelect && selectedLocalityId) {
        localitySelect.value = selectedLocalityId;
        const locText = localitySelect.options[localitySelect.selectedIndex]?.text || '';
        if (hiddenCityInput) hiddenCityInput.value = locText;
      }
    });
  }

  // DNI: formato NN.NNN.NNN
  const dniInputs = document.querySelectorAll('input.js-dni');
  const dniRegex = /^\d{2}\.\d{3}\.\d{3}$/;

  function formatDni(value) {
    const digits = String(value || '').replace(/\D/g, '').slice(0, 8);
    if (digits.length <= 2) return digits;
    if (digits.length <= 5) return digits.slice(0, 2) + '.' + digits.slice(2);
    return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5);
  }

  dniInputs.forEach(function (input) {
    function sync() {
      const formatted = formatDni(input.value);
      if (input.value !== formatted) input.value = formatted;
    }

    input.addEventListener('input', function () {
      input.setCustomValidity('');
      sync();
    });

    input.addEventListener('blur', function () {
      if (!input.value) {
        maybeSyncTribuno('');
        return;
      }
      if (!dniRegex.test(input.value)) {
        input.setCustomValidity('Ingresá el DNI con formato NN.NNN.NNN');
      } else {
        input.setCustomValidity('');
        maybeSyncTribuno(input.value);
      }
    });

    input.addEventListener('change', function () {
      if (!input.value) {
        maybeSyncTribuno('');
        return;
      }
      if (dniRegex.test(input.value)) {
        maybeSyncTribuno(input.value);
      }
    });

    input.addEventListener('invalid', function () {
      if (input.validity.patternMismatch) {
        input.setCustomValidity('Ingresá el DNI con formato NN.NNN.NNN');
      } else {
        input.setCustomValidity('');
      }
    });

    sync();
  });

  // CUIT/DNI (datos fiscales): DNI NN.NNN.NNN o CUIT NN-NNNNNNNN-N
  const fiscalDocInputs = document.querySelectorAll('input.js-fiscal-document');
  const fiscalDniRegex = /^\d{2}\.\d{3}\.\d{3}$/;
  const fiscalCuitRegex = /^\d{2}-\d{8}-\d$/;

  function formatFiscalDocument(value) {
    const digits = String(value || '').replace(/\D/g, '').slice(0, 11);

    // DNI (8 dígitos)
    if (digits.length <= 8) {
      if (digits.length <= 2) return digits;
      if (digits.length <= 5) return digits.slice(0, 2) + '.' + digits.slice(2);
      return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5);
    }

    // CUIT (11 dígitos)
    const a = digits.slice(0, 2);
    const b = digits.slice(2, 10);
    const c = digits.slice(10, 11);
    let out = a;
    if (digits.length > 2) out += '-' + b;
    if (digits.length === 11) out += '-' + c;
    return out;
  }

  fiscalDocInputs.forEach(function (input) {
    function sync() {
      const formatted = formatFiscalDocument(input.value);
      if (input.value !== formatted) input.value = formatted;
    }

    input.addEventListener('input', function () {
      input.setCustomValidity('');
      sync();
    });

    input.addEventListener('blur', function () {
      if (!input.value) return;
      if (!fiscalDniRegex.test(input.value) && !fiscalCuitRegex.test(input.value)) {
        input.setCustomValidity('Ingresá un DNI (NN.NNN.NNN) o CUIT (NN-NNNNNNNN-N)');
      } else {
        input.setCustomValidity('');
      }
    });

    input.addEventListener('invalid', function () {
      if (input.validity.patternMismatch) {
        input.setCustomValidity('Ingresá un DNI (NN.NNN.NNN) o CUIT (NN-NNNNNNNN-N)');
      } else {
        input.setCustomValidity('');
      }
    });

    sync();
  });
});
</script>
