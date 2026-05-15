@extends('layouts.app')

@section('title', $paymentMethod->exists ? 'Editar forma de pagamento' : 'Nova forma de pagamento')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h1 class="h4 mb-0">{{ $paymentMethod->exists ? 'Editar forma de pagamento' : 'Nova forma de pagamento' }}</h1>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ $paymentMethod->exists ? route('payment-methods.update', $paymentMethod) : route('payment-methods.store') }}">
                        @csrf
                        @if ($paymentMethod->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $paymentMethod->name) }}" required>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $paymentMethod->is_active))>
                            <label class="form-check-label" for="is_active">Forma de pagamento ativa</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('payment-methods.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
