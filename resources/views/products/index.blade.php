@extends('layouts.app')

@section('title', 'Produtos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Produtos</h1>
            <p class="text-muted mb-0">Cadastre e atualize os produtos disponíveis para venda.</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">Novo produto</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Preço unitário</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>R$ {{ number_format($product->unit_price, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge text-bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                    {{ $product->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja excluir este produto?')">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Nenhum produto cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $products->links() }}
        </div>
    </div>
@endsection
