@extends('layouts.app')

@section('title', 'Formas de pagamento')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Formas de pagamento</h1>
            <p class="text-muted mb-0">Mantenha as opções disponíveis para uso nas vendas.</p>
        </div>
        <a href="{{ route('payment-methods.create') }}" class="btn btn-primary">Nova forma de pagamento</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paymentMethods as $paymentMethod)
                        <tr>
                            <td>{{ $paymentMethod->name }}</td>
                            <td>
                                <span class="badge text-bg-{{ $paymentMethod->is_active ? 'success' : 'secondary' }}">
                                    {{ $paymentMethod->is_active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('payment-methods.edit', $paymentMethod) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('payment-methods.destroy', $paymentMethod) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja excluir esta forma de pagamento?')">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Nenhuma forma de pagamento cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $paymentMethods->links() }}
        </div>
    </div>
@endsection
