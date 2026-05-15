@extends('layouts.app')

@section('title', $customer->exists ? 'Editar cliente' : 'Novo cliente')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h1 class="h4 mb-0">{{ $customer->exists ? 'Editar cliente' : 'Novo cliente' }}</h1>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}">
                        @csrf
                        @if ($customer->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
