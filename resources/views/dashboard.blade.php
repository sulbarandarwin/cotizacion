@extends('adminlte::page') {{-- ESTO ES LO MÁS IMPORTANTE --}}

@section('title', 'Dashboard | Sistema Cotizaciones') {{-- Título de la pestaña del navegador --}}

@section('content_header')
    <h1 class="m-0 text-dark">Panel Principal</h1> {{-- Título principal dentro de la página --}}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">¡Bienvenido al Sistema de Cotizaciones!</p>
                    <p>Utiliza el menú de la izquierda para navegar por las diferentes secciones.</p>
                </div>
            </div>
        </div>
    </div>
    {{-- Aquí puedes empezar a añadir más contenido, como "tarjetas" (cards) con estadísticas o accesos directos --}}
    {{-- Ejemplo de algunas tarjetas (puedes personalizarlas o quitarlas): --}}
    <div class="row mt-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>N/A</h3> {{-- Aquí iría un contador de cotizaciones, por ejemplo --}}
                    <p>Cotizaciones Nuevas</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i> {{-- Necesitarías iconos ionicons o cambiar por fas/far --}}
                </div>
                <a href="#" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>N/A<sup style="font-size: 20px"></sup></h3> {{-- Aquí un contador de productos --}}
                    <p>Productos Activos</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="{{ route('products.index') }}" class="small-box-footer">Ver Productos <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>N/A</h3> {{-- Aquí un contador de clientes --}}
                    <p>Clientes Registrados</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="#" class="small-box-footer">Ver Clientes <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>N/A</h3>
                    <p>Reportes Pendientes</p>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <a href="#" class="small-box-footer">Ver Reportes <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        </div>
@stop

@section('css')
    {{-- Si necesitas CSS personalizado para esta página --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        // Si necesitas JavaScript específico para esta página
        // console.log('Dashboard page loaded!');
    </script>
@stop