<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Rango por defecto: mes en curso
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   now()->endOfMonth()->format('Y-m-d'));

        // Estados
        $paidOrderStatuses = ['paid', 'shipped', 'delivered'];

        // === KPIs ===
        // Ventas del rango (solo órdenes pagas)
        $sales = Order::whereIn('status', $paidOrderStatuses)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->sum('total');

        // Pedidos totales del rango (todos los estados)
        $ordersCount = Order::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->count();

        // Ticket promedio (ventas / pedidos pagados)
        $paidCount = Order::whereIn('status', $paidOrderStatuses)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->count();
        $avgTicket = $paidCount > 0 ? $sales / $paidCount : 0;

        // Clientes nuevos
        $newCustomers = Customer::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->count();

        // Pendientes de pago (payments con status pending) dentro del rango
        $pendingPayments = Payment::where('status', 'pending')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        $pendingPaymentsCount = (clone $pendingPayments)->count();
        $pendingPaymentsAmount = (clone $pendingPayments)->sum('amount');

        // Pendientes de envío (orders pagas pero shipment pendiente, o directamente orders en 'paid')
        $pendingShipmentCount = Order::whereIn('status', ['paid']) // podés ampliar lógica si tenés shipments
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->count();

        // === Gráficas ===
        // Ventas por día del rango (orders pagas)
        $salesByDay = Order::selectRaw('DATE(created_at) as d, SUM(total) as s')
            ->whereIn('status', $paidOrderStatuses)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->map(fn($r) => ['date' => $r->d, 'sum' => (float)$r->s]);

        // Distribución por estado de pedido (en el rango)
        $statusDist = Order::select('status', DB::raw('COUNT(*) as c'))
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->groupBy('status')
            ->pluck('c', 'status');

        // Top 5 clientes por monto (rango y pagas)
        $topCustomers = Customer::select('customers.id','customers.name',
                DB::raw('COALESCE(SUM(orders.total),0) as sum'))
            ->join('orders','orders.customer_id','=','customers.id')
            ->whereIn('orders.status', $paidOrderStatuses)
            ->whereBetween(DB::raw('DATE(orders.created_at)'), [$from, $to])
            ->groupBy('customers.id','customers.name')
            ->orderByDesc('sum')
            ->limit(5)
            ->get();

        // Top 5 productos por cantidad (si tenés OrderItem y relación con Product)
        $topProducts = OrderItem::select('order_items.product_id', DB::raw('SUM(order_items.quantity) as qty'))
            ->join('orders','orders.id','=','order_items.order_id')
            ->whereIn('orders.status', $paidOrderStatuses)
            ->whereBetween(DB::raw('DATE(orders.created_at)'), [$from, $to])
            ->groupBy('order_items.product_id')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // Traemos nombres de productos (si existe relación)
        $productNames = [];
        if ($topProducts->isNotEmpty()) {
            $productIds = $topProducts->pluck('product_id')->filter()->values();
            if ($productIds->count()) {
                $productNames = DB::table('products')
                    ->whereIn('id', $productIds)
                    ->pluck('name','id');
            }
        }

        // Últimos pedidos
        $lastOrders = Order::with('customer')
            ->latest('id')
            ->limit(10)
            ->get();

        // Traducciones ES para estados de órdenes
        $orderStatusLabels = [
            'pending'   => 'Pendiente',
            'paid'      => 'Pagado',
            'shipped'   => 'Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ];

        return view('admin.dashboard.index', compact(
            'from','to',
            'sales','ordersCount','avgTicket','newCustomers',
            'pendingPaymentsCount','pendingPaymentsAmount','pendingShipmentCount',
            'salesByDay','statusDist','topCustomers','topProducts','productNames',
            'lastOrders','orderStatusLabels'
        ));
    }
}