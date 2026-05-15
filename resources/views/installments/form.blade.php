@extends('layouts.app')

@section('title', 'Editar pagamento')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Editar pagamento</h1>
                    <p class="text-muted mb-0">
                        Venda #{{ $sale->id }} · {{ $sale->customer?->name ?? 'Sem cliente' }} · {{ $sale->sale_date->format('d/m/Y') }}
                    </p>
                </div>
                <a href="{{ route('sales.edit', $sale) }}" class="btn btn-outline-secondary">Voltar para venda</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Dados da parcela</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sales.installments.update', [$sale, $installment]) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="payment_method_id" class="form-label">Forma de pagamento</label>
                                <select id="payment_method_id" name="payment_method_id" class="form-select" required>
                                    <option value="">Selecione</option>
                                    @foreach ($paymentMethods as $paymentMethod)
                                        <option value="{{ $paymentMethod->id }}" @selected(old('payment_method_id', $installment->payment_method_id) == $paymentMethod->id)>
                                            {{ $paymentMethod->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="due_date" class="form-label">Vencimento</label>
                                <input
                                    type="date"
                                    id="due_date"
                                    name="due_date"
                                    class="form-control"
                                    value="{{ old('due_date', $installment->due_date->format('Y-m-d')) }}"
                                    required
                                >
                            </div>

                            <div class="col-md-4">
                                <label for="amount" class="form-label">Valor</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    id="amount"
                                    name="amount"
                                    class="form-control"
                                    value="{{ old('amount', $installment->amount) }}"
                                    required
                                >
                            </div>

                            <div class="col-md-12">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select" required>
                                    <option value="pending" @selected(old('status', $installment->status) === 'pending')>Pendente</option>
                                    <option value="paid" @selected(old('status', $installment->status) === 'paid')>Paga</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded bg-body-tertiary border">
                            <div class="small text-muted">Total da venda</div>
                            <div class="fw-semibold">R$ {{ number_format($sale->total, 2, ',', '.') }}</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar pagamento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
