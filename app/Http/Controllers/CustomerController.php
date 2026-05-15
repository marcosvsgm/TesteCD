<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('customers.index', [
            'customers' => Customer::orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('customers.form', [
            'customer' => new Customer(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->sales()->exists()) {
            return redirect()->route('customers.index')->with('error', 'Este cliente possui vendas vinculadas e não pode ser excluído.');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Cliente excluído com sucesso.');
    }
}
