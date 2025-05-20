@extends('adminlte::page')

@section('title', 'Editar Producto: ' . $product->name)

@section('content_header')
    <h1 class="m-0 text-dark">Editar Producto: {{ $product->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> ¡Error de Validación!</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nombre del Producto <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="code">Código (SKU) <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $product->code) }}" required>
                    @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $product->description) }}</textarea>
                    @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cost">Costo <span class="text-danger">*</span></label>
                            <input type="number" name="cost" id="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ old('cost', $product->cost) }}" step="0.01" min="0" required>
                            @error('cost') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="unit_of_measure">Unidad de Medida</label>
                            <input type="text" name="unit_of_measure" id="unit_of_measure" class="form-control @error('unit_of_measure') is-invalid @enderror" value="{{ old('unit_of_measure', $product->unit_of_measure) }}" placeholder="Ej: Pza, Kg, Lt, Caja">
                            @error('unit_of_measure') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category_id">Categoría</label>
                            <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">-- Sin Categoría --</option>
                                {{-- $categories es pasada desde ProductController@edit --}}
                                @if(isset($categories) && $categories->count() > 0)
                                    @foreach($categories as $id => $categoryName)
                                        <option value="{{ $id }}" {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>{{ $categoryName }}</option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No hay categorías disponibles. Por favor, cree algunas primero.</option>
                                @endif
                            </select>
                            @error('category_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tax_type">Tipo de Impuesto <span class="text-danger">*</span></label>
                            <select name="tax_type" id="tax_type" class="form-control @error('tax_type') is-invalid @enderror" required>
                                <option value="Gravado" {{ old('tax_type', $product->tax_type) == 'Gravado' ? 'selected' : '' }}>Gravado</option>
                                <option value="Exento" {{ old('tax_type', $product->tax_type) == 'Exento' ? 'selected' : '' }}>Exento</option>
                                <option value="No Sujeto" {{ old('tax_type', $product->tax_type) == 'No Sujeto' ? 'selected' : '' }}>No Sujeto</option>
                            </select>
                            @error('tax_type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image_path_file">Imagen del Producto</label>
                    <div class="custom-file">
                        <input type="file" name="image_path_file" class="custom-file-input @error('image_path_file') is-invalid @enderror" id="image_path_file">
                        <label class="custom-file-label" for="image_path_file">Elegir archivo nuevo...</label>
                    </div>
                    @error('image_path_file') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    @if($product->image_path)
                        <div class="mt-2">
                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" style="max-height: 100px; border: 1px solid #ccc; padding: 5px;">
                            <p class="text-muted"><small>Imagen actual. Subir una nueva la reemplazará.</small></p>
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar Producto</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
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
                $(inputFile).next('.custom-file-label').html('Elegir archivo nuevo...');
            }
        });
    });
</script>
@stop
