<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\JobApplication;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $monthStart = now()->startOfMonth();
        $prevMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $prevMonthEnd = now()->subMonthNoOverflow()->endOfMonth();

        $stats = [
            'products_total' => Product::count(),
            'products_active' => Product::where('is_active', true)->count(),
            'categories_total' => ProductCategory::count(),
            'orders_total' => Order::count(),
            'orders_this_month' => Order::where('created_at', '>=', $monthStart)->count(),
            'orders_pending' => Order::where('status', 'pending')->count(),
            'contact_new' => ContactMessage::where('status', 'new')->count(),
            'applications_new' => JobApplication::where('status', 'new')->count(),
            'blog_posts_total' => BlogPost::count(),
            'events_upcoming' => Event::where('event_date', '>=', now())->count(),
            'revenue_total' => (float) Order::where('status', '!=', 'cancelled')->sum('total'),
            'revenue_this_month' => (float) Order::where('status', '!=', 'cancelled')->where('created_at', '>=', $monthStart)->sum('total'),
            'revenue_last_month' => (float) Order::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->sum('total'),
            'stock_low' => Product::where('is_active', true)->where('stock_quantity', '<', 10)->count(),
        ];

        $stats['revenue_trend'] = $stats['revenue_last_month'] > 0
            ? round((($stats['revenue_this_month'] - $stats['revenue_last_month']) / $stats['revenue_last_month']) * 100)
            : ($stats['revenue_this_month'] > 0 ? 100 : 0);

        $recentOrders = Order::latest('placed_at')->latest('id')->take(8)->get();
        $recentMessages = ContactMessage::latest()->take(6)->get();
        $lowStockProducts = Product::where('is_active', true)
            ->where('stock_quantity', '<', 10)
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();
        $upcomingEvents = Event::where('event_date', '>=', now())
            ->orderBy('event_date')
            ->take(4)
            ->get();

        // Last 14 days order-count + revenue series for the trend chart.
        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->startOfDay());
        $ordersByDay = Order::where('created_at', '>=', $days->first())
            ->get()
            ->groupBy(fn ($o) => $o->created_at->format('Y-m-d'));

        $chart = [
            'labels' => $days->map(fn (Carbon $d) => $d->locale('en')->isoFormat('DD MMM'))->all(),
            'orders' => $days->map(fn (Carbon $d) => $ordersByDay->get($d->format('Y-m-d'), collect())->count())->all(),
            'revenue' => $days->map(fn (Carbon $d) => round($ordersByDay->get($d->format('Y-m-d'), collect())
                ->where('status', '!=', 'cancelled')->sum('total'), 2))->all(),
        ];

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'recentMessages', 'lowStockProducts', 'upcomingEvents', 'chart'
        ));
    }
}
