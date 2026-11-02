@extends('layouts.app')

@section('title', 'Restablecer Contraseña - TecNM Mérida')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card card-custom bg-white border-0 shadow-sm p-4 mb-4">
            <div class="text-center mb-4">
                <i class="bi bi-key-fill fs-1 text-warning"></i>
                <h2 class="h4 fw-bold text-tecnm mt-2">Restablecer Contraseña</h2>
                <p class="text-muted small">Ingresa tu nueva contraseña para tu cuenta institucional.</p>
            </div>

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                {{-- Correo --}}
                <div class="mb-3">
                    <label for="email" class="form-label small fw-bold">Correo Institucional <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope-at text-muted"></i></span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email', $email) }}" 
                            required 
                            readonly>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nueva Contraseña (RF-73, RF-74) --}}
                <div class="mb-3">
                    <label for="password" class="form-label small fw-bold">Nueva Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            placeholder="Mínimo 8 caracteres, mayúscula, minúscula, número y símbolo" 
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
                    <label for="password_confirmation" class="form-label small fw-bold">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-muted"></i></span>
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

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-warning py-2 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> Restablecer Contraseña
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
