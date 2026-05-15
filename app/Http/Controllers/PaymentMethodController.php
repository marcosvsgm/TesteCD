<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        return view('payment-methods.index', [
            'paymentMethods' => PaymentMethod::orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('payment-methods.form', [
            'paymentMethod' => new PaymentMethod(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        PaymentMethod::create($validated);

        return redirect()->route('payment-methods.index')->with('success', 'Forma de pagamento cadastrada com sucesso.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('payment-methods.form', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $paymentMethod->update($validated);

        return redirect()->route('payment-methods.index')->with('success', 'Forma de pagamento atualizada com sucesso.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->sales()->exists() || $paymentMethod->installments()->exists()) {
            return redirect()->route('payment-methods.index')->with('error', 'Esta forma de pagamento possui vendas vinculadas e não pode ser excluída.');
        }

        $paymentMethod->delete();

        return redirect()->route('payment-methods.index')->with('success', 'Forma de pagamento excluída com sucesso.');
    }
}
