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
                    Administra tus datos personales y credenciales de acceso de TecNM Mérida.
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
 Puedes modificar exclusivamente tu nombre completo. El correo institucional y rol son ineditables desde el perfil.
            </p>

            <div>



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
                {{-- Nombre Completo Ineditable --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nombre Completo (Ineditable)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person-fill text-muted"></i></span>
                        <input type="text" class="form-control bg-light" value="{{ $user->name }}" readonly disabled>
                    </div>
                    <div class="form-text small">Tu nombre institucional registrado no puede modificarse directamente.</div>
                </div>
            </div>
        </div>

        {{-- TARJETA 2: CAMBIO DE CONTRASEÑA EN PERFIL (RF-73, RF-74, RF-84, RF-85) --}}
        <div class="card card-custom bg-white border-0 shadow-sm p-4">
            <h5 class="fw-bold text-dark mb-1">
                <i class="bi bi-key-fill me-2 text-warning"></i>Seguridad y Contraseña
            </h5>
            <p class="text-muted small mb-3">
 Actualiza tu contraseña proporcionando tu clave actual y la nueva contraseña confirmada.
            </p>

            <form action="{{ route('profile.update-password') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Contraseña Actual (RF-74, RF-84) --}}
                <div class="mb-3">
                    <label for="current_password" class="form-label small fw-bold">
                        Contraseña Actual <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                        <input
                            type="password"
                            name="current_password"
                            id="current_password"
                            class="form-control @error('current_password', 'passwordUpdate') is-invalid @enderror"
                            required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('current_password', 'toggleCurrentPassIcon')">
                            <i class="bi bi-eye" id="toggleCurrentPassIcon"></i>
                        </button>
                    </div>
                    @error('current_password', 'passwordUpdate')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nueva Contraseña (RF-73, RF-74) --}}
                <div class="mb-3">
                    <label for="password" class="form-label small fw-bold">
                        Nueva Contraseña <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-shield-lock text-muted"></i></span>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control @error('password', 'passwordUpdate') is-invalid @enderror"
                            placeholder="Mínimo 8 caracteres, mayúscula, minúscula, número y símbolo"
                            required
                            oninput="validatePasswordPolicy(this.value)">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'togglePassIcon')">
                            <i class="bi bi-eye" id="togglePassIcon"></i>
                        </button>
                    </div>
                    @error('password', 'passwordUpdate')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    {{-- Indicadores dinámicos de política de contraseña (RF-73) --}}
                    <div class="card bg-light border-0 p-2 mt-2">
                        <div class="small fw-bold text-muted mb-1">Requisitos de la contraseña:</div>
                        <ul class="list-unstyled mb-0 small ps-1" style="font-size: 0.8rem;">
                            <li id="rule-len" class="text-danger"><i class="bi bi-x-circle me-1"></i> Mínimo 8 caracteres</li>
                            <li id="rule-upper" class="text-danger"><i class="bi bi-x-circle me-1"></i> Al menos una letra mayúscula</li>
                            <li id="rule-lower" class="text-danger"><i class="bi bi-x-circle me-1"></i> Al menos una letra minúscula</li>
                            <li id="rule-num" class="text-danger"><i class="bi bi-x-circle me-1"></i> Al menos un número</li>
                            <li id="rule-sym" class="text-danger"><i class="bi bi-x-circle me-1"></i> Al menos un carácter especial (!@#$%^&*)</li>
                        </ul>
                    </div>
                </div>

                {{-- Confirmar Nueva Contraseña (RF-74) --}}
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label small fw-bold">
                        Confirmar Nueva Contraseña <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-shield-check text-muted"></i></span>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="form-control"
                            placeholder="Repite la nueva contraseña"
                            required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', 'toggleConfirmIcon')">
                            <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                        </button>
                    </div>
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
@push('scripts')
<script>
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    function validatePasswordPolicy(val) {
        updateRule('rule-len', val.length >= 8);
        updateRule('rule-upper', /[A-Z]/.test(val));
        updateRule('rule-lower', /[a-z]/.test(val));
        updateRule('rule-num', /[0-9]/.test(val));
        updateRule('rule-sym', /[^A-Za-z0-9]/.test(val));
    }

    function updateRule(elementId, isValid) {
        const el = document.getElementById(elementId);
        if (!el) return;
        const text = el.textContent.trim().replace(/^[\s\S]*?\s(?=[A-Za-z0-9ÁÉÍÓÚáéíóúM])/, '');
        if (isValid) {
            el.className = 'text-success fw-semibold';
            el.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + (text || el.innerText);
        } else {
            el.className = 'text-danger';
            el.innerHTML = '<i class="bi bi-x-circle me-1"></i> ' + (text || el.innerText);
        }
    }
</script>
@endpush
@endsection

