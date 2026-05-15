@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Resumo rápido das vendas e parcelas do sistema.</p>
        </div>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">Nova venda</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-2">Total de vendas</p>
                    <h2 class="display-6 mb-0">{{ $totalSales }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-2">Valor total vendido</p>
                    <h2 class="display-6 mb-0">R$ {{ number_format($salesAmount, 2, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-2">Parcelas pendentes</p>
                    <h2 class="display-6 mb-0">{{ $pendingInstallments }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0">Últimas vendas</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Forma de pagamento</th>
                        <th>Data</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentSales as $sale)
                        <tr>
                            <td>{{ $sale->id }}</td>
                            <td>{{ $sale->customer?->name ?? 'Sem cliente' }}</td>
                            <td>{{ $sale->seller->name }}</td>
                            <td>{{ $sale->payment_method_names }}</td>
                            <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Nenhuma venda cadastrada até o momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
