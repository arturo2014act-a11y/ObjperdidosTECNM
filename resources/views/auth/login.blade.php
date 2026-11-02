@extends('layouts.app')

@section('title', 'Iniciar Sesión - TecNM Mérida')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card card-custom bg-white border-0 shadow-sm p-4 mb-4">
            <div class="text-center mb-4">
                <i class="bi bi-person-circle fs-1 text-tecnm"></i>
                <h2 class="h4 fw-bold text-tecnm mt-2">Iniciar Sesión</h2>
                <p class="text-muted small">Acceso exclusivo con correo institucional @merida.tecnm.mx</p>
            </div>

            <form action="{{ route('login') }}" method="POST">
                @csrf

                {{-- Correo Institucional --}}
                <div class="mb-3">
                    <label for="email" class="form-label small fw-bold">Correo Institucional <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope-at text-muted"></i></span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            placeholder="ejemplo@merida.tecnm.mx" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Contraseña con Mostrar/Ocultar (RF-74) --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label small fw-bold mb-0">Contraseña <span class="text-danger">*</span></label>
                        <a href="{{ route('password.request') }}" class="small text-decoration-none text-primary">¿Olvidaste tu contraseña?</a>
                    </div>
                    <div class="input-group mt-1">
                        <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            placeholder="Tu contraseña" 
                            required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'toggleIcon')">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Recordarme --}}
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small text-muted" for="remember">
                        Recordar mis datos en este equipo
                    </label>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-tecnm py-2 fw-bold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                    </button>
                </div>

                <div class="text-center small text-muted">
                    ¿No tienes una cuenta aún? <a href="{{ route('register') }}" class="fw-bold text-tecnm text-decoration-none">Regístrate aquí</a>
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
</script>
@endpush
@endsection
