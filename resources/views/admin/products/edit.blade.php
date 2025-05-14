@extends('adminlte::page')

@section('title', 'Editar Producto')

@section('content_header')
    <h1>Editar Producto: {{ $product->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT') {{-- Método para la actualización --}}

                <div class="form-group">
                    <label for="code">Código (SKU):</label>
                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $product->code) }}" required>
                    @error('code')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name">Nombre del Producto:</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="unit_of_measure">Unidad de Medida:</label>
                    <input type="text" name="unit_of_measure" id="unit_of_measure" class="form-control @error('unit_of_measure') is-invalid @enderror" value="{{ old('unit_of_measure', $product->unit_of_measure) }}" required>
                    @error('unit_of_measure')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="cost">Costo:</label>
                    <input type="number" name="cost" id="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ old('cost', $product->cost) }}" step="0.01" min="0" required>
                    @error('cost')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Producto</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop