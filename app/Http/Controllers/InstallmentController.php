<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstallmentRequest;
use App\Models\Installment;
use App\Models\PaymentMethod;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InstallmentController extends Controller
{
    public function edit(Sale $sale, Installment $installment): View
    {
        $this->ensureInstallmentBelongsToSale($sale, $installment);

        $sale->loadMissing(['customer', 'seller']);
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('installments.form', compact('sale', 'installment', 'paymentMethods'));
    }

    public function update(InstallmentRequest $request, Sale $sale, Installment $installment): RedirectResponse
    {
        $this->ensureInstallmentBelongsToSale($sale, $installment);

        $installment->update($request->validated());
        $paymentMethodIds = $sale->installments()
            ->pluck('payment_method_id')
            ->filter()
            ->unique()
            ->values();

        $sale->update([
            'payment_method_id' => $paymentMethodIds->first(),
        ]);
        $sale->paymentMethods()->sync($paymentMethodIds->all());

        return redirect()
            ->route('sales.edit', $sale)
            ->with('success', 'Pagamento da parcela atualizado com sucesso.');
    }

    private function ensureInstallmentBelongsToSale(Sale $sale, Installment $installment): void
    {
        abort_unless($installment->sale_id === $sale->id, 404);
    }
}
