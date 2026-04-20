<style>
  @import url('https://fonts.googleapis.com/css2?family=Playfair:ital,opsz,wght@0,5..1200,300..900;1,5..1200,300..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap');

  :root {
    --font-heading: "Work Sans", sans-serif;
    --font-highlight: "Playfair", serif;
    --font-body: "Roboto", sans-serif;
    --brand-black: #060606;
    --brand-gray: #808080;
    --brand-cream: #f8eedd;
    --brand-paper: #f7f2eb;
    --brand-muted: #f2f2f2;
    --brand-white: #ffffff;
    --brand-border: rgba(128, 128, 128, 0.35);
  }

  body,
  p,
  li,
  span,
  small,
  input,
  textarea,
  select,
  .form-control,
  .form-select,
  .table,
  .alert,
  .dropdown-menu,
  .offcanvas-body,
  .modal-body {
    font-family: var(--font-body);
    font-optical-sizing: auto;
    font-weight: 400;
    font-style: normal;
  }

  body {
    background: var(--brand-paper);
    color: var(--brand-black);
  }

  body.page-hero {
    background: var(--brand-paper);
  }

  body.page-hero header {
    position: absolute !important;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1040;
    border-bottom: 0 !important;
    background: transparent !important;
  }

  body.page-hero header .navbar {
    background: transparent !important;
    padding-top: 1rem;
    padding-bottom: 1rem;
  }

  body.page-hero header .navbar-brand img {
    filter: brightness(0) invert(1);
    max-width: 200px !important;
    height: auto;
  }

  body.page-hero header .nav-link,
  body.page-hero header .navbar-toggler,
  body.page-hero header .btn,
  body.page-hero header .dropdown-toggle {
    color: rgba(255, 255, 255, 0.92) !important;
  }

  body.page-hero header .nav-link.active,
  body.page-hero header .nav-link:hover,
  body.page-hero header .dropdown-item:hover {
    color: #fff !important;
  }

  body.page-hero header .navbar-toggler {
    border-color: rgba(255, 255, 255, 0.35);
  }

  body.page-hero header .navbar-toggler-icon {
    filter: invert(1);
  }

  body.page-hero header .btn-outline-secondary {
    border-color: rgba(255, 255, 255, 0.42);
    background: transparent;
  }

  body.page-hero header .btn-primary {
    border-color: rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.12);
  }

  body.page-hero header .dropdown-menu {
    background: rgba(16, 34, 40, 0.96);
    border: 1px solid rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(12px);
  }

  body.page-hero header .dropdown-item,
  body.page-hero header .btn-link {
    color: rgba(255, 255, 255, 0.92);
  }

  body.page-hero header .header-action-icon {
    filter: brightness(0) invert(1);
  }

  body.mobile-menu-open {
    overflow: hidden;
  }

  body.mobile-menu-open header,
  body.mobile-menu-open header .navbar {
    background: transparent !important;
    border-bottom: 0 !important;
  }

  body.mobile-menu-open header .navbar-brand img,
  body.mobile-menu-open header .header-action-icon {
    filter: brightness(0) invert(1);
  }

  .page-header-hero {
    position: relative;
    width: 100%;
    min-height: clamp(320px, 44vh, 460px);
    display: flex;
    align-items: flex-end;
    overflow: hidden;
    background-color: #102228;
    background-image:
      linear-gradient(180deg, rgba(9, 24, 28, 0.54) 0%, rgba(9, 24, 28, 0.26) 32%, rgba(9, 24, 28, 0.74) 100%),
      linear-gradient(90deg, rgba(9, 24, 28, 0.46) 0%, rgba(9, 24, 28, 0.08) 45%, rgba(9, 24, 28, 0.38) 100%),
      var(--page-header-bg-image);
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    color: #fff;
    margin-bottom: 2rem;
  }

  .page-header-hero-slider {
    background-image: none;
  }

  .page-header-hero-carousel {
    position: absolute;
    inset: 0;
    z-index: 0;
  }

  .page-header-hero-carousel .carousel-inner,
  .page-header-hero-carousel .carousel-item,
  .page-header-hero-slide {
    height: 100%;
  }

  .page-header-hero-slide {
    background-color: #102228;
    background-image:
      linear-gradient(180deg, rgba(9, 24, 28, 0.54) 0%, rgba(9, 24, 28, 0.26) 32%, rgba(9, 24, 28, 0.74) 100%),
      linear-gradient(90deg, rgba(9, 24, 28, 0.46) 0%, rgba(9, 24, 28, 0.08) 45%, rgba(9, 24, 28, 0.38) 100%),
      var(--page-header-bg-image);
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
  }

  .page-header-hero-inner {
    width: 100%;
    padding-top: 8rem;
    padding-bottom: 2.5rem;
    position: relative;
    z-index: 2;
  }

  .page-header-hero-breadcrumb {
    margin-bottom: 0;
  }

  .page-header-hero-breadcrumb .breadcrumb-item,
  .page-header-hero-breadcrumb .breadcrumb-item.active,
  .page-header-hero-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.74);
  }

  .page-header-hero-breadcrumb a {
    color: rgba(255, 255, 255, 0.88);
    text-decoration: none;
  }

  .page-header-hero-breadcrumb a:hover {
    color: #fff;
  }

  .page-header-hero-eyebrow {
    font-size: 0.78rem;
    letter-spacing: 0.26em;
    text-transform: uppercase;
    opacity: 0.84;
  }

  .page-header-hero-title {
    font-family: var(--font-heading);
    font-size: clamp(2.2rem, 4.4vw, 3.3rem);
    font-weight: 400;
    line-height: 1.02;
    color: #fff;
    text-transform: uppercase;
  }

  .page-header-hero-subtitle {
    max-width: 34rem;
    color: rgba(255, 255, 255, 0.84);
    font-size: 1rem;
  }

  .checkout-flow-section {
    width: 100%;
    position: relative;
    z-index: 2;
    margin-top: -4rem;
    padding-top: 5rem;
    padding-bottom: 4rem;
    background: var(--brand-paper);
    border-radius: 1.85rem 1.85rem 0 0;
    overflow: hidden;
  }

  .checkout-flow-shell {
    padding-top: clamp(0.3rem, 2vw, 0.85rem);
  }

  .about-section-stack {
    display: flex;
    flex-direction: column;
    gap: clamp(2.5rem, 5vw, 4rem);
  }

  .about-section-block {
    padding-bottom: clamp(2.4rem, 5vw, 3.5rem);
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  }

  .about-section-block:last-child {
    padding-bottom: 0;
    border-bottom: 0;
  }

  .contact-section-grid {
    display: grid;
    grid-template-columns: minmax(0, 0.92fr) minmax(320px, 1.08fr);
    gap: clamp(2.5rem, 5vw, 5rem);
    align-items: start;
  }

  .contact-copy-intro {
    max-width: 38rem;
  }

  .contact-copy-title,
  .about-section-title {
    font-size: 2.3rem;
    font-weight: 400;
    color: var(--brand-gray);
    letter-spacing: -1px;
  }

  .contact-copy-text {
    font-size: clamp(1.02rem, 1.55vw, 1.35rem);
    line-height: 1.38;
    color: rgba(0, 0, 0, 0.28);
  }

  .contact-form-card {
    margin-top: clamp(2rem, 4vw, 3rem);
    padding: clamp(1.3rem, 2.5vw, 1.9rem);
    background: rgba(248, 238, 221, 0.72);
    border-radius: 0.95rem;
  }

  .front-form-card {
    border: 0;
    border-radius: 0.95rem;
    background: rgba(248, 238, 221, 0.72);
    box-shadow: none !important;
  }

  .front-form-card .card-body {
    padding: clamp(1.3rem, 2.5vw, 1.9rem);
  }

  .front-form-card .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  }

  .front-form-card .alert {
    border: 0;
    border-radius: 0.55rem;
  }

  .front-form-card .list-group {
    display: grid;
    gap: 0.75rem;
  }

  .front-form-card .list-group-item {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 0.55rem !important;
    background: rgba(255, 255, 255, 0.82);
  }

  .front-form-card .list-group-item + .list-group-item {
    border-top-width: 1px;
  }

  .account-nav-menu {
    display: grid;
    gap: 0.75rem;
  }

  .account-nav-button {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    min-height: 4.15rem;
    padding: 1rem 1.5rem;
    text-align: left;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.2;
  }

  .account-nav-button.btn-outline-secondary {
    /* background: rgba(255, 255, 255, 0.86); */
  }

  .account-nav-button-active {
    box-shadow: 0 0.85rem 2rem rgba(0, 0, 0, 0.08);
  }

  .contact-form .form-label,
  main form .form-label {
    margin-bottom: 0.5rem;
    color: rgba(0, 0, 0, 0.56);
    font-size: 0.98rem;
    font-weight: 300;
  }

  .contact-form .form-control,
  main form .form-control:not(.product-showcase-quantity-input):not(.quantity-input),
  main form .form-select,
  main form .input-group-text {
    min-height: 4rem;
    border-radius: 0.55rem;
    border: 0;
    background: rgba(255, 255, 255, 0.94);
    color: rgba(0, 0, 0, 0.72);
    padding: 1rem 1.2rem;
    box-shadow: none;
  }

  .contact-form textarea.form-control,
  main form textarea.form-control {
    min-height: 10.5rem;
    resize: vertical;
  }

  .contact-form .form-control::placeholder,
  main form .form-control::placeholder {
    color: rgba(0, 0, 0, 0.28);
  }

  main form .form-control:focus,
  main form .form-select:focus {
    background: rgba(255, 255, 255, 1);
    border-color: transparent;
    box-shadow: 0 0 0 0.18rem rgba(0, 0, 0, 0.06);
  }

  .contact-map-frame {
    position: relative;
    min-height: clamp(420px, 52vw, 760px);
    border-radius: 1.5rem;
    overflow: hidden;
    background:
      linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.08)),
      var(--brand-muted);
  }

  .contact-map-iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
    filter: grayscale(1) saturate(0) contrast(0.96) brightness(1.05);
    opacity: 0.7;
  }

  .contact-map-card {
    position: absolute;
    left: 50%;
    bottom: clamp(1.2rem, 2vw, 2rem);
    transform: translateX(-50%);
    width: min(86%, 28rem);
    padding: 1.4rem 1.6rem;
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 1.1rem 2.8rem rgba(0, 0, 0, 0.08);
    text-align: center;
  }

  .contact-map-address {
    font-size: clamp(1.35rem, 2.2vw, 2rem);
    font-weight: 300;
    color: rgba(0, 0, 0, 0.42);
  }

  .contact-map-meta,
  .contact-map-meta a {
    color: rgba(0, 0, 0, 0.42);
    text-decoration: none;
  }

  .contact-map-meta a:hover {
    color: rgba(0, 0, 0, 0.62);
  }

  .contact-meta-list {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1.2rem;
  }

  .contact-meta-item {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    padding: 0.95rem 1rem;
    border-radius: 0.95rem;
    background: rgba(255, 255, 255, 0.38);
  }

  .contact-meta-label {
    font-family: var(--font-heading);
    font-size: 0.75rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    font-weight: 500;
    color: rgba(0, 0, 0, 0.45);
  }

  .contact-meta-item,
  .contact-meta-item a {
    color: rgba(0, 0, 0, 0.68);
    text-decoration: none;
  }

  .contact-meta-item a:hover {
    color: rgba(0, 0, 0, 0.88);
  }

  .about-section-row {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
    gap: clamp(2rem, 4vw, 4rem);
    align-items: start;
  }

  .about-section-row--text-only {
    grid-template-columns: minmax(0, 1fr);
  }

  .about-section-kicker {
    color: rgba(0, 0, 0, 0.5);
    font-size: 0.78rem;
    font-weight: 500;
    letter-spacing: 0.2em;
    text-transform: uppercase;
  }

  .about-section-subtitle {
    max-width: 44rem;
    color: rgba(0, 0, 0, 0.62);
    font-size: 1.05rem;
    line-height: 1.55;
  }

  .about-section-body {
    max-width: 54rem;
  }

  .about-section-media {
    overflow: hidden;
    border-radius: 1.55rem;
    background: #e8ddd0;
  }

  .about-section-image {
    width: 100%;
    aspect-ratio: 4 / 4.9;
    object-fit: cover;
    display: block;
  }

  .about-section-body p:last-child {
    margin-bottom: 0;
  }

  .product-showcase-section {
    width: 100%;
    position: relative;
    z-index: 2;
    margin-top: -4rem;
    padding-top: 2rem;
    padding-bottom: 3rem;
    background: var(--brand-paper);
    border-radius: 1.85rem 1.85rem 0 0;
    overflow: hidden;
  }

  .product-showcase-shell {
    padding-top: clamp(0.3rem, 2vw, 0.85rem);
  }

  .product-showcase-media {
    position: relative;
    overflow: hidden;
    border-radius: 1.35rem;
    background: #e9e0d5;
  }

  .product-gallery-image {
    width: 100%;
    aspect-ratio: 4 / 4.35;
    object-fit: cover;
  }

  .product-gallery-indicators {
    margin-bottom: 0.9rem;
    gap: 0.45rem;
  }

  .product-gallery-indicators [data-bs-target] {
    width: 0.5rem;
    height: 0.5rem;
    margin: 0;
    border: 0;
    border-radius: 999px;
    background-color: rgba(255, 255, 255, 0.55);
    opacity: 1;
  }

  .product-gallery-indicators .active {
    background-color: #fff;
  }

  .product-gallery-control {
    top: 50%;
    bottom: auto;
    width: 2.5rem;
    height: 2.5rem;
    margin-top: -1.25rem;
    border: 1px solid rgba(255, 255, 255, 0.72);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    opacity: 1;
  }

  .product-gallery-control.carousel-control-prev {
    left: 1rem;
  }

  .product-gallery-control.carousel-control-next {
    right: 1rem;
  }

  .product-gallery-control .carousel-control-prev-icon,
  .product-gallery-control .carousel-control-next-icon {
    width: 1rem;
    height: 1rem;
  }

  .product-showcase-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  .product-showcase-price-box {
    display: flex;
    flex-direction: column;
    gap: 0;
  }

  .product-showcase-price-kicker {
    color: var(--brand-gray);
    font-size: 0.85rem;
    font-weight: 400;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .product-showcase-price-compare {
    color: rgba(128, 128, 128, 0.8);
    font-size: 1rem;
    text-decoration: line-through;
  }

  .product-showcase-price-value {
    color: var(--brand-gray);
    font-family: var(--font-heading);
    font-size: clamp(2.2rem, 5vw, 3.25rem);
    font-weight: 300;
    letter-spacing: -0.03em;
    line-height: 0.95;
  }

  .product-showcase-price-value.is-placeholder {
    color: rgba(128, 128, 128, 0.92);
    font-size: clamp(1.75rem, 4vw, 2.3rem);
    line-height: 1.05;
  }

  .product-showcase-price-note {
    max-width: 28rem;
    color: rgba(0, 0, 0, 0.56);
    font-size: 0.92rem;
    line-height: 1.45;
  }

  .product-showcase-copy {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .product-showcase-title {
    font-size: clamp(1.7rem, 2.7vw, 2.35rem);
    font-weight: 400;
    line-height: 1.02;
    color: #595959;
  }

  .product-showcase-summary {
    max-width: 34rem;
    color: rgba(0, 0, 0, 0.38);
    font-size: 1rem;
    line-height: 1.35;
  }

  .product-showcase-meta {
    row-gap: 1rem;
  }

  .product-showcase-meta-label {
    display: block;
    margin-bottom: 0.25rem;
    color: rgba(0, 0, 0, 0.36);
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
  }

  .product-showcase-meta-value {
    display: block;
    color: #4c4c4c;
    font-size: 0.98rem;
    font-weight: 400;
  }

  .product-showcase-option-block {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .product-showcase-option-label {
    color: rgba(0, 0, 0, 0.46);
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
  }

  .product-showcase-swatches {
    gap: 0.7rem;
  }

  .product-showcase-swatches .attribute-swatch-chip {
    width: 2.25rem;
    height: 2.25rem;
    padding: 0;
    background: transparent;
    border-color: rgba(0, 0, 0, 0.1);
  }

  .product-showcase-swatches .attribute-swatch-dot {
    width: 100%;
    height: 100%;
    border: 0;
    box-shadow: none;
  }

  .product-option-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .product-option-pill {
    position: relative;
    display: inline-flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 8.25rem;
    padding: 0.75rem 0.95rem;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.54);
    cursor: pointer;
    transition: border-color 0.18s ease, background-color 0.18s ease, transform 0.18s ease;
  }

  .product-option-pill.is-disabled {
    opacity: 0.48;
    cursor: not-allowed;
  }

  .product-option-pill:has(.product-option-input:checked) {
    border-color: rgba(0, 0, 0, 0.25);
    background: rgba(255, 255, 255, 0.88);
    transform: translateY(-1px);
  }

  .product-option-pill:has(.product-option-input:focus-visible) {
    box-shadow: 0 0 0 0.18rem rgba(16, 34, 40, 0.12);
  }

  .product-option-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    pointer-events: none;
  }

  .product-option-pill-label {
    color: #3f3f3f;
    font-size: 0.95rem;
    font-weight: 500;
    line-height: 1.2;
  }

  .product-option-pill-meta {
    color: rgba(0, 0, 0, 0.42);
    font-size: 0.78rem;
    line-height: 1.2;
  }

  .product-showcase-quantity {
    max-width: 11rem;
  }

  .product-showcase-quantity-input {
    min-height: 3rem;
    border-radius: 0.95rem;
    background: rgba(255, 255, 255, 0.78);
  }

  .product-showcase-actions {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
    align-items: stretch;
    gap: 0.85rem;
  }

  .product-showcase-actions .btn {
    width: 100%;
    min-width: 0;
    padding-block: 0.7rem;
  }

  .product-showcase-status {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.55rem;
    margin-top: 0.85rem;
  }

  .product-showcase-favorite {
    width: 3.2rem !important;
    min-width: 3.2rem !important;
    padding-inline: 0 !important;
    padding-block: 0 !important;
    border-radius: 999px !important;
  }

  .product-showcase-favorite.active {
    color: var(--brand-black) !important;
  }

  .product-showcase-favorite:hover {
    color: var(--brand-black) !important;
  }

  .product-showcase-favorite .favorite-toggle-icon {
    width: 1rem;
    height: 1rem;
  }

  .product-showcase-favorite.active .favorite-toggle-icon {
    filter: brightness(0) saturate(100%);
  }

  .product-showcase-back-link {
    color: rgba(0, 0, 0, 0.56);
    font-size: 0.92rem;
    text-decoration: none;
  }

  .product-showcase-back-link:hover {
    color: var(--brand-black);
  }

  .product-showcase-shipping {
    margin-top: 0.35rem;
  }

  .product-detail-panel {
    background: rgba(255, 255, 255, 0.42);
  }

  .product-detail-table th {
    width: 32%;
    color: rgba(0, 0, 0, 0.56);
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .product-detail-table td {
    color: #434343;
  }

  .attribute-swatch-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
  }

  .attribute-swatch-option {
    position: relative;
    display: inline-flex;
    cursor: pointer;
  }

  .attribute-swatch-option.is-disabled {
    cursor: not-allowed;
    opacity: 0.48;
  }

  .attribute-swatch-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    pointer-events: none;
  }

  .attribute-swatch-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 999px;
    background: rgba(242, 242, 242, 0.92);
    border: 1px solid rgba(16, 34, 40, 0.14);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
  }

  .attribute-swatch-dot {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.7rem;
    height: 1.7rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.68);
    box-shadow: inset 0 0 0 1px rgba(16, 34, 40, 0.08);
  }

  .attribute-swatch-check {
    font-size: 0.82rem;
    line-height: 1;
    font-weight: 700;
    opacity: 0;
    transform: scale(0.82);
    transition: opacity 0.18s ease, transform 0.18s ease;
  }

  .attribute-swatch-input:checked + .attribute-swatch-chip {
    border-color: rgba(16, 34, 40, 0.38);
    box-shadow: 0 0 0 0.16rem rgba(16, 34, 40, 0.14);
    transform: translateY(-1px);
  }

  .attribute-swatch-input:checked + .attribute-swatch-chip .attribute-swatch-check {
    opacity: 1;
    transform: scale(1);
  }

  .attribute-swatch-input:focus-visible + .attribute-swatch-chip {
    box-shadow: 0 0 0 0.18rem rgba(16, 34, 40, 0.18);
  }

  .attribute-swatch-inline {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.45rem;
  }

  .attribute-inline-swatch {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border-radius: 999px;
    border: 1px solid rgba(16, 34, 40, 0.16);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.45);
    vertical-align: middle;
  }

  .attribute-inline-swatch.attribute-inline-swatch-lg {
    width: 1.4rem;
    height: 1.4rem;
  }

  h1,
  h2,
  h3,
  h4,
  h5,
  h6,
  .h1,
  .h2,
  .h3,
  .h4,
  .h5,
  .h6,
  .display-1,
  .display-2,
  .display-3,
  .display-4,
  .display-5,
  .display-6,
  .navbar-brand,
  .nav-link,
  .dropdown-item,
  .btn,
  .form-label,
  .offcanvas-title,
  .card-title,
  .accordion-button,
  .table thead th,
  .breadcrumb-item {
    font-family: var(--font-heading);
    font-optical-sizing: auto;
    font-weight: 600;
    font-style: normal;
  }

  h1,
  h2,
  h3,
  h4,
  h5,
  h6 {
    font-family: var(--font-heading) !important;
    font-style: normal !important;
  }

  .font-highlight,
  .lead,
  blockquote,
  .blockquote,
  .price-highlight,
  .section-highlight,
  .carousel-caption p:first-child {
    font-family: var(--font-highlight);
    font-optical-sizing: auto;
    font-weight: 500;
    font-style: italic;
    font-variation-settings: "wdth" 100;
  }

  .font-heading {
    font-family: var(--font-heading);
    font-optical-sizing: auto;
    font-weight: 600;
    font-style: normal;
  }

  .font-body {
    font-family: var(--font-body);
    font-optical-sizing: auto;
    font-weight: 400;
    font-style: normal;
    font-variation-settings: "wdth" 100;
  }

  header .navbar-nav .nav-link,
  header .navbar .dropdown-item {
    font-family: var(--font-heading) !important;
    font-optical-sizing: auto;
    font-style: normal;
    font-weight: 300 !important;
  }

  .header-action-link,
  .header-action-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    padding: 0;
    border: 0;
    background: transparent;
    box-shadow: none !important;
    text-decoration: none;
  }

  .header-action-link:hover,
  .header-action-button:hover {
    opacity: 0.72;
  }

  .header-action-link:focus-visible,
  .header-action-button:focus-visible {
    outline: 2px solid rgba(0, 0, 0, 0.25);
    outline-offset: 3px;
  }

  .header-action-icon {
    display: block;
    width: 1rem;
    height: 1rem;
    filter: brightness(0) saturate(100%);
  }

  .header-action-badge {
    min-width: 1.1rem;
    height: 1.1rem;
    padding: 0 0.2rem;
    font-size: 0.65rem;
    line-height: 1.1rem;
  }

  .mobile-menu-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    padding: 0;
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 999px;
    background: rgba(9, 24, 28, 0.28);
    color: rgba(255, 255, 255, 0.96);
    box-shadow: none !important;
    backdrop-filter: blur(8px);
    border:none
  }

  .mobile-menu-toggle:hover {
    background: rgba(9, 24, 28, 0.4);
    border-color: rgba(255, 255, 255, 0.3);
  }

  .mobile-menu-toggle-box {
    position: relative;
    width: 1.7rem;
    height: 1rem;
    display: block;
  }

  .mobile-menu-toggle-line {
    position: absolute;
    left: 0;
    width: 100%;
    height: 2px;
    border-radius: 999px;
    background: currentColor;
    transform-origin: center;
    transition:
      transform 0.32s ease,
      opacity 0.22s ease,
      top 0.32s ease,
      bottom 0.32s ease;
  }

  .mobile-menu-toggle-line:nth-child(1) {
    top: 0;
  }

  .mobile-menu-toggle-line:nth-child(2) {
    top: calc(50% - 1px);
  }

  .mobile-menu-toggle-line:nth-child(3) {
    bottom: 0;
  }

  body.mobile-menu-open .mobile-menu-toggle-line:nth-child(1) {
    top: calc(50% - 1px);
    transform: rotate(45deg);
  }

  body.mobile-menu-open .mobile-menu-toggle-line:nth-child(2) {
    opacity: 0;
    transform: scaleX(0.2);
  }

  body.mobile-menu-open .mobile-menu-toggle-line:nth-child(3) {
    bottom: calc(50% - 1px);
    transform: rotate(-45deg);
  }

  .mobile-menu-panel {
    position: fixed;
    inset: 0;
    z-index: 1035;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    background:
      radial-gradient(circle at top, rgba(248, 238, 221, 0.12), transparent 36%),
      linear-gradient(180deg, rgba(9, 24, 28, 0.96) 0%, rgba(9, 24, 28, 0.98) 100%);
    backdrop-filter: blur(16px);
    transition: opacity 0.32s ease, visibility 0.32s ease;
  }

  body.mobile-menu-open .mobile-menu-panel {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }

  .mobile-menu-panel-inner {
    min-height: 100vh;
    padding-top: clamp(7rem, 16vh, 9rem);
    padding-bottom: 2rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 2rem;
  }

  .mobile-menu-header {
    display: flex;
    justify-content: flex-end;
  }

  .mobile-menu-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.6rem;
    height: 2.6rem;
    padding: 0;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1;
    box-shadow: none !important;
    transform: translateY(0.75rem);
    opacity: 0;
    transition:
      transform 0.35s ease 0.02s,
      opacity 0.25s ease 0.02s,
      color 0.2s ease;
      position: absolute;
      top:20px
  }

  .mobile-menu-close:hover {
    color: #fff;
  }

  .mobile-menu-close-icon {
    display: inline-block;
    font-size: 2rem;
    line-height: 1;
    transform: translateY(-2px);
  }

  .mobile-menu-primary {
    display: grid;
    gap: 0.85rem;
  }

  .mobile-menu-link {
    font-family: var(--font-heading);
    font-size: clamp(2rem, 9vw, 3.4rem);
    font-weight: 300;
    line-height: 0.98;
    letter-spacing: -0.04em;
    color: rgba(255, 255, 255, 0.88);
    text-decoration: none;
    transform: translateY(1rem);
    opacity: 0;
    transition: transform 0.4s ease, opacity 0.28s ease, color 0.2s ease;
  }

  .mobile-menu-link.active,
  .mobile-menu-link:hover {
    color: #fff;
  }

  body.mobile-menu-open .mobile-menu-link,
  body.mobile-menu-open .mobile-menu-category-link,
  body.mobile-menu-open .mobile-menu-action-button,
  body.mobile-menu-open .mobile-menu-label,
  body.mobile-menu-open .mobile-menu-close,
  body.mobile-menu-open .mobile-menu-logout-button {
    transform: none;
    opacity: 1;
  }

  .mobile-menu-link:nth-child(1) { transition-delay: 0.04s; }
  .mobile-menu-link:nth-child(2) { transition-delay: 0.08s; }
  .mobile-menu-link:nth-child(3) { transition-delay: 0.12s; }
  .mobile-menu-link:nth-child(4) { transition-delay: 0.16s; }

  .mobile-menu-secondary {
    display: grid;
    gap: 0.9rem;
  }

  .mobile-menu-label {
    margin: 0;
    font-family: var(--font-heading);
    font-size: 0.8rem;
    font-weight: 400;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.48);
    transform: translateY(0.75rem);
    opacity: 0;
    transition: transform 0.35s ease 0.18s, opacity 0.25s ease 0.18s;
  }

  .mobile-menu-category-list,
  .mobile-menu-actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
  }

  .mobile-menu-category-link,
  .mobile-menu-action-button,
  .mobile-menu-logout-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 3rem;
    padding: 0.85rem 1rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    background: rgba(255, 255, 255, 0.06);
    color: rgba(255, 255, 255, 0.84);
    font-family: var(--font-heading);
    font-size: 0.9rem;
    font-weight: 400;
    text-decoration: none;
    box-shadow: none !important;
    transform: translateY(0.75rem);
    opacity: 0;
    transition:
      transform 0.35s ease,
      opacity 0.25s ease,
      background 0.2s ease,
      border-color 0.2s ease,
      color 0.2s ease;
  }

  .mobile-menu-category-link:hover,
  .mobile-menu-action-button:hover,
  .mobile-menu-logout-button:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.24);
    color: #fff;
  }

  .mobile-menu-category-link:nth-child(1),
  .mobile-menu-action-button:nth-child(1) { transition-delay: 0.2s; }
  .mobile-menu-category-link:nth-child(2),
  .mobile-menu-action-button:nth-child(2) { transition-delay: 0.24s; }
  .mobile-menu-category-link:nth-child(3),
  .mobile-menu-action-button:nth-child(3) { transition-delay: 0.28s; }
  .mobile-menu-category-link:nth-child(4),
  .mobile-menu-action-button:nth-child(4) { transition-delay: 0.32s; }
  .mobile-menu-category-link:nth-child(5) { transition-delay: 0.36s; }
  .mobile-menu-category-link:nth-child(6) { transition-delay: 0.4s; }
  .mobile-menu-category-link:nth-child(7) { transition-delay: 0.44s; }

  .mobile-menu-footer {
    display: grid;
    gap: 1rem;
  }

  .mobile-menu-action-button {
    position: relative;
  }

  .mobile-menu-action-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.4rem;
    height: 1.4rem;
    margin-left: 0.5rem;
    padding: 0 0.3rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.16);
    font-size: 0.7rem;
    line-height: 1;
  }

  .mobile-menu-logout-form {
    margin: 0;
  }

  .favorite-toggle-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
  }

  .favorite-toggle-icon {
    width: 1rem;
    height: 1rem;
    filter: brightness(0) saturate(100%);
  }

  .favorite-toggle-button.active .favorite-toggle-icon,
  .favorite-toggle-button.btn-dark .favorite-toggle-icon,
  .favorite-toggle-button.text-white .favorite-toggle-icon {
    filter: brightness(0) invert(1);
  }

  .catalog-product-card {
    background: transparent;
  }

  .catalog-product-media {
    position: relative;
    display: block;
    overflow: hidden;
    border-radius: 1.35rem;
    background: #e8ddd0;
  }

  .catalog-product-media-link {
    display: block;
  }

  .catalog-product-image {
    width: 100%;
    aspect-ratio: 4 / 4.75;
    object-fit: cover;
    display: block;
    transition: transform 0.45s ease;
  }

  .catalog-product-media:hover .catalog-product-image,
  .catalog-product-media:focus .catalog-product-image {
    transform: scale(1.03);
  }

  .catalog-product-title {
    line-height: 1.25;
  }

  .catalog-product-title a {
    color: var(--brand-black);
    text-decoration: none;
  }

  .catalog-product-title a:hover {
    color: rgba(0, 0, 0, 0.72);
  }

  .catalog-product-price {
    color: rgba(0, 0, 0, 0.84);
    font-family: var(--font-heading);
    font-size: 1.15rem;
    font-weight: 500;
    line-height: 1.15;
  }

  .catalog-product-actions {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 0.65rem;
  }

  .catalog-product-actions--single {
    grid-template-columns: minmax(0, 1fr);
  }

  .catalog-product-actions .btn {
    width: 100%;
    min-width: 0;
  }

  .catalog-product-favorite {
    width: 3rem !important;
    min-width: 3rem !important;
    padding-inline: 0 !important;
  }

  .catalog-product-favorite-floating {
    position: absolute;
    top: 0.9rem;
    right: 0.9rem;
    z-index: 2;
    height: 3rem;
    border-radius: 999px !important;
    background: rgba(247, 242, 235, 0.94);
    border-color: rgba(0, 0, 0, 0.12);
    box-shadow: 0 0.35rem 1rem rgba(0, 0, 0, 0.08);
  }

  .catalog-product-favorite-floating:hover {
    background: rgba(247, 242, 235, 1);
  }

  .catalog-product-favorite-floating.active,
  .catalog-product-favorite-floating.btn-dark {
    background: var(--brand-black);
    border-color: var(--brand-black);
  }

  .search-offcanvas {
    --bs-offcanvas-height: auto;
    height: auto !important;
    min-height: 0;
    border: 0;
    background: transparent;
    box-shadow: none;
  }

  .search-offcanvas-panel {
    width: min(64rem, calc(100% - 2rem));
    margin: 1rem auto 0;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 1.5rem;
    background: rgba(247, 242, 235, 0.98);
    box-shadow: 0 1.1rem 3rem rgba(0, 0, 0, 0.14);
    overflow: hidden;
  }

  .search-offcanvas .offcanvas-header {
    align-items: center;
    padding: 1.1rem 1.35rem 0.9rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.07);
  }

  .search-offcanvas .offcanvas-title {
    font-family: var(--font-heading);
    font-size: 1rem;
    font-weight: 300;
    letter-spacing: 0.04em;
  }

  .search-offcanvas .offcanvas-body {
    padding: 0;
  }

  .search-offcanvas-shell {
    padding: 1rem 1.35rem 1.35rem;
  }

  .search-offcanvas-form .form-control {
    min-height: 3.5rem;
    font-size: 1.1rem;
  }

  @media (max-width: 991.98px) {
    .page-header-hero-inner {
      padding-top: 7rem;
      padding-bottom: 2rem;
    }

    .checkout-flow-section {
      margin-top: -1.35rem;
      padding-top: 1.5rem;
      padding-bottom: 2.4rem;
      border-top-left-radius: 1.4rem;
      border-top-right-radius: 1.4rem;
    }

    .about-section-row {
      grid-template-columns: minmax(0, 1fr);
    }

    .contact-section-grid,
    .contact-meta-list {
      grid-template-columns: minmax(0, 1fr);
    }

    .account-nav-menu {
      gap: 0.6rem;
    }

    .account-nav-button {
      min-height: 3.7rem;
      padding-inline: 1.15rem;
    }

    .contact-map-frame {
      min-height: 24rem;
    }

    .contact-map-card {
      width: calc(100% - 2rem);
    }

    .product-showcase-section {
      margin-top: -1.35rem;
      padding-top: 1.5rem;
      padding-bottom: 2.4rem;
      border-top-left-radius: 1.4rem;
      border-top-right-radius: 1.4rem;
    }

    body.product-page .page-header-hero-inner {
      padding-bottom: 3.1rem;
    }

    .product-showcase-actions {
      grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 3rem;
    }

    .catalog-product-actions {
      grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    }

    body.checkout-page .page-header-hero-inner {
      padding-bottom: 3.1rem;
    }

    body.contact-page .page-header-hero-inner {
      padding-bottom: 3.1rem;
    }

    body.account-page .page-header-hero-inner {
      padding-bottom: 3.1rem;
    }

    body.category-page .page-header-hero-inner {
      padding-bottom: 3.1rem;
    }

    body.search-page .page-header-hero-inner {
      padding-bottom: 3.1rem;
    }

    .search-offcanvas-panel {
      width: calc(100% - 1rem);
      margin-top: 0.5rem;
      border-radius: 1.2rem;
    }

    .search-offcanvas .offcanvas-header,
    .search-offcanvas-shell {
      padding-inline: 1rem;
    }
  }

  body.checkout-page .page-header-hero {
    margin-bottom: 0;
  }

  body.checkout-page .page-header-hero-inner {
    padding-bottom: 4.15rem;
  }

  body.about-page .page-header-hero {
    margin-bottom: 0;
  }

  body.about-page .page-header-hero-inner {
    padding-bottom: 4.15rem;
  }

  body.contact-page .page-header-hero {
    margin-bottom: 0;
  }

  body.contact-page .page-header-hero-inner {
    padding-bottom: 4.15rem;
  }

  body.account-page .page-header-hero {
    margin-bottom: 0;
  }

  body.account-page .page-header-hero-inner {
    padding-bottom: 4.15rem;
  }

  body.category-page .page-header-hero {
    margin-bottom: 0;
  }

  body.category-page .page-header-hero-inner {
    padding-bottom: 4.15rem;
  }

  body.search-page .page-header-hero {
    margin-bottom: 0;
  }

  body.search-page .page-header-hero-inner {
    padding-bottom: 4.15rem;
  }

  body.product-page .page-header-hero {
    margin-bottom: 0;
  }

  body.product-page .page-header-hero-inner {
    padding-bottom: 4.35rem;
  }

  .bg-brand-paper {
    background: var(--brand-paper) !important;
  }

  .bg-brand-cream {
    background: var(--brand-cream) !important;
  }

  .bg-brand-muted {
    background: var(--brand-muted) !important;
  }

  .text-brand-black {
    color: var(--brand-black) !important;
  }

  .text-brand-gray {
    color: var(--brand-gray) !important;
  }

  .btn {
    --bs-btn-font-family: var(--font-heading);
    --bs-btn-font-weight: 600;
    --bs-btn-border-radius: 1rem;
    padding: 0.75rem 1.4rem;
    letter-spacing: 0.01em;
  }

  .btn.rounded-pill {
    padding-inline: 1.75rem;
  }

  .btn-primary {
    --bs-btn-bg: var(--brand-black);
    --bs-btn-border-color: var(--brand-black);
    --bs-btn-hover-bg: #1a1a1a;
    --bs-btn-hover-border-color: #1a1a1a;
    --bs-btn-active-bg: #1a1a1a;
    --bs-btn-active-border-color: #1a1a1a;
    --bs-btn-disabled-bg: var(--brand-gray);
    --bs-btn-disabled-border-color: var(--brand-gray);
    --bs-btn-color: var(--brand-white);
    --bs-btn-hover-color: var(--brand-white);
    --bs-btn-active-color: var(--brand-white);
    --bs-btn-disabled-color: var(--brand-white);
  }

  .btn-secondary {
    --bs-btn-bg: var(--brand-gray);
    --bs-btn-border-color: var(--brand-gray);
    --bs-btn-hover-bg: #6f6f6f;
    --bs-btn-hover-border-color: #6f6f6f;
    --bs-btn-active-bg: #6f6f6f;
    --bs-btn-active-border-color: #6f6f6f;
    --bs-btn-disabled-bg: #b0b0b0;
    --bs-btn-disabled-border-color: #b0b0b0;
    --bs-btn-color: var(--brand-white);
    --bs-btn-hover-color: var(--brand-white);
    --bs-btn-active-color: var(--brand-white);
    --bs-btn-disabled-color: var(--brand-white);
  }

  .btn-outline-dark {
    --bs-btn-bg: var(--brand-white);
    --bs-btn-border-color: var(--brand-gray);
    --bs-btn-hover-bg: var(--brand-black);
    --bs-btn-hover-border-color: var(--brand-black);
    --bs-btn-active-bg: var(--brand-black);
    --bs-btn-active-border-color: var(--brand-black);
    --bs-btn-color: var(--brand-black);
    --bs-btn-hover-color: var(--brand-white);
    --bs-btn-active-color: var(--brand-white);
  }

  .btn-outline-secondary {
    --bs-btn-bg: transparent;
    --bs-btn-border-color: var(--brand-gray);
    --bs-btn-hover-bg: var(--brand-black);
    --bs-btn-hover-border-color: var(--brand-black);
    --bs-btn-active-bg: var(--brand-black);
    --bs-btn-active-border-color: var(--brand-black);
    --bs-btn-color: var(--brand-black);
    --bs-btn-hover-color: var(--brand-white);
    --bs-btn-active-color: var(--brand-white);
  }

  .btn-light {
    --bs-btn-bg: var(--brand-cream);
    --bs-btn-border-color: var(--brand-cream);
    --bs-btn-hover-bg: #efe1c8;
    --bs-btn-hover-border-color: #efe1c8;
    --bs-btn-active-bg: #efe1c8;
    --bs-btn-active-border-color: #efe1c8;
    --bs-btn-color: var(--brand-black);
    --bs-btn-hover-color: var(--brand-black);
    --bs-btn-active-color: var(--brand-black);
  }

  .form-control,
  .form-select,
  .input-group-text {
    border-color: var(--brand-border);
    border-radius: 1rem;
    background: var(--brand-white);
    color: var(--brand-black);
  }

  .form-control:focus,
  .form-select:focus {
    border-color: rgba(0, 0, 0, 0.28);
    box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.08);
  }
</style>
