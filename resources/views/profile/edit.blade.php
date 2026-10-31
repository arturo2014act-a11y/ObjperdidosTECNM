@extends('layouts.app')

@section('title', 'Mi Perfil Institucional - TecNM')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 fw-bold text-tecnm mb-1">
                    <i class="bi bi-person-gear me-2 text-warning"></i>Mi Perfil Institucional
                </h1>
                <p class="text-muted small mb-0">
                    Administra tus datos personales y credenciales de acceso de TecNM Mérida (RF-81 a RF-85).
                </p>
            </div>
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Ir al Muro
            </a>
        </div>

        {{-- TARJETA 1: EDICIÓN EXCLUSIVA DE NOMBRE COMPLETO (RF-81, RF-82, RF-83) --}}
        <div class="card card-custom bg-white border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold text-dark mb-1">
                <i class="bi bi-person-badge me-2 text-primary"></i>Datos Personales
            </h5>
            <p class="text-muted small mb-3">
                (RF-83) Puedes modificar exclusivamente tu nombre completo. El correo institucional y rol son ineditables desde el perfil.
            </p>

            <form action="{{ route('profile.update-name') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Correo Ineditable (RF-83) --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Correo Institucional (Ineditable)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-muted"></i></span>
                        <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly disabled>
                    </div>
                    <div class="form-text small">Tu correo institucional autenticado no puede modificarse.</div>
                </div>

                {{-- Rol Actual --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Rol en el Sistema</label>
                    <div>
                        <span class="badge bg-secondary px-3 py-2 text-capitalize">
                            {{ str_replace('_', ' ', $user->role) }}
                        </span>
                    </div>
                </div>

                {{-- Nombre Completo (RF-81, RF-82) --}}
                <div class="mb-3">
                    <label for="name" class="form-label small fw-bold">
                        Nombre Completo <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="form-control @error('name', 'nameUpdate') is-invalid @enderror" 
                        value="{{ old('name', $user->name) }}" 
                        maxlength="100" 
                        required>
                    <div class="form-text small">
                        Máx. 100 caracteres. Solo letras, espacios, acentos, ñ, guiones y apóstrofes; sin números (RF-81, RF-82).
                    </div>
                    @error('name', 'nameUpdate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-tecnm btn-sm px-4 fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Guardar Cambios de Nombre
                    </button>
                </div>
            </form>
        </div>

        {{-- TARJETA 2: CAMBIO DE CONTRASEÑA EN PERFIL (RF-73, RF-74, RF-84, RF-85) --}}
        <div class="card card-custom bg-white border-0 shadow-sm p-4">
            <h5 class="fw-bold text-dark mb-1">
                <i class="bi bi-key-fill me-2 text-warning"></i>Seguridad y Contraseña
            </h5>
            <p class="text-muted small mb-3">
                (RF-84) Actualiza tu contraseña proporcionando tu clave actual y la nueva contraseña confirmada.
            </p>

            <form action="{{ route('profile.update-password') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Contraseña Actual --}}
                <div class="mb-3">
                    <label for="current_password" class="form-label small fw-bold">
                        Contraseña Actual <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="current_password" 
                        id="current_password" 
                        class="form-control @error('current_password', 'passwordUpdate') is-invalid @enderror" 
                        required>
                    @error('current_password', 'passwordUpdate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nueva Contraseña (RF-73) --}}
                <div class="mb-3">
                    <label for="password" class="form-label small fw-bold">
                        Nueva Contraseña <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="form-control @error('password', 'passwordUpdate') is-invalid @enderror" 
                        required>
                    <div class="form-text small">
                        Mínimo 8 caracteres, al menos 1 mayúscula, 1 minúscula, 1 número y 1 símbolo especial (RF-73).
                    </div>
                    @error('password', 'passwordUpdate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Confirmar Nueva Contraseña (RF-74) --}}
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label small fw-bold">
                        Confirmar Nueva Contraseña <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="password_confirmation" 
                        class="form-control" 
                        required>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-warning btn-sm px-4 fw-bold">
                        <i class="bi bi-shield-check me-1"></i> Actualizar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

