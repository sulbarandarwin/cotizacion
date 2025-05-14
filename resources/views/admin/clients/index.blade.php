@extends('adminlte::page') {{-- Usa la plantilla base de AdminLTE --}}

@section('title', 'Gestión de Clientes') {{-- Título de la pestaña del navegador --}}

@section('content_header')
    <h1 class="m-0 text-dark">Listado de Clientes</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('clients.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Agregar Cliente
            </a>
            {{-- Aquí podrías añadir botones para Importar/Exportar Clientes más adelante --}}
        </div>
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

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre / Razón Social</th>
                            <th>RIF / Cédula</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr>
                                <td>{{ $client->id }}</td>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->identifier }}</td>
                                <td>{{ $client->email ?? 'N/A' }}</td>
                                <td>{{ $client->phone ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de que desea eliminar este cliente? ESTA ACCIÓN TAMBIÉN ELIMINARÁ SUS COTIZACIONES ASOCIADAS.')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    {{-- Podrías añadir un botón para ver detalle (show) si lo implementas --}}
                                    {{-- <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay clientes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $clients->links() }} {{-- Muestra los enlaces de paginación --}}
        </div>
    </div>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        // Script para que las alertas de sesión se cierren automáticamente después de un tiempo
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 4000); // 4 segundos
    </script>
@stop