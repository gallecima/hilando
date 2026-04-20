<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $from   = $request->input('from'); // YYYY-mm-dd
        $to     = $request->input('to');   // YYYY-mm-dd
        $q      = $request->input('q');    // búsqueda por nombre/email/teléfono
        $minTx  = (int) $request->input('min_tx', 0);

        // Estados que cuentan como venta concretada
        $paidStatuses = ['paid', 'shipped', 'delivered'];

        $customers = Customer::query()
            ->select('customers.*')
            // búsqueda
            ->when($q, fn($qb) => $qb->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            }))
            // cantidad de órdenes (todas) en rango
            ->withCount([
                'orders as tx_count' => function ($o) use ($from, $to) {
                    $o->when($from, fn($x) => $x->whereDate('created_at', '>=', $from))
                      ->when($to,   fn($x) => $x->whereDate('created_at', '<=', $to));
                },
            ])
            // cantidad de órdenes pagas en rango
            ->withCount([
                'orders as paid_count' => function ($o) use ($from, $to, $paidStatuses) {
                    $o->whereIn('status', $paidStatuses)
                      ->when($from, fn($x) => $x->whereDate('created_at', '>=', $from))
                      ->when($to,   fn($x) => $x->whereDate('created_at', '<=', $to));
                },
            ])
            // suma de lo gastado (solo órdenes pagas) en rango
            ->withSum([
                'orders as paid_sum' => function ($o) use ($from, $to, $paidStatuses) {
                    $o->whereIn('status', $paidStatuses)
                      ->when($from, fn($x) => $x->whereDate('created_at', '>=', $from))
                      ->when($to,   fn($x) => $x->whereDate('created_at', '<=', $to));
                },
            ], 'total')
            // última compra (fecha) en rango y pagas
            ->withMax([
                'orders as last_order_at' => function ($o) use ($from, $to, $paidStatuses) {
                    $o->whereIn('status', $paidStatuses)
                      ->when($from, fn($x) => $x->whereDate('created_at', '>=', $from))
                      ->when($to,   fn($x) => $x->whereDate('created_at', '<=', $to));
                },
            ], 'created_at')
            // filtro por mínimo de transacciones (pagas) si se pide
            ->when($minTx > 0, fn($qb) => $qb->having('paid_count', '>=', $minTx))
            ->orderByDesc('paid_sum') // top spenders primero
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters'   => compact('from','to','q','minTx'),
        ]);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:customers',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string',
            'document' => 'nullable|string',
            'is_active' => 'boolean',
            'is_wholesaler' => 'boolean',
        ]);

        $data['password'] = bcrypt($data['password']);
        $data['is_wholesaler'] = $request->boolean('is_wholesaler');

        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'Cliente creado.');
    }

    public function edit(Customer $customer)
    {
        $customer->loadMissing(['billingData']);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'phone'                 => ['nullable', 'string', 'max:50'],
            'document'              => ['nullable', 'string', 'max:30'],
            'is_active'             => ['nullable', 'boolean'],
            'is_wholesaler'         => ['nullable', 'boolean'],
            'billing'               => ['nullable', 'array'],
            'billing.business_name' => ['nullable', 'string', 'max:255'],
            'billing.document_number' => ['nullable', 'string', 'max:20'],
            'billing.tax_status'    => ['nullable', 'string', 'max:100'],
            'billing.invoice_type'  => ['nullable', 'in:A,B,C'],
            'billing.address_line'  => ['nullable', 'string', 'max:255'],
            'billing.city'          => ['nullable', 'string', 'max:120'],
            'billing.province'      => ['nullable', 'string', 'max:120'],
            'billing.postal_code'   => ['nullable', 'string', 'max:20'],
            'billing.country'       => ['nullable', 'string', 'max:120'],
        ]);

        $customer->fill([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'document' => $validated['document'] ?? null,
        ]);
        $customer->is_active = $request->boolean('is_active');
        $customer->is_wholesaler = $request->boolean('is_wholesaler');
        $customer->save();

        $billingInput = $request->input('billing', []);
        $billingRequiredFields = [
            'business_name',
            'document_number',
            'tax_status',
            'address_line',
            'city',
            'province',
            'postal_code',
            'country',
        ];
        $hasBillingData = collect($billingRequiredFields)
            ->some(fn ($field) => filled($billingInput[$field] ?? null));

        if ($hasBillingData) {
            $request->validate(
                [
                    'billing.business_name'   => ['required'],
                    'billing.document_number' => ['required'],
                    'billing.tax_status'      => ['required'],
                    'billing.address_line'    => ['required'],
                    'billing.city'            => ['required'],
                    'billing.province'        => ['required'],
                    'billing.postal_code'     => ['required'],
                    'billing.country'         => ['required'],
                ],
                [
                    'billing.business_name.required' => 'Ingresá la razón social para guardar los datos de facturación.',
                    'billing.document_number.required' => 'Ingresá el CUIT o documento para guardar los datos de facturación.',
                    'billing.tax_status.required' => 'Seleccioná la condición frente al IVA para guardar los datos de facturación.',
                    'billing.address_line.required' => 'Ingresá el domicilio fiscal para guardar los datos de facturación.',
                    'billing.city.required' => 'Ingresá la ciudad para guardar los datos de facturación.',
                    'billing.province.required' => 'Ingresá la provincia para guardar los datos de facturación.',
                    'billing.postal_code.required' => 'Ingresá el código postal para guardar los datos de facturación.',
                    'billing.country.required' => 'Ingresá el país para guardar los datos de facturación.',
                ]
            );

            $customer->billingData()->updateOrCreate([], [
                'business_name'   => $billingInput['business_name'] ?? null,
                'document_number' => $billingInput['document_number'] ?? null,
                'tax_status'      => $billingInput['tax_status'] ?? null,
                'invoice_type'    => $billingInput['invoice_type'] ?? 'C',
                'address_line'    => $billingInput['address_line'] ?? null,
                'city'            => $billingInput['city'] ?? null,
                'province'        => $billingInput['province'] ?? null,
                'postal_code'     => $billingInput['postal_code'] ?? null,
                'country'         => $billingInput['country'] ?? null,
                'is_default'      => true,
            ]);
        } elseif ($customer->billingData) {
            $customer->billingData->delete();
        }

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Datos del cliente actualizados correctamente.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Cliente eliminado.');
    }

    public function show(Customer $customer)
    {
        $paidStatuses = ['paid', 'shipped', 'delivered'];

        $customer->loadCount([
            'orders as tx_count',
            'orders as paid_count' => fn($q) => $q->whereIn('status', $paidStatuses),
        ])->loadSum([
            'orders as paid_sum' => fn($q) => $q->whereIn('status', $paidStatuses),
        ], 'total');

        $orders = $customer->orders()
            ->with(['payments', 'shipment'])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Solo las relaciones que existen en tu modelo:
        $customer->loadMissing(['address', 'billingData']);

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$query, $filters] = $this->buildCustomerQuery($request);

        $filename = 'clientes_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            // Abrimos salida y agregamos BOM para Excel
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados (incluye Tx totales)
            fputcsv($out, [
                'ID', 'Nombre', 'Email', 'Teléfono',
                'Tx totales', 'Tx pagas', 'Gastado', 'Ticket promedio',
                'Última compra', 'Alta'
            ]);

            // Recorremos sin paginar
            $query->orderByDesc('paid_sum')->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $c) {
                    $txCount   = (int) ($c->tx_count ?? 0);
                    $paidCount = (int) ($c->paid_count ?? 0);
                    $paidSum   = (float) ($c->paid_sum ?? 0);
                    $avg       = $paidCount > 0 ? $paidSum / $paidCount : 0;

                    fputcsv($out, [
                        $c->id,
                        $c->name,
                        $c->email,
                        $c->phone,
                        $txCount,
                        $paidCount,
                        number_format($paidSum, 2, '.', ''),
                        number_format($avg, 2, '.', ''),
                        $c->last_order_at ? \Carbon\Carbon::parse($c->last_order_at)->format('Y-m-d H:i') : '',
                        optional($c->created_at)->format('Y-m-d'),
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Pragma'              => 'no-cache',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Devuelve [query, filtros] con los mismos agregados del index.
     */
    private function buildCustomerQuery(Request $request): array
    {
        $from   = $request->input('from');
        $to     = $request->input('to');
        $q      = $request->input('q');
        $minTx  = (int) $request->input('min_tx', 0);

        $filters = compact('from','to','q','minTx');
        $paidStatuses = ['paid','shipped','delivered'];

        $query = Customer::query()
            ->select('customers.*')
            ->when($q, fn($qb) => $qb->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            }))
            // todas las transacciones en rango
            ->withCount([
                'orders as tx_count' => function ($o) use ($from, $to) {
                    $o->when($from, fn($x) => $x->whereDate('created_at', '>=', $from))
                      ->when($to,   fn($x) => $x->whereDate('created_at', '<=', $to));
                },
            ])
            // transacciones pagas en rango
            ->withCount([
                'orders as paid_count' => function ($o) use ($from, $to, $paidStatuses) {
                    $o->whereIn('status', $paidStatuses)
                      ->when($from, fn($x) => $x->whereDate('created_at', '>=', $from))
                      ->when($to,   fn($x) => $x->whereDate('created_at', '<=', $to));
                },
            ])
            // suma gastada (pagas)
            ->withSum([
                'orders as paid_sum' => function ($o) use ($from, $to, $paidStatuses) {
                    $o->whereIn('status', $paidStatuses)
                      ->when($from, fn($x) => $x->whereDate('created_at', '>=', $from))
                      ->when($to,   fn($x) => $x->whereDate('created_at', '<=', $to));
                },
            ], 'total')
            // última compra (pagas)
            ->withMax([
                'orders as last_order_at' => function ($o) use ($from, $to, $paidStatuses) {
                    $o->whereIn('status', $paidStatuses)
                      ->when($from, fn($x) => $x->whereDate('created_at', '>=', $from))
                      ->when($to,   fn($x) => $x->whereDate('created_at', '<=', $to));
                },
            ], 'created_at')
            ->when($minTx > 0, fn($qb) => $qb->having('paid_count', '>=', $minTx));

        return [$query, $filters];
    }
}
