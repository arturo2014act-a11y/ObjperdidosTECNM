@extends('layouts.app')

@section('title', 'Recuperar Contraseña - TecNM Mérida')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card card-custom bg-white border-0 shadow-sm p-4 mb-4">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock fs-1 text-warning"></i>
                <h2 class="h4 fw-bold text-tecnm mt-2">Recuperar Contraseña</h2>
                <p class="text-muted small">Ingresa tu correo institucional y te enviaremos un enlace de recuperación temporal.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-info small shadow-sm mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf

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
                            required 
                            autofocus>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-warning py-2 fw-bold">
                        <i class="bi bi-send-fill me-1"></i> Enviar Enlace de Recuperación
                    </button>
                </div>

                <div class="text-center small text-muted">
                    <a href="{{ route('login') }}" class="text-decoration-none text-tecnm fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Regresar al Inicio de Sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
