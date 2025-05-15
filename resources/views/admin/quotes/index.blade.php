@extends('adminlte::page')

@section('title', 'Gestión de Cotizaciones')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="m-0 text-dark">Listado de Cotizaciones</h1>
        {{-- El permiso can('create_quotes') es un ejemplo, ajusta según tu sistema de permisos si usas uno --}}
        @can('create_quotes') 
            <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                <i class="fas fa-file-medical"></i> Crear Nueva Cotización
            </a>
        @else
             {{-- Si no tienes sistema de permisos aún, puedes mostrar el botón directamente --}}
             <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                <i class="fas fa-file-medical"></i> Crear Nueva Cotización
            </a>
        @endcan
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
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
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
                            <th># Cotización</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Fecha Emisión</th>
                            <th>Fecha Validez</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center" style="width: 20%;">Acciones</th> {{-- Ajustar ancho si es necesario --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quotes as $quote)
                            <tr>
                                <td>
                                    <a href="{{ route('quotes.show', $quote) }}" title="Ver Detalles">{{ $quote->quote_number }}</a>
                                </td>
                                <td>{{ $quote->client->name ?? 'N/A' }}</td>
                                <td>{{ $quote->user->name ?? 'N/A' }}</td>
                                <td>{{ $quote->issue_date->format('d/m/Y') }}</td>
                                <td>{{ $quote->expiry_date->format('d/m/Y') }}</td>
                                <td class="text-right">{{ number_format($quote->total, 2, ',', '.') }} {{ $quote->base_currency }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $quote->status_class }}">{{ $quote->status_text }}</span>
                                </td>
                                <td class="text-center">
                                    {{-- Botón Ver --}}
                                    <a href="{{ route('quotes.show', $quote) }}" class="btn btn-sm btn-info" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Botón Editar (con permiso de ejemplo) --}}
                                    @can('edit_quotes')
                                    <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @elsecan('edit_own_quotes') {{-- Ejemplo de otro permiso: editar solo las propias --}}
                                        @if(Auth::id() == $quote->user_id)
                                        <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                    @else {{-- Si no hay sistema de permisos, mostrar siempre --}}
                                    <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    {{-- Formulario para Duplicar (con permiso de ejemplo) --}}
                                    @can('duplicate_quotes')
                                    <form action="{{ route('quotes.duplicate', $quote) }}" method="POST" style="display: inline;" class="ml-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Duplicar Cotización" onclick="return confirm('¿Está seguro de que desea duplicar esta cotización #{{ $quote->quote_number }}? Se creará una nueva cotización editable.')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                    @else {{-- Si no hay sistema de permisos, mostrar siempre --}}
                                    <form action="{{ route('quotes.duplicate', $quote) }}" method="POST" style="display: inline;" class="ml-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Duplicar Cotización" onclick="return confirm('¿Está seguro de que desea duplicar esta cotización #{{ $quote->quote_number }}? Se creará una nueva cotización editable.')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                    @endcan

                                    {{-- Formulario para Eliminar (con permiso de ejemplo) --}}
                                    @can('delete_quotes')
                                    <form action="{{ route('quotes.destroy', $quote) }}" method="POST" style="display:inline;" class="ml-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de que desea eliminar esta cotización #{{ $quote->quote_number }}? Esta acción no se puede deshacer.')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @elsecan('delete_own_quotes') {{-- Ejemplo de otro permiso: eliminar solo las propias (con cuidado) --}}
                                        @if(Auth::id() == $quote->user_id && $quote->status == 'Borrador') {{-- Ejemplo: solo borrar borradores propios --}}
                                        <form action="{{ route('quotes.destroy', $quote) }}" method="POST" style="display:inline;" class="ml-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de que desea eliminar esta cotización #{{ $quote->quote_number }}? Esta acción no se puede deshacer.')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @else {{-- Si no hay sistema de permisos, mostrar siempre --}}
                                    <form action="{{ route('quotes.destroy', $quote) }}" method="POST" style="display:inline;" class="ml-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de que desea eliminar esta cotización #{{ $quote->quote_number }}? Esta acción no se puede deshacer.')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay cotizaciones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($quotes->hasPages())
            <div class="card-footer clearfix">
                {{ $quotes->links() }}
            </div>
        @endif
    </div>
@stop

@section('js')
    <script>
        // Script para que los mensajes flash desaparezcan automáticamente
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 7000);
    </script>
@stop