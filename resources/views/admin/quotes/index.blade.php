@extends('adminlte::page')

@section('title', 'Listado de Cotizaciones')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Listado de Cotizaciones</h1>
        @if(Auth::user()->can('crear cotizaciones') || Auth::user()->hasRole('Administrador') || Auth::user()->hasRole('Vendedor'))
            <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Cotización
            </a>
        @endif
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
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
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
                            <th class="text-right">Total</th>
                            <th>Estado</th>
                            <th style="min-width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotes as $quote)
                            <tr>
                                <td>{{ $quote->quote_number }}</td>
                                <td>{{ $quote->client->name ?? 'N/A' }}</td>
                                <td>{{ $quote->user->name ?? 'N/A' }}</td>
                                <td>{{ $quote->issue_date ? $quote->issue_date->format('d/m/Y') : 'N/A' }}</td>
                                <td class="text-right">{{ number_format($quote->total ?? 0, 2, ',', '.') }} {{ $quote->base_currency }}</td>
                                <td>
                                    @if ($quote->status)
                                    <span class="badge badge-{{ $quote->status_class }} p-2">{{ $quote->status_text }}</span>
                                    @else
                                    <span class="badge badge-light p-2">Indefinido</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group" aria-label="Acciones de Cotización">
                                        {{-- Para show, edit, update, changeStatus que usan Route Model Binding con {quote} --}}
                                        <a href="{{ route('quotes.show', ['quote' => $quote->id ?? 0]) }}" class="btn btn-xs btn-success" title="Ver Detalle"><i class="far fa-eye"></i></a>
                                        
                                        @if(Auth::user()->can('editar cotizaciones') || Auth::user()->hasRole('Administrador') || Auth::user()->hasRole('Vendedor'))
                                            <a href="{{ route('quotes.edit', ['quote' => $quote->id ?? 0]) }}" class="btn btn-xs btn-info" title="Editar"><i class="fas fa-edit"></i></a>
                                        @endif

                                        {{-- Para DUPLICATE que espera {quoteId} --}}
                                        @if(Auth::user()->can('duplicar cotizaciones') || Auth::user()->hasRole('Administrador') || Auth::user()->hasRole('Vendedor'))
                                        <form action="{{ route('quotes.duplicate', ['quoteId' => $quote->id ?? 0]) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Está seguro de duplicar esta cotización #{{ $quote->quote_number }}? Se creará una nueva cotización editable.');">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-warning" title="Duplicar Cotización">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        @if(Auth::user()->can('cambiar estado cotizaciones') || Auth::user()->hasRole('Administrador') || Auth::user()->hasRole('Vendedor'))
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-xs btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Cambiar Estado">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @foreach(App\Models\Quote::getAllStatuses() as $statusValue)
                                                    @if($quote->status !== $statusValue)
                                                    {{-- Para changeStatus que usa Route Model Binding con {quote} --}}
                                                    <form action="{{ route('quotes.changeStatus', ['quote' => $quote->id ?? 0]) }}" method="POST" style="display: block;">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $statusValue }}">
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('¿Está seguro de cambiar el estado a \'{{ App\Models\Quote::getStatusMap()[$statusValue] ?? $statusValue }}\'?')">
                                                            Marcar como {{ App\Models\Quote::getStatusMap()[$statusValue] ?? $statusValue }}
                                                        </button>
                                                    </form>
                                                    @else
                                                    <a class="dropdown-item disabled" href="#">Actual: {{ App\Models\Quote::getStatusMap()[$statusValue] ?? $statusValue }}</a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                        
                                        {{-- Para DESTROY que espera {quoteId} --}}
                                        @if(Auth::user()->hasRole('Administrador'))
                                        <form action="{{ route('quotes.destroy', ['quoteId' => $quote->id ?? 0]) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Está seguro de que desea eliminar esta cotización #{{ $quote->quote_number }}? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay cotizaciones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $quotes->links() }}
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
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
