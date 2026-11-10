@extends('layouts.app')

@section('title', 'Mis Publicaciones - Objetos Perdidos TecNM')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h1 class="h3 fw-bold text-tecnm mb-1">
                <i class="bi bi-card-checklist me-2 text-warning"></i>Mis Publicaciones
            </h1>
            <p class="text-muted mb-0 small">
                Consulta el estado de tus objetos reportados, motivos de revisión y solicita el cierre de tus pertenencias.
            </p>
        </div>
        {{-- TEMPORALMENTE DESHABILITADO --}}
        <button class="btn btn-warning fw-bold btn-sm shadow-sm" disabled title="Funci&oacute;n temporalmente deshabilitada">
            <i class="bi bi-plus-circle me-1"></i> Publicar Nuevo Objeto
        </button>
    </div>
</div>

<div class="card card-custom bg-white border-0 shadow-sm">
    <div class="card-body p-0">
        @if ($myItems->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-folder-x display-3 text-muted"></i>
                <h5 class="fw-bold text-dark mt-3">Aún no tienes publicaciones registradas</h5>
                <p class="text-muted small mb-4">
                    Cuando reportes un objeto perdido o encontrado en el campus, podrás darle seguimiento aquí.
                </p>
                {{-- TEMPORALMENTE DESHABILITADO --}}
                <button class="btn btn-tecnm btn-sm" disabled title="Funci&oacute;n temporalmente deshabilitada">
                    <i class="bi bi-pencil-square me-1"></i> Crear mi Primer Reporte
                </button>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4">Folio</th>
                            <th>Tipo</th>
                            <th>Objeto y Ubicación</th>
                            <th>Fecha</th>
                            <th>Estado Actual</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($myItems as $item)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-secondary">#{{ $item->id }}</span>
                                </td>
                                <td>
                                    @if ($item->type === 'perdido')
                                        <span class="badge bg-danger text-uppercase">Perdido</span>
                                    @else
                                        <span class="badge bg-success text-uppercase">Encontrado</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-tecnm">{{ $item->name }}</div>
                                    <div class="small text-muted">
                                        <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $item->area }}
                                        • <i class="bi bi-tag me-1 text-secondary"></i>{{ $item->category }}
                                    </div>

                                    {{-- Si fue rechazado, mostrar el motivo obligatorio de prefectura (RF-21) --}}
                                    @if ($item->status === 'rechazado' && $item->rejection_reason)
                                        <div class="alert alert-danger py-1 px-2 mt-2 mb-0 small" role="alert">
                                            <strong><i class="bi bi-x-circle me-1"></i>Motivo del rechazo de Prefectura:</strong>
                                            {{ $item->rejection_reason }}
                                        </div>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $item->created_at->format('d/m/Y') }}
                                </td>
                                <td>
                                    @switch($item->status)
                                        @case('publicado')
                                            <span class="badge bg-primary">
                                                <i class="bi bi-check2 me-1"></i>Publicado / Activo
                                            </span>
                                            @break
                                        @case('pendiente')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-hourglass-split me-1"></i>Pendiente de Aprobación
                                            </span>
                                            <div class="text-muted fst-italic" style="font-size: 0.75rem;">
                                                Entrega física en Prefectura requerida
                                            </div>
                                            @break
                                        @case('cierre_pendiente')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock-history me-1"></i>Cierre pendiente de revisión
                                            </span>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                Prefectura está validando tu solicitud
                                            </div>
                                            @break
                                        @case('cerrado')
                                            <span class="badge bg-dark">
                                                <i class="bi bi-door-closed me-1"></i>Cerrado
                                            </span>
                                            @break
                                        @case('devuelto')
                                            <span class="badge bg-success">
                                                <i class="bi bi-box2-heart me-1"></i>Devuelto al Propietario
                                            </span>
                                            @break
                                        @case('rechazado')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-slash-circle me-1"></i>Rechazado
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <a href="{{ route('items.show', $item) }}" class="btn btn-outline-secondary btn-sm" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- Botón "Ya encontré mi objeto" para Perdido Activo (RF-17) --}}
                                        @if ($item->type === 'perdido' && $item->status === 'publicado')
                                            <button type="button" class="btn btn-success btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalRequestClosure{{ $item->id }}">
                                                <i class="bi bi-check2-circle me-1"></i> Ya encontré mi objeto
                                            </button>
                                        @endif

                                        {{-- Si fue rechazado, opción de crear uno nuevo corregido (RF-21) --}}
                                        {{-- TEMPORALMENTE DESHABILITADO --}}
                                        @if ($item->status === 'rechazado')
                                            <button class="btn btn-outline-primary btn-sm" disabled title="Funci&oacute;n temporalmente deshabilitada">
                                                <i class="bi bi-arrow-repeat me-1"></i> Nuevo Reporte
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- MODAL SOLICITUD DE CIERRE ("YA ENCONTRÉ MI OBJETO" RF-17) --}}
                            @if ($item->type === 'perdido' && $item->status === 'publicado')
                                <div class="modal fade" id="modalRequestClosure{{ $item->id }}" tabindex="-1" aria-labelledby="modalRequestClosureLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('items.request-closure', $item) }}" method="POST" class="modal-content">
                                            @csrf
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title fs-6 fw-bold" id="modalRequestClosureLabel{{ $item->id }}">
                                                    <i class="bi bi-check-circle me-1"></i> Confirmar Recuperación del Objeto
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <p class="mb-2">
                                                    ¿Has recuperado tu objeto: <strong>{{ $item->name }}</strong>?
                                                </p>
                                                <div class="alert alert-light border-success small mb-0">
 Al confirmar, tu publicación pasará a estado <strong>«Cierre pendiente de revisión»</strong>. Prefectura validará la solicitud para retirar definitivamente el reporte del muro.
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check2-circle me-1"></i> Sí, solicitar cierre
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <small class="text-muted">Mostrando {{ $myItems->firstItem() ?? 0 }} a {{ $myItems->lastItem() ?? 0 }} de {{ $myItems->total() }} reportes</small>
                {{ $myItems->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

