@extends('adminlte::page')

@section('title', 'Listado de Productos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Listado de Productos</h1>
        <div>
            @can('importar productos') {{-- Permiso para importar --}}
            <a href="{{ route('products.import.form') }}" class="btn btn-info btn-sm mr-2">
                <i class="fas fa-upload"></i> Importar Productos
            </a>
            @endcan

            @can('exportar productos')
            <div class="btn-group mr-2">
                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-download"></i> Exportar
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="{{ route('products.export.excel') }}">
                        <i class="fas fa-file-excel"></i> Exportar a Excel (.xlsx)
                    </a>
                    <a class="dropdown-item" href="{{ route('products.export.csv') }}">
                        <i class="fas fa-file-csv"></i> Exportar a CSV
                    </a>
                </div>
            </div>
            @endcan

            @can('crear productos')
            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session('error'))
                 <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
             @if (session('import_errors')) {{-- Para mostrar errores de validación de Maatwebsite --}}
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Errores de Validación Durante la Importación:</h5>
                    <ul style="max-height: 200px; overflow-y: auto;">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
             @if (session('import_detailed_errors')) {{-- Para mostrar errores personalizados desde ProductsImport --}}
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Observaciones Durante la Importación:</h5>
                    <ul style="max-height: 200px; overflow-y: auto;">
                        @foreach (session('import_detailed_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th class="text-right">Costo</th>
                            <th>Unidad</th>
                            <th>Tipo Imp.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->code }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name ?? 'N/A' }}</td>
                                <td class="text-right">{{ number_format($product->cost, 2, ',', '.') }}</td>
                                <td>{{ $product->unit_of_measure }}</td>
                                <td>{{ $product->tax_type }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        @can('editar productos')
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-xs btn-info" title="Editar"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('eliminar productos')
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Está seguro de eliminar este producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay productos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        window.setTimeout(function() {
            $(".alert").not(".alert-danger").not(".alert-warning").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove(); 
            });
        }, 6000); // Aumentado el tiempo para mensajes de éxito
    </script>
@stop
