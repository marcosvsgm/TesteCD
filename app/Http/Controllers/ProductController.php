<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.index', [
            'products' => Product::orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('products.form', [
            'product' => new Product(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit_price' => ['required', 'numeric', 'min:0.01'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Produto cadastrado com sucesso.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit_price' => ['required', 'numeric', 'min:0.01'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->saleItems()->exists()) {
            return redirect()->route('products.index')->with('error', 'Este produto possui itens de venda vinculados e não pode ser excluído.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produto excluído com sucesso.');
    }
}
