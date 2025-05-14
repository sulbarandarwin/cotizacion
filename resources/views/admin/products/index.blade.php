@extends('adminlte::page') {{-- Usamos la plantilla base de AdminLTE --}}

@section('title', 'Gestión de Productos') {{-- Título de la página --}}

@section('content_header')
    <h1>Listado de Productos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('products.create') }}" class="btn btn-primary">Agregar Producto</a>
            {{-- Aquí podrías añadir botones para Importar/Exportar más adelante --}}
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Unidad</th>
                        <th>Costo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->unit_of_measure }}</td>
                            <td>{{ number_format($product->cost, 2, ',', '.') }}</td> {{-- Formato de moneda --}}
                            <td>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este producto?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay productos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $products->links() }} {{-- Paginación --}}
        </div>
    </div>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop