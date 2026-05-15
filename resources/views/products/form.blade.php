@extends('layouts.app')

@section('title', $product->exists ? 'Editar produto' : 'Novo produto')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h1 class="h4 mb-0">{{ $product->exists ? 'Editar produto' : 'Novo produto' }}</h1>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
                        @csrf
                        @if ($product->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="unit_price" class="form-label">Preço unitário</label>
                            <input type="number" step="0.01" min="0.01" id="unit_price" name="unit_price" class="form-control" value="{{ old('unit_price', $product->unit_price) }}" required>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                            <label class="form-check-label" for="is_active">Produto ativo</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
