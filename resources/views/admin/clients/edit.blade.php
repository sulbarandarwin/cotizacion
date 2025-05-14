@extends('adminlte::page')

@section('title', 'Editar Cliente')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Cliente: {{ $client->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('clients.update', $client) }}" method="POST">
                @csrf {{-- Protección CSRF --}}
                @method('PUT') {{-- Método HTTP para actualizar --}}

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Nombre / Razón Social <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $client->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="identifier">RIF / Cédula <span class="text-danger">*</span></label>
                            <input type="text" name="identifier" id="identifier" class="form-control @error('identifier') is-invalid @enderror" value="{{ old('identifier', $client->identifier) }}" required>
                            @error('identifier')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Dirección</label>
                    <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $client->address) }}</textarea>
                    @error('address')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $client->phone) }}">
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $client->email) }}">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="contact_person">Persona de Contacto</label>
                    <input type="text" name="contact_person" id="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person', $client->contact_person) }}">
                    @error('contact_person')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Cliente
                    </button>
                    <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop