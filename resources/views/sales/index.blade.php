@extends('layouts.app')

@section('title', 'Vendas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Vendas</h1>
            <p class="text-muted mb-0">Consulte, filtre e gerencie o histórico de vendas realizadas.</p>
        </div>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">Nova venda</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="customer_id" class="form-label">Cliente</label>
                    <select id="customer_id" name="customer_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? '') == $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="user_id" class="form-label">Vendedor</label>
                    <select id="user_id" name="user_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($sellers as $seller)
                            <option value="{{ $seller->id }}" @selected(($filters['user_id'] ?? '') == $seller->id)>{{ $seller->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="payment_method_id" class="form-label">Forma de pagamento</label>
                    <select id="payment_method_id" name="payment_method_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($paymentMethods as $paymentMethod)
                            <option value="{{ $paymentMethod->id }}" @selected(($filters['payment_method_id'] ?? '') == $paymentMethod->id)>{{ $paymentMethod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_start" class="form-label">Data inicial</label>
                    <input type="date" id="date_start" name="date_start" class="form-control" value="{{ $filters['date_start'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="date_end" class="form-label">Data final</label>
                    <input type="date" id="date_end" name="date_end" class="form-control" value="{{ $filters['date_end'] ?? '' }}">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Pagamento</th>
                        <th>Data</th>
                        <th>Total</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $sale->id }}</td>
                            <td>{{ $sale->customer?->name ?? 'Sem cliente' }}</td>
                            <td>{{ $sale->seller->name }}</td>
                            <td>{{ $sale->payment_method_names }}</td>
                            <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                            <td class="text-end">
                                <a href="{{ route('sales.pdf', $sale) }}" class="btn btn-sm btn-outline-dark">PDF</a>
                                <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja excluir esta venda?')">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Nenhuma venda encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $sales->links() }}
        </div>
    </div>
@endsection
