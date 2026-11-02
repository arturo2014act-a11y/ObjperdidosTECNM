@extends('layouts.app')

@section('title', $item->name . ' - Detalle de Objeto')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        {{-- Enlace de regreso --}}
        <div class="mb-3">
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Regresar al Muro
            </a>
        </div>

        <div class="card card-custom bg-white border-0 shadow-sm overflow-hidden mb-4">
            <div class="row g-0">
                {{-- Columna de Imagen --}}
                <div class="col-md-5 bg-light d-flex align-items-center justify-content-center p-3 text-center border-end">
                    @if ($item->image_path && Storage::disk('public')->exists($item->image_path))
                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="img-fluid rounded shadow-sm" style="max-height: 400px; object-fit: contain;">
                    @else
                        <div class="py-5 text-muted">
                            <i class="bi {{ $item->type === 'perdido' ? 'bi-question-circle' : 'bi-box-seam' }} display-1 opacity-25"></i>
                            <div class="small mt-2">Esta publicación no incluye imagen adjunta</div>
                        </div>
                    @endif
                </div>

                {{-- Columna de Detalles --}}
                <div class="col-md-7 p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            @if ($item->type === 'perdido')
                                <span class="badge bg-danger text-uppercase px-3 py-2 fw-bold">
                                    <i class="bi bi-search me-1"></i> Objeto Perdido
                                </span>
                            @else
                                <span class="badge bg-success text-uppercase px-3 py-2 fw-bold">
                                    <i class="bi bi-check-circle me-1"></i> Objeto Encontrado
                                </span>
                            @endif
                            <span class="badge bg-secondary ms-1">Folio #{{ $item->id }}</span>
                        </div>

                        <div>
                            @switch($item->status)
                                @case('publicado')
                                    <span class="badge bg-primary">Activo en Muro</span>
                                    @break
                                @case('pendiente')
                                    <span class="badge bg-warning text-dark">Pendiente de Recepción</span>
                                    @break
                                @case('devuelto')
                                    <span class="badge bg-success">Devuelto al Propietario</span>
                                    @break
                                @case('cerrado')
                                    <span class="badge bg-dark">Cerrado</span>
                                    @break
                                @case('rechazado')
                                    <span class="badge bg-danger">Rechazado</span>
                                    @break
                                @case('cierre_pendiente')
                                    <span class="badge bg-warning text-dark">Cierre Pendiente</span>
                                    @break
                            @endswitch
                        </div>
                    </div>

                    <h2 class="h4 fw-bold text-tecnm mb-3">{{ $item->name }}</h2>

                    <div class="mb-4">
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Descripción del Objeto:</h6>
                        <p class="text-dark bg-light p-3 rounded border">
                            {{ $item->description }}
                        </p>
                    </div>

                    {{-- Metadatos del Objeto --}}
                    <div class="row g-2 mb-4 small">
                        <div class="col-sm-6">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block"><i class="bi bi-tag text-warning me-1"></i>Categoría:</span>
                                <strong>{{ $item->category }}</strong>
                                @if ($item->category_specification)
                                    <span class="text-muted">({{ $item->category_specification }})</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block"><i class="bi bi-geo-alt text-danger me-1"></i>Ubicación:</span>
                                <strong>{{ $item->area }}</strong>
                                @if ($item->academic_class)
                                    <div class="text-muted small">Aula/Edificio: {{ $item->academic_class }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block"><i class="bi bi-calendar-event text-primary me-1"></i>Fecha del Suceso:</span>
                                <strong>{{ $item->event_date ? $item->event_date->format('d/m/Y') : 'No especificada' }}</strong>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block"><i class="bi bi-clock-history text-secondary me-1"></i>Fecha de Publicación:</span>
                                <strong>{{ $item->created_at->format('d/m/Y H:i') }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Privacidad del Autor (RF-43, RF-44) --}}
                    @if ($isStaff || $isAuthor)
                        <div class="author-box mb-3">
                            <span class="badge bg-dark mb-1" style="font-size: 0.7rem;">
                                <i class="bi bi-shield-lock-fill me-1"></i>Datos del Autor (Visible según RF-44)
                            </span>
                            <div class="fw-bold small text-dark">
                                <i class="bi bi-person-fill text-tecnm me-1"></i>{{ $item->user->name ?? 'Usuario no asignado' }}
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-envelope-at text-tecnm me-1"></i>{{ $item->user->email ?? 'Sin correo' }}
                            </div>
                        </div>
                    @else
                        {{-- Mensaje de Privacidad y Protocolo Presencial (RF-15, RF-43) --}}
                        <div class="alert alert-light border-warning small mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-shield-shaded text-warning fs-5"></i>
                                <strong class="text-dark">Protocolo de Recuperación en Prefectura</strong>
                            </div>
                            <p class="mb-0 text-muted">
                                Por políticas de privacidad y seguridad de la institución, la identidad de los autores no es pública. Para reclamar o entregar este bien, acude presencialmente al <strong>Módulo de Prefectura del Campus Poniente</strong> con tu credencial escolar y detalles que acrediten tu propiedad.
                            </p>
                        </div>
                    @endif

                    {{-- Acciones para el autor --}}
                    @if ($isAuthor && $item->type === 'perdido' && $item->status === 'publicado')
                        <div class="mt-auto pt-3 border-top">
                            <form action="{{ route('items.request-closure', $item) }}" method="POST" onsubmit="return confirm('¿Confirmas que ya recuperaste tu objeto para solicitar el cierre de la publicación?');">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                                    <i class="bi bi-check2-circle me-1"></i> Ya encontré mi objeto (Solicitar Cierre)
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

