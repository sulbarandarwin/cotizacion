@extends('adminlte::page')

@section('title', 'Importar Productos')

@section('content_header')
    <h1 class="m-0 text-dark">Importar Productos desde Archivo</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-ban"></i> {{ session('error') }}
                </div>
            @endif
             @if(session('warning'))
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Atención Durante la Importación:</h5>
                    {{ session('warning') }}
                </div>
            @endif

            {{-- Errores de validación de Maatwebsite/Excel (fallas por fila) --}}
            @if (session('import_errors'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Errores de Validación Durante la Importación (Maatwebsite):</h5>
                    <ul style="max-height: 200px; overflow-y: auto; margin-bottom: 0;">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Errores detallados personalizados desde ProductsImport (errores de BD, etc.) --}}
             @if (session('import_detailed_errors') && count(session('import_detailed_errors')) > 0)
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Errores Detallados Adicionales:</h5>
                    <ul style="max-height: 200px; overflow-y: auto; margin-bottom: 0;">
                        @foreach (session('import_detailed_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Errores de validación del Request (ej: archivo no subido) --}}
            @if ($errors->any() && !$errors->has('import_file_content_errors')) {{-- Evitar duplicar si ya hay errores de contenido --}}
                 <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> ¡Error de Validación del Formulario!</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <form action="{{ route('products.import.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="import_file">Seleccionar Archivo (Excel: .xlsx, .xls o CSV: .csv) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" name="import_file" class="custom-file-input @error('import_file') is-invalid @enderror" id="import_file" required accept=".xlsx,.xls,.csv">
                            <label class="custom-file-label" for="import_file">Elegir archivo...</label>
                        </div>
                    </div>
                    @error('import_file') 
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span> 
                    @enderror
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Instrucciones y Formato del Archivo</h3>
                        </div>
                        <div class="card-body" style="font-size: 0.9em;">
                            <p>El archivo debe ser Excel (.xlsx, .xls) o CSV (.csv) y la primera fila debe contener las cabeceras. Las cabeceras deben coincidir con los siguientes nombres (el orden no importa, pero sí los nombres exactos):</p>
                            <ul>
                                <li><strong><code>codigo</code></strong> (Obligatorio, Texto, Máx: 50. Único para identificar productos existentes a actualizar o crear nuevos).</li>
                                <li><strong><code>nombre</code></strong> (Obligatorio, Texto, Máx: 255).</li>
                                <li><strong><code>descripcion</code></strong> (Opcional, Texto largo).</li>
                                <li><strong><code>costo</code></strong> (Obligatorio, Numérico. Usar punto <code>.</code> como separador decimal. Ej: <code>1250.75</code>).</li>
                                <li><strong><code>unidad_medida</code></strong> (Opcional, Texto, Máx: 50. Ej: Pza, Kg, Lt. Por defecto: Pza).</li>
                                <li><strong><code>categoria_nombre</code></strong> (Opcional, Texto, Máx: 255. Nombre de la categoría. Si no existe, se creará una nueva).</li>
                                <li><strong><code>tipo_impuesto</code></strong> (Opcional, Texto. Valores aceptados: <code>Gravado</code>, <code>Exento</code>, <code>No Sujeto</code>. Por defecto: Gravado).</li>
                            </ul>
                            <p><strong>Notas:</strong></p>
                            <ul>
                                <li>La importación de imágenes de productos no está soportada a través de este formulario. Las imágenes deben gestionarse individualmente.</li>
                                <li>Si un producto con el mismo <code>codigo</code> ya existe, sus datos serán actualizados. Si no existe, se creará uno nuevo.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Importar Productos</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Script para mostrar el nombre del archivo en el input de tipo file (Bootstrap 4)
        $('.custom-file-input').on('change', function(event) {
            var inputFile = event.target;
            if (inputFile.files.length > 0) {
                var fileName = inputFile.files[0].name;
                $(inputFile).next('.custom-file-label').html(fileName);
            } else {
                $(inputFile).next('.custom-file-label').html('Elegir archivo...');
            }
        });
    });
</script>
@stop
