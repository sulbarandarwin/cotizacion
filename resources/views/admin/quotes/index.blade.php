@extends('adminlte::page')

@section('title', 'Gestión de Cotizaciones')

@section('content_header')
    <h1 class="m-0 text-dark">Listado de Cotizaciones</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                <i class="fas fa-file-medical"></i> Crear Nueva Cotización
            </a>
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
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quotes as $quote)
                            <tr>
                                <td>
                                    <a href="{{ route('quotes.show', $quote->id) }}" title="Ver Detalles">{{ $quote->quote_number }}</a>
                                </td>
                                <td>{{ $quote->client->name ?? 'N/A' }}</td>
                                <td>{{ $quote->user->name ?? 'N/A' }}</td>
                                <td>{{ $quote->issue_date->format('d/m/Y') }}</td>
                                <td>{{ $quote->expiry_date->format('d/m/Y') }}</td>
                                <td>{{ number_format($quote->total, 2, ',', '.') }} {{ $quote->base_currency }}</td>
                                <td>
                                    <span class="badge @if($quote->status == 'Aceptada') badge-success @elseif($quote->status == 'Enviada') badge-info @elseif($quote->status == 'Borrador') badge-secondary @elseif($quote->status == 'Rechazada' || $quote->status == 'Cancelada') badge-danger @else badge-warning @endif">
                                        {{ $quote->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('quotes.show', $quote->id) }}" class="btn btn-sm btn-info" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('quotes.edit', $quote->id) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('quotes.destroy', $quote->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE') {{-- Esencial para la eliminación --}}
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de que desea eliminar esta cotización? ESTA ACCIÓN TAMBIÉN ELIMINARÁ SUS ÍTEMS ASOCIADOS.')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
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
        <div class="card-footer clearfix">
            {{ $quotes->links() }}
        </div>
    </div>
@stop

@section('js')
    <script>
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 4000);
    </script>
@stop