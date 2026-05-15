<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $sales = Sale::query()
            ->with(['customer', 'seller', 'paymentMethod', 'paymentMethods', 'items.product', 'installments.paymentMethod'])
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when(
                $request->filled('payment_method_id'),
                fn ($query) => $query->whereHas(
                    'installments',
                    fn ($installments) => $installments->where('payment_method_id', $request->integer('payment_method_id'))
                )
            )
            ->when($request->filled('date_start'), fn ($query) => $query->whereDate('sale_date', '>=', $request->date('date_start')))
            ->when($request->filled('date_end'), fn ($query) => $query->whereDate('sale_date', '<=', $request->date('date_end')))
            ->latest('sale_date')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'customers' => Customer::orderBy('name')->get(),
            'sellers' => User::orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::orderBy('name')->get(),
            'filters' => $request->only(['customer_id', 'user_id', 'payment_method_id', 'date_start', 'date_end']),
        ]);
    }

    public function create(): View
    {
        return view('sales.form', $this->formData(new Sale([
            'sale_date' => now()->toDateString(),
        ])));
    }

    public function store(SaleRequest $request): RedirectResponse
    {
        $this->persistSale(new Sale(), $request->validated(), Auth::id());

        return redirect()->route('sales.index')->with('success', 'Venda cadastrada com sucesso.');
    }

    public function edit(Sale $sale): View
    {
        $sale->load(['items.product', 'installments.paymentMethod', 'paymentMethods']);

        return view('sales.form', $this->formData($sale));
    }

    public function update(SaleRequest $request, Sale $sale): RedirectResponse
    {
        $this->persistSale($sale, $request->validated(), $sale->user_id);

        return redirect()->route('sales.index')->with('success', 'Venda atualizada com sucesso.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Venda excluída com sucesso.');
    }

    public function pdf(Sale $sale)
    {
        $sale->load(['customer', 'seller', 'paymentMethod', 'paymentMethods', 'items.product', 'installments.paymentMethod']);

        return Pdf::loadView('sales.pdf', compact('sale'))->download("resumo-venda-{$sale->id}.pdf");
    }

    private function formData(Sale $sale): array
    {
        return [
            'sale' => $sale,
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function persistSale(Sale $sale, array $validated, int $sellerId): void
    {
        DB::transaction(function () use ($sale, $validated, $sellerId): void {
            $items = collect($validated['items'])
                ->filter(fn ($item) => filled($item['product_id']))
                ->map(function (array $item): array {
                    $quantity = (int) $item['quantity'];
                    $unitPrice = round((float) $item['unit_price'], 2);

                    return [
                        'product_id' => (int) $item['product_id'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => round($quantity * $unitPrice, 2),
                    ];
                })
                ->values();

            $installments = collect($validated['installments'])
                ->filter(fn ($installment) => filled($installment['due_date']))
                ->map(fn (array $installment): array => [
                    'payment_method_id' => (int) $installment['payment_method_id'],
                    'due_date' => $installment['due_date'],
                    'amount' => round((float) $installment['amount'], 2),
                    'status' => $installment['status'],
                ])
                ->values();

            $paymentMethodIds = $installments
                ->pluck('payment_method_id')
                ->filter()
                ->unique()
                ->values();

            $sale->fill([
                'user_id' => $sellerId,
                'customer_id' => $validated['customer_id'] ?? null,
                'payment_method_id' => $paymentMethodIds->first(),
                'sale_date' => $validated['sale_date'],
                'total' => round($items->sum('total'), 2),
            ]);
            $sale->save();

            $sale->items()->delete();
            $sale->installments()->delete();
            $sale->items()->createMany($items->all());
            $sale->installments()->createMany($installments->all());
            $sale->paymentMethods()->sync($paymentMethodIds->all());
        });
    }
}
