@extends('layouts.app')

@section('title', 'Panel de Control de Prefectura - TecNM Mérida')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h2 class="h3 fw-bold text-tecnm mb-1">
                <i class="bi bi-clipboard2-check me-2"></i>Módulo de Prefectura y Control de Bienes
            </h2>
            <p class="text-muted mb-0 small">
                Gestión, validación presencial de pertenencias y bitácora de auditoría interna de TecNM Mérida.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @auth
                <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                    <i class="bi bi-person-gear"></i>
                    <span>Rol Actual: <strong>{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</strong></span>
                </button>
            @endauth
        </div>
    </div>

    @auth
        @if (Auth::user()->role === 'usuario_general')
            <div class="col-12 mt-3">
                <div class="alert alert-info d-flex align-items-center justify-content-between py-2 px-3 shadow-sm" role="alert">
                    <div class="small">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Estás explorando con el rol de <strong>Usuario General</strong> ({{ Auth::user()->email }}). En producción este panel requiere rol Prefectura. Puedes cambiar de rol cuando gustes.
                    </div>
                    <button type="button" class="btn btn-sm btn-info text-white fw-bold py-1 px-3" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                        Cambiar a Prefectura
                    </button>
                </div>
            </div>
        @endif
    @endauth
</div>

{{-- Pestañas de Navegación de Secciones (RF-50, RF-51, RF-56) --}}
<ul class="nav nav-pills nav-fill bg-white p-2 rounded-3 shadow-sm mb-4" id="prefecturaTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold position-relative py-3" id="found-tab" data-bs-toggle="tab" data-bs-target="#found-pane" type="button" role="tab">
            <i class="bi bi-box-arrow-in-down me-2 text-warning"></i> 1. Encontrados por Aprobar
            @if ($pendingFound->count() > 0)
                <span class="badge bg-danger ms-2 rounded-pill">{{ $pendingFound->count() }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        {{-- TEMPORALMENTE DESHABILITADO --}}
        <button class="nav-link fw-semibold position-relative py-3 disabled" id="closure-tab" type="button" role="tab" disabled title="Funci&oacute;n temporalmente deshabilitada">
            <i class="bi bi-check2-circle me-2 text-info"></i> 2. Solicitudes de Cierre
            @if ($pendingClosures->count() > 0)
                <span class="badge bg-warning text-dark ms-2 rounded-pill">{{ $pendingClosures->count() }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        {{-- TEMPORALMENTE DESHABILITADO --}}
        <button class="nav-link fw-semibold position-relative py-3 disabled" id="management-tab" type="button" role="tab" disabled title="Funci&oacute;n temporalmente deshabilitada">
            <i class="bi bi-folder2-open me-2 text-success"></i> 3. Publicaciones bajo Gestión
            <span class="badge bg-secondary ms-2 rounded-pill">{{ $managedItems->total() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        {{-- TEMPORALMENTE DESHABILITADO --}}
        <button class="nav-link fw-semibold position-relative py-3 disabled" id="audit-tab" type="button" role="tab" disabled title="Funci&oacute;n temporalmente deshabilitada">
            <i class="bi bi-journal-text me-2 text-dark"></i> Bitácora de Auditoría
        </button>
    </li>
</ul>

<div class="tab-content" id="prefecturaTabsContent">

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 1: OBJETOS ENCONTRADOS PENDIENTES DE APROBACIÓN (RF-50, RF-53, RF-21) --}}
    {{-- ========================================================================= --}}
    <div class="tab-pane fade show active" id="found-pane" role="tabpanel" tabindex="0">
        <div class="card card-custom mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-tecnm">
                        <i class="bi bi-hourglass-split me-2 text-warning"></i>Objetos Encontrados Pendientes de Aprobación
                    </h5>
                    <small class="text-muted">
                        Publicaciones de objetos encontrados reportados por usuarios generales, pendientes de entrega física y validación presencial en Prefectura (RF-50, RF-53).
                    </small>
                </div>
                <span class="badge bg-warning text-dark px-3 py-2">
                    {{ $pendingFound->count() }} por revisar
                </span>
            </div>

            <div class="card-body">
                @if ($pendingFound->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No hay objetos encontrados pendientes de recepción física en este momento.</p>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach ($pendingFound as $item)
                            <div class="col-md-6 col-xl-4">
                                <div class="card h-100 border-warning shadow-sm">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <span class="badge bg-warning text-dark fw-bold text-uppercase">
                                            <i class="bi bi-search me-1"></i> {{ $item->type }}
                                        </span>
                                        <span class="badge bg-secondary">Folio #{{ $item->id }}</span>
                                    </div>
                                    <div class="card-body">
                                        {{-- Cuadro de Imagen del Objeto --}}
                                        <div class="rounded overflow-hidden mb-3 bg-light border d-flex align-items-center justify-content-center" style="height: 190px;">
                                            @if ($item->image_path && Storage::disk('public')->exists($item->image_path))
                                                <img src="{{ asset('storage/' . $item->image_path) }}" 
                                                     alt="{{ $item->name }}" 
                                                     class="w-100 h-100 object-fit-cover"
                                                     style="cursor: pointer;"
                                                     onclick="window.open(this.src, '_blank')"
                                                     title="Clic para ver en tamaño completo">
                                            @else
                                                <div class="text-center text-muted p-3">
                                                    <i class="bi bi-image fs-1 opacity-50 d-block mb-1"></i>
                                                    <span class="small">Sin fotografía adjunta</span>
                                                </div>
                                            @endif
                                        </div>

                                        <h5 class="card-title fw-bold text-tecnm mb-2">{{ $item->name }}</h5>
                                        <p class="card-text text-muted small mb-3">
                                            {{ Str::limit($item->description, 180) }}
                                        </p>

                                        <div class="mb-2 small">
                                            <strong><i class="bi bi-tag me-1 text-secondary"></i>Categoría:</strong>
                                            {{ $item->category }} {{ $item->category_specification ? "({$item->category_specification})" : '' }}
                                        </div>
                                        <div class="mb-2 small">
                                            <strong><i class="bi bi-geo-alt me-1 text-danger"></i>Ubicación:</strong>
                                            {{ $item->area }} {{ $item->academic_class ? "- Aula/Edif: {$item->academic_class}" : '' }}
                                        </div>
                                        <div class="mb-3 small">
                                            <strong><i class="bi bi-calendar-event me-1 text-primary"></i>Fecha evento:</strong>
                                            {{ $item->event_date ? $item->event_date->format('d/m/Y') : 'No especificada' }}
                                        </div>

                                        {{-- PRIVACIDAD DE DATOS DEL AUTOR (RF-44) --}}
                                        <div class="author-box mb-3">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="badge bg-dark" style="font-size: 0.7rem;">
                                                    <i class="bi bi-eye-fill me-1"></i>Visible solo para Prefectura
                                                </span>
                                            </div>
                                            <div class="fw-semibold text-dark small">
                                                <i class="bi bi-person-fill text-tecnm me-1"></i>{{ $item->user->name ?? 'Usuario no asignado' }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-envelope-at text-tecnm me-1"></i>{{ $item->user->email ?? 'Sin correo' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-footer bg-white border-top-0 pt-0 pb-3 d-flex gap-2">
                                        {{-- Botón Modal Aprobar --}}
                                        <button type="button" class="btn btn-sm btn-success flex-grow-1" data-bs-toggle="modal" data-bs-target="#modalApproveFound{{ $item->id }}">
                                            <i class="bi bi-check-lg me-1"></i> Aprobar Recepción
                                        </button>
                                        {{-- Botón Modal Rechazar --}}
                                        <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1" data-bs-toggle="modal" data-bs-target="#modalRejectFound{{ $item->id }}">
                                            <i class="bi bi-x-lg me-1"></i> Rechazar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL APROBACIÓN (RF-53: CHECKBOX OBLIGATORIO DE RECEPCIÓN FÍSICA PARA ENCONTRADOS) --}}
                            <div class="modal fade" id="modalApproveFound{{ $item->id }}" tabindex="-1" aria-labelledby="modalApproveFoundLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('prefectura.items.approve-found', $item) }}" method="POST" class="modal-content">
                                        @csrf
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title fs-6 fw-bold" id="modalApproveFoundLabel{{ $item->id }}">
                                                <i class="bi bi-check-circle me-1"></i> Confirmar Aprobación - Folio #{{ $item->id }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="mb-2">Vas a aprobar el reporte de objeto encontrado: <strong>{{ $item->name }}</strong>.</p>
                                            <p class="small text-muted mb-3">Una vez aprobado, pasará al estado <strong>Publicado</strong> y se mostrará en el muro institucional.</p>
                                            
                                            <div class="alert alert-light border-warning p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="confirm_physical_receipt" id="confirm_physical_receipt_{{ $item->id }}" value="1" required>
                                                    <label class="form-check-label fw-bold text-dark small" for="confirm_physical_receipt_{{ $item->id }}">
                                                        (RF-53) Confirmo formalmente que el objeto ha sido entregado en custodia física en el módulo de Prefectura del TecNM.
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="bi bi-check-circle me-1"></i> Confirmar y Publicar en Muro
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- MODAL RECHAZO (RF-21: MOTIVO OBLIGATORIO) --}}
                            <div class="modal fade" id="modalRejectFound{{ $item->id }}" tabindex="-1" aria-labelledby="modalRejectFoundLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('prefectura.items.reject-found', $item) }}" method="POST" class="modal-content">
                                        @csrf
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title fs-6 fw-bold" id="modalRejectFoundLabel{{ $item->id }}">
                                                <i class="bi bi-x-circle me-1"></i> Rechazar Publicación - Folio #{{ $item->id }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="small text-muted mb-3">
                                                Al rechazar la publicación, no se mostrará en el muro y se le notificará al usuario el motivo ingresado.
                                            </p>
                                            <div class="mb-3">
                                                <label for="rejection_reason_{{ $item->id }}" class="form-label fw-bold small">
                                                    Motivo obligatorio de rechazo <span class="text-danger">*</span>
                                                </label>
                                                <textarea 
                                                    name="rejection_reason" 
                                                    id="rejection_reason_{{ $item->id }}" 
                                                    rows="3" 
                                                    class="form-control" 
                                                    placeholder="Ej: El objeto no fue entregado físicamente en la oficina, descripción ambigua, o no cumple los lineamientos institucionales..." 
                                                    required 
                                                    minlength="5" 
                                                    maxlength="500"></textarea>
                                                <div class="form-text small">Mínimo 5 caracteres y máximo 500.</div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-x-circle me-1"></i> Confirmar Rechazo
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 2: SOLICITUDES DE CIERRE PENDIENTES (RF-50)                        --}}
    {{-- ========================================================================= --}}
    <div class="tab-pane fade" id="closure-pane" role="tabpanel" tabindex="0">
        <div class="card card-custom mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-tecnm">
                        <i class="bi bi-door-closed me-2 text-info"></i>Solicitudes de Cierre de Objetos Perdidos
                    </h5>
                    <small class="text-muted">
                        Publicaciones donde el autor reportó haber encontrado su pertenencia ("Ya encontré mi objeto") y solicitó cerrar el caso.
                    </small>
                </div>
                <span class="badge bg-info text-dark px-3 py-2">
                    {{ $pendingClosures->count() }} solicitudes
                </span>
            </div>

            <div class="card-body">
                @if ($pendingClosures->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-check-all fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No hay solicitudes de cierre pendientes por validar.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Folio</th>
                                    <th>Objeto</th>
                                    <th>Ubicación</th>
                                    <th>Datos del Autor</th>
                                    <th>Fecha Reporte</th>
                                    <th class="text-end">Acciones de Prefectura</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingClosures as $item)
                                    <tr>
                                        <td><span class="badge bg-secondary">#{{ $item->id }}</span></td>
                                        <td>
                                            <div class="fw-bold text-tecnm">{{ $item->name }}</div>
                                            <small class="text-muted">{{ Str::limit($item->description, 60) }}</small>
                                        </td>
                                        <td>
                                            <span class="small">{{ $item->area }}</span>
                                            @if ($item->academic_class)
                                                <div class="small text-muted">{{ $item->academic_class }}</div>
                                            @endif
                                        </td>
                                        {{-- PRIVACIDAD DE DATOS (RF-44) --}}
                                        <td>
                                            <div class="author-box py-1 px-2">
                                                <div class="fw-bold small text-dark">{{ $item->user->name ?? 'N/D' }}</div>
                                                <div class="small text-muted">{{ $item->user->email ?? 'N/D' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="small text-muted">{{ $item->updated_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                {{-- Formulario Aprobar Cierre --}}
                                                <form action="{{ route('prefectura.items.approve-closure', $item) }}" method="POST" onsubmit="return confirm('¿Aprobar el cierre definitivo de esta publicación?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="bi bi-check2-circle me-1"></i> Aprobar Cierre
                                                    </button>
                                                </form>

                                                {{-- Botón Modal Rechazar Cierre con Motivo --}}
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRejectClosure{{ $item->id }}">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Rechazar Cierre
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- MODAL RECHAZO DE CIERRE (EXIGE MOTIVO OBLIGATORIO) --}}
                                    <div class="modal fade" id="modalRejectClosure{{ $item->id }}" tabindex="-1" aria-labelledby="modalRejectClosureLabel{{ $item->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="{{ route('prefectura.items.reject-closure', $item) }}" method="POST" class="modal-content">
                                                @csrf
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title fs-6 fw-bold" id="modalRejectClosureLabel{{ $item->id }}">
                                                        <i class="bi bi-exclamation-triangle me-1"></i> Rechazar Cierre - Folio #{{ $item->id }}
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="small text-muted mb-2">
                                                        Al rechazar la solicitud de cierre, la publicación regresará al estado <strong>Activo</strong> (publicado en el muro).
                                                    </p>
                                                    <div class="mb-3">
                                                        <label for="rejection_closure_reason_{{ $item->id }}" class="form-label fw-bold small">
                                                            Motivo obligatorio del rechazo de cierre <span class="text-danger">*</span>
                                                        </label>
                                                        <textarea 
                                                            name="rejection_reason" 
                                                            id="rejection_closure_reason_{{ $item->id }}" 
                                                            rows="3" 
                                                            class="form-control" 
                                                            placeholder="Indique la justificación para no proceder con el cierre..." 
                                                            required 
                                                            minlength="5" 
                                                            maxlength="500"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        Rechazar y Reactivar en Muro
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 3: PUBLICACIONES BAJO GESTIÓN (RF-51, RF-14, RF-54, RF-55)        --}}
    {{-- ========================================================================= --}}
    <div class="tab-pane fade" id="management-pane" role="tabpanel" tabindex="0">
        <div class="card card-custom mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-tecnm">
                    <i class="bi bi-sliders me-2 text-primary"></i>Publicaciones bajo Gestión del Campus
                </h5>
                <small class="text-muted">
                    Búsqueda, control de entrega presencial, reversión de devoluciones y filtrado administrativo.
                </small>
            </div>

            <div class="card-body">
                {{-- Formulario de Filtros y Búsqueda (RF-51) --}}
                <form action="{{ route('prefectura.dashboard') }}" method="GET" class="row g-2 mb-4 bg-light p-3 rounded-3 border">
                    <div class="col-md-3">
                        <label for="filter_type" class="form-label small fw-semibold text-muted">Tipo de Objeto</label>
                        <select name="type" id="filter_type" class="form-select form-select-sm">
                            <option value="">-- Todos los tipos --</option>
                            <option value="perdido" {{ request('type') === 'perdido' ? 'selected' : '' }}>Perdido</option>
                            <option value="encontrado" {{ request('type') === 'encontrado' ? 'selected' : '' }}>Encontrado</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_status" class="form-label small fw-semibold text-muted">Estado del Objeto</label>
                        <select name="status" id="filter_status" class="form-select form-select-sm">
                            <option value="">-- Todos los estados --</option>
                            <option value="activo" {{ request('status') === 'activo' || request('status') === 'publicado' ? 'selected' : '' }}>Activo / Publicado</option>
                            <option value="devuelto" {{ request('status') === 'devuelto' ? 'selected' : '' }}>Devuelto</option>
                            <option value="rechazado" {{ request('status') === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                            <option value="cerrado" {{ request('status') === 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="filter_search" class="form-label small fw-semibold text-muted">Buscador por Nombre</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="filter_search" class="form-control" placeholder="Ej: Calculadora, Credencial, Mochila..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-sm btn-tecnm flex-grow-1">
                            <i class="bi bi-funnel-fill me-1"></i> Filtrar
                        </button>
                        @if (request()->hasAny(['type', 'status', 'search']))
                            <a href="{{ route('prefectura.dashboard') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </form>

                {{-- Tabla de Publicaciones --}}
                @if ($managedItems->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-search fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No se encontraron publicaciones con los criterios seleccionados.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th>Folio</th>
                                    <th>Tipo</th>
                                    <th>Objeto</th>
                                    <th>Ubicación</th>
                                    <th>Estado Actual</th>
                                    <th>Datos del Autor</th>
                                    <th class="text-end">Operaciones Prefectura</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($managedItems as $item)
                                    <tr>
                                        <td><span class="badge bg-secondary">#{{ $item->id }}</span></td>
                                        <td>
                                            @if ($item->type === 'perdido')
                                                <span class="badge bg-danger text-uppercase">Perdido</span>
                                            @else
                                                <span class="badge bg-success text-uppercase">Encontrado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-tecnm">{{ $item->name }}</div>
                                            <small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                            @if ($item->rejection_reason)
                                                <div class="small text-danger fst-italic mt-1">
                                                    <strong>Motivo:</strong> {{ $item->rejection_reason }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small">{{ $item->area }}</span>
                                        </td>
                                        <td>
                                            @switch($item->status)
                                                @case('publicado')
                                                    <span class="badge bg-primary">Activo / Muro</span>
                                                    @break
                                                @case('devuelto')
                                                    <span class="badge bg-success"><i class="bi bi-box2-heart me-1"></i>Devuelto</span>
                                                    @break
                                                @case('rechazado')
                                                    <span class="badge bg-danger">Rechazado</span>
                                                    @break
                                                @case('cerrado')
                                                    <span class="badge bg-dark">Cerrado</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                                            @endswitch
                                        </td>

                                        {{-- PRIVACIDAD DE DATOS (RF-44) --}}
                                        <td>
                                            <div class="author-box py-1 px-2">
                                                <div class="fw-bold small text-dark">
                                                    <i class="bi bi-person me-1"></i>{{ $item->user->name ?? 'N/D' }}
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="bi bi-envelope me-1"></i>{{ $item->user->email ?? 'N/D' }}
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                {{-- ACCIÓN: Marcar como Devuelto (RF-14, RF-54) --}}
                                                @if ($item->type === 'encontrado' && in_array($item->status, ['publicado', 'pendiente']))
                                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalMarkReturned{{ $item->id }}" title="Marcar como Devuelto presencialmente">
                                                        <i class="bi bi-box2-check me-1"></i> Marcar Devuelto
                                                    </button>
                                                @endif

                                                {{-- ACCIÓN: Revertir Devuelto a Activo (RF-55) --}}
                                                @if ($item->status === 'devuelto')
                                                    <button type="button" class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalRevertReturned{{ $item->id }}" title="Revertir entrega con motivo obligatorio">
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Revertir a Activo
                                                    </button>
                                                @endif

                                                {{-- Ver Historial de Auditoría individual (RF-56) --}}
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalItemHistory{{ $item->id }}" title="Ver historial de auditoría de este objeto">
                                                    <i class="bi bi-clock-history"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- MODAL MARCAR COMO DEVUELTO (RF-14, RF-54: CHECKBOX PRESENCIAL OBLIGATORIO) --}}
                                    @if ($item->type === 'encontrado' && in_array($item->status, ['publicado', 'pendiente']))
                                        <div class="modal fade" id="modalMarkReturned{{ $item->id }}" tabindex="-1" aria-labelledby="modalMarkReturnedLabel{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('prefectura.items.mark-returned', $item) }}" method="POST" class="modal-content">
                                                    @csrf
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title fs-6 fw-bold" id="modalMarkReturnedLabel{{ $item->id }}">
                                                            <i class="bi bi-hand-thumbs-up me-1"></i> Registrar Entrega al Propietario - Folio #{{ $item->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-2">Estás registrando la devolución del objeto <strong>{{ $item->name }}</strong>.</p>
                                                        
                                                        <div class="alert alert-light border-success p-3 mb-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="confirm_in_person_delivery" id="confirm_in_person_delivery_{{ $item->id }}" value="1" required>
                                                                <label class="form-check-label fw-bold text-dark small" for="confirm_in_person_delivery_{{ $item->id }}">
 Confirmo que el propietario acudió presencialmente a Prefectura, acreditó su identidad/propiedad y recibió el bien.
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div class="mb-2">
                                                            <label for="delivery_notes_{{ $item->id }}" class="form-label small fw-semibold">Notas adicionales de entrega (opcional):</label>
                                                            <textarea name="delivery_notes" id="delivery_notes_{{ $item->id }}" rows="2" class="form-control form-control-sm" placeholder="Ej: Se cotejó con credencial escolar o factura de compra..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            Confirmar Devolución
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- MODAL REVERTIR DEVOLUCIÓN A ACTIVO (RF-55: MOTIVO OBLIGATORIO) --}}
                                    @if ($item->status === 'devuelto')
                                        <div class="modal fade" id="modalRevertReturned{{ $item->id }}" tabindex="-1" aria-labelledby="modalRevertReturnedLabel{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('prefectura.items.revert-returned', $item) }}" method="POST" class="modal-content">
                                                    @csrf
                                                    <div class="modal-header bg-warning text-dark">
                                                        <h5 class="modal-title fs-6 fw-bold" id="modalRevertReturnedLabel{{ $item->id }}">
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Revertir Devolución a Estado Activo - Folio #{{ $item->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning small mb-3">
 Esta acción revertirá el objeto a estado <strong>Activo</strong> (volverá a figurar en el muro). Se requiere especificar una justificación obligatoria para la bitácora de auditoría interna.
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="revert_reason_{{ $item->id }}" class="form-label fw-bold small">
                                                                Motivo obligatorio de reversión <span class="text-danger">*</span>
                                                            </label>
                                                            <textarea 
                                                                name="revert_reason" 
                                                                id="revert_reason_{{ $item->id }}" 
                                                                rows="3" 
                                                                class="form-control" 
                                                                placeholder="Ej: Registro por equivocación de folio, el solicitante no completó la acreditación de pertenencia..." 
                                                                required 
                                                                minlength="5" 
                                                                maxlength="500"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-warning btn-sm">
                                                            Confirmar Reversión
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- MODAL HISTORIAL DE AUDITORÍA INDIVIDUAL DEL OBJETO (RF-56) --}}
                                    <div class="modal fade" id="modalItemHistory{{ $item->id }}" tabindex="-1" aria-labelledby="modalItemHistoryLabel{{ $item->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-tecnm text-white">
                                                    <h5 class="modal-title fs-6 fw-bold" id="modalItemHistoryLabel{{ $item->id }}">
                                                        <i class="bi bi-clock-history me-1"></i> Bitácora de Auditoría - Folio #{{ $item->id }}: {{ $item->name }}
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @if ($item->logs->isEmpty())
                                                        <p class="text-muted text-center py-3">No hay registros de auditoría registrados para este objeto.</p>
                                                    @else
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-striped">
                                                                <thead class="small table-light">
                                                                    <tr>
                                                                        <th>Fecha / Hora</th>
                                                                        <th>Acción</th>
                                                                        <th>Responsable</th>
                                                                        <th>Motivo / Justificación</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="small">
                                                                    @foreach ($item->logs as $log)
                                                                        <tr>
                                                                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                                                            <td>
                                                                                <span class="badge bg-secondary">{{ $log->action_label }}</span>
                                                                            </td>
                                                                            <td>
                                                                                <strong>{{ $log->user->name ?? 'Prefectura' }}</strong>
                                                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $log->user->email ?? '' }}</div>
                                                                            </td>
                                                                            <td>{{ $log->reason ?? 'Sin observaciones' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Mostrando {{ $managedItems->firstItem() ?? 0 }} a {{ $managedItems->lastItem() ?? 0 }} de {{ $managedItems->total() }} registros
                        </small>
                        {{ $managedItems->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- BITÁCORA GLOBAL DE AUDITORÍA INTERNA DE ACCIONES (RF-56)                   --}}
    {{-- ========================================================================= --}}
    <div class="tab-pane fade" id="audit-pane" role="tabpanel" tabindex="0">
        <div class="card card-custom mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-tecnm">
                        <i class="bi bi-shield-lock me-2 text-dark"></i>Registro de Auditoría Interna de Acciones
                    </h5>
                    <small class="text-muted">
                        Trazabilidad obligatoria: tipo de acción, fecha/hora, cuenta responsable y motivos de decisión.
                    </small>
                </div>
                <span class="badge bg-dark px-3 py-2">Últimos 15 movimientos</span>
            </div>

            <div class="card-body">
                @if ($recentAuditLogs->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-journal-x fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Aún no se han ejecutado acciones de Prefectura en el sistema.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha / Hora</th>
                                    <th>Objeto Relacionado</th>
                                    <th>Acción Ejecutada</th>
                                    <th>Cuenta Responsable</th>
                                    <th>Motivo / Justificación</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentAuditLogs as $log)
                                    <tr>
                                        <td>
                                            <i class="bi bi-clock me-1 text-muted"></i>
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td>
                                            @if ($log->item)
                                                <a href="#" class="fw-bold text-tecnm text-decoration-none">
                                                    Folio #{{ $log->item->id }} - {{ $log->item->name }}
                                                </a>
                                                <span class="badge bg-light text-dark border ms-1">{{ ucfirst($log->item->type) }}</span>
                                            @else
                                                <span class="text-muted">Objeto eliminado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary text-wrap">{{ $log->action_label }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $log->user->name ?? 'Prefectura' }}</div>
                                            <div class="text-muted">{{ $log->user->email ?? 'N/D' }}</div>
                                        </td>
                                        <td>
                                            {{ $log->reason ?? 'Acción efectuada conforme al protocolo estándar.' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-muted border">{{ $log->ip_address ?? 'Local' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

