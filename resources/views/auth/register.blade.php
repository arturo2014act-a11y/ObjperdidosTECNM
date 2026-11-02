@extends('layouts.app')

@section('title', 'Registro Institucional - TecNM Mérida')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-7 col-lg-6">
        <div class="card card-custom bg-white border-0 shadow-sm p-4 mb-4">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus fs-1 text-tecnm"></i>
                <h2 class="h4 fw-bold text-tecnm mt-2">Crear Cuenta Institucional</h2>
                <p class="text-muted small">Solo datos esenciales de acceso: nombre y correo institucional</p>
            </div>

            <form action="{{ route('register') }}" method="POST">
                @csrf

                {{-- Nombre Completo (RF-80, RF-81, RF-82) --}}
                <div class="mb-3">
                    <label for="name" class="form-label small fw-bold">Nombre Completo <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            class="form-control @error('name') is-invalid @enderror" 
                            placeholder="Nombre y Apellidos" 
                            value="{{ old('name') }}" 
                            maxlength="100" 
                            required 
                            autofocus>
                    </div>
                    <div class="form-text small">Máx. 100 caracteres. Solo letras, espacios, acentos, ñ, guiones y apóstrofes. Sin números.</div>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Correo Institucional (RF-25, RF-78, RF-79) --}}
                <div class="mb-3">
                    <label for="email" class="form-label small fw-bold">Correo Institucional <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope-at text-muted"></i></span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            placeholder="tu_usuario@merida.tecnm.mx" 
                            value="{{ old('email') }}" 
                            required>
                    </div>
                    <div class="form-text small">Dominio obligatorio: <code>@merida.tecnm.mx</code>.</div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Contraseña (RF-73, RF-74) --}}
                <div class="mb-3">
                    <label for="password" class="form-label small fw-bold">Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            placeholder="Crea una contraseña segura" 
                            required 
                            oninput="validatePasswordPolicy(this.value)">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'togglePassIcon')">
                            <i class="bi bi-eye" id="togglePassIcon"></i>
                        </button>
                    </div>
                    @error('password')
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

                {{-- Confirmar Contraseña (RF-74) --}}
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label small fw-bold">Confirmar Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-muted"></i></span>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation" 
                            class="form-control" 
                            placeholder="Repite la contraseña" 
                            required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', 'toggleConfirmIcon')">
                            <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-tecnm py-2 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Registrar Cuenta
                    </button>
                </div>

                <div class="text-center small text-muted">
                    ¿Ya tienes una cuenta registrada? <a href="{{ route('login') }}" class="fw-bold text-tecnm text-decoration-none">Inicia sesión</a>
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
        if (isValid) {
            el.className = 'text-success fw-semibold';
            el.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + el.textContent.trim();
        } else {
            el.className = 'text-danger';
            el.innerHTML = '<i class="bi bi-x-circle me-1"></i> ' + el.textContent.trim();
        }
    }
</script>
@endpush
@endsection
