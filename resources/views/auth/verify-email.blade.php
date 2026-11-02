@extends('layouts.app')

@section('title', 'Verificar Correo Institucional - TecNM Mérida')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-7 col-lg-6">
        <div class="card card-custom bg-white border-0 shadow-sm p-4 text-center">
            <div class="mb-3">
                <i class="bi bi-envelope-exclamation fs-1 text-warning"></i>
            </div>
            
            <h2 class="h4 fw-bold text-tecnm mb-2">Acceso restringido</h2>
            <p class="text-secondary">
                Debes verificar tu correo institucional para continuar y acceder a todas las funciones protegidas de la plataforma.
            </p>

            @if (session('status'))
                <div class="alert alert-success small shadow-sm my-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('status') }}
                </div>
            @endif

            <div class="alert alert-light border small text-muted my-3">
                Hemos enviado un enlace de confirmación a tu correo <strong>{{ Auth::user()->email ?? '' }}</strong>. Si no lo has recibido, revisa tu carpeta de spam o haz clic en el siguiente botón para solicitar otro.
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mt-3">
                <form action="{{ route('verification.send') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning fw-bold w-100">
                        <i class="bi bi-arrow-repeat me-1"></i> Reenviar verificación
                    </button>
                </form>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
