<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\Sale;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'totalSales' => Sale::count(),
            'salesAmount' => Sale::sum('total'),
            'pendingInstallments' => Installment::where('status', 'pending')->count(),
            'recentSales' => Sale::query()
                ->with(['customer', 'seller', 'paymentMethod', 'paymentMethods', 'installments.paymentMethod'])
                ->latest('sale_date')
                ->take(5)
                ->get(),
        ]);
    }
}
