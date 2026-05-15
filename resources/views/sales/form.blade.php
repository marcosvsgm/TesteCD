@extends('layouts.app')

@section('title', $sale->exists ? 'Editar venda' : 'Nova venda')

@php
    $itemRows = old('items', $sale->exists ? $sale->items->map(fn ($item) => [
        'product_id' => $item->product_id,
        'quantity' => $item->quantity,
        'unit_price' => $item->unit_price,
    ])->toArray() : [['product_id' => '', 'quantity' => 1, 'unit_price' => '']]);

    $installmentRows = old('installments', $sale->exists ? $sale->installments->map(fn ($installment) => [
        'id' => $installment->id,
        'payment_method_id' => $installment->payment_method_id ?? $sale->payment_method_id,
        'due_date' => $installment->due_date->format('Y-m-d'),
        'amount' => $installment->amount,
        'status' => $installment->status,
    ])->toArray() : [[
        'payment_method_id' => '',
        'due_date' => now()->toDateString(),
        'amount' => '',
        'status' => 'pending',
    ]]);
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $sale->exists ? 'Editar venda' : 'Nova venda' }}</h1>
            <p class="text-muted mb-0">O vendedor logado é associado automaticamente à venda.</p>
        </div>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>

    <form method="POST" action="{{ $sale->exists ? route('sales.update', $sale) : route('sales.store') }}" id="sale-form">
        @csrf
        @if ($sale->exists)
            @method('PUT')
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Dados gerais</h2>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label for="sale_date" class="form-label">Data da venda</label>
                    <input type="date" id="sale_date" name="sale_date" class="form-control" value="{{ old('sale_date', optional($sale->sale_date)->format('Y-m-d') ?? $sale->sale_date) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="customer_id" class="form-label">Cliente</label>
                    <select id="customer_id" name="customer_id" class="form-select" required>
                        <option value="">Selecione</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $sale->customer_id) == $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Itens da venda</h2>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-item">Adicionar item</button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="items-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Valor unitário</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemRows as $index => $item)
                            <tr class="item-row">
                                <td>
                                    <select name="items[{{ $index }}][product_id]" class="form-select product-select" required>
                                        <option value="">Selecione</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->unit_price }}" @selected(($item['product_id'] ?? '') == $product->id)>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" min="1" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $item['quantity'] ?? 1 }}" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" name="items[{{ $index }}][unit_price]" class="form-control item-unit-price" value="{{ $item['unit_price'] ?? '' }}" required>
                                </td>
                                <td class="item-total text-nowrap">R$ 0,00</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item">Remover</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end">
                <strong>Total da venda: <span id="sale-total">R$ 0,00</span></strong>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <h2 class="h5 mb-0">Parcelas</h2>
                <div class="d-flex gap-2 flex-wrap">
                    <input type="number" min="1" value="{{ max(count($installmentRows), 1) }}" class="form-control form-control-sm" id="installments-count" style="width: 110px;">
                    <input type="date" value="{{ old('sale_date', optional($sale->sale_date)->format('Y-m-d') ?? now()->toDateString()) }}" class="form-control form-control-sm" id="first-due-date" style="width: 170px;">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="generate-installments">Gerar parcelas</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-installment">Adicionar parcela</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="installments-table">
                    <thead>
                        <tr>
                            <th>Forma de pagamento</th>
                            <th>Vencimento</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($installmentRows as $index => $installment)
                            <tr class="installment-row">
                                <td>
                                    <select name="installments[{{ $index }}][payment_method_id]" class="form-select installment-payment-method" required>
                                        <option value="">Selecione</option>
                                        @foreach ($paymentMethods as $paymentMethod)
                                            <option value="{{ $paymentMethod->id }}" @selected(($installment['payment_method_id'] ?? '') == $paymentMethod->id)>
                                                {{ $paymentMethod->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    @if (! empty($installment['id']))
                                        <input type="hidden" name="installments[{{ $index }}][id]" value="{{ $installment['id'] }}">
                                    @endif
                                    <input type="date" name="installments[{{ $index }}][due_date]" class="form-control installment-date" value="{{ $installment['due_date'] ?? '' }}" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" name="installments[{{ $index }}][amount]" class="form-control installment-amount" value="{{ $installment['amount'] ?? '' }}" required>
                                </td>
                                <td>
                                    <select name="installments[{{ $index }}][status]" class="form-select installment-status">
                                        <option value="pending" @selected(($installment['status'] ?? 'pending') === 'pending')>Pendente</option>
                                        <option value="paid" @selected(($installment['status'] ?? '') === 'paid')>Paga</option>
                                    </select>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        @if (! empty($installment['id']) && $sale->exists)
                                            <a href="{{ route('sales.installments.edit', [$sale, $installment['id']]) }}" class="btn btn-sm btn-outline-primary">Editar pagamento</a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary edit-installment-inline" title="Edite a forma de pagamento desta parcela nesta linha">
                                                Editar pagamento
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-installment">Remover</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar venda</button>
        </div>
    </form>

    <template id="item-row-template">
        <tr class="item-row">
            <td>
                <select class="form-select product-select" required>
                    <option value="">Selecione</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->unit_price }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" min="1" class="form-control item-quantity" value="1" required></td>
            <td><input type="number" step="0.01" min="0.01" class="form-control item-unit-price" required></td>
            <td class="item-total text-nowrap">R$ 0,00</td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-item">Remover</button></td>
        </tr>
    </template>

    <template id="installment-row-template">
        <tr class="installment-row">
            <td>
                <select class="form-select installment-payment-method" required>
                    <option value="">Selecione</option>
                    @foreach ($paymentMethods as $paymentMethod)
                        <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="date" class="form-control installment-date" required></td>
            <td><input type="number" step="0.01" min="0.01" class="form-control installment-amount" required></td>
            <td>
                <select class="form-select installment-status">
                    <option value="pending">Pendente</option>
                    <option value="paid">Paga</option>
                </select>
            </td>
            <td class="text-end">
                <div class="d-inline-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary edit-installment-inline" title="Edite a forma de pagamento desta parcela nesta linha">
                        Editar pagamento
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-installment">Remover</button>
                </div>
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        window.saleFormConfig = {
            currencyFormatter: new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            })
        };
    </script>
@endpush
