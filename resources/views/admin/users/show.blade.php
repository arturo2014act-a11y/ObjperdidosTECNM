@extends('layouts.app')

@section('title', 'Detalle de Cuenta - ' . $user->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        {{-- Enlace de Regreso --}}
        <div class="mb-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Regresar al Listado de Cuentas
            </a>
        </div>

        {{-- Tarjeta de Perfil de la Cuenta --}}
        <div class="card card-custom bg-white border-0 shadow-sm p-4 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-tecnm text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; font-size: 1.5rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-0">{{ $user->name }}</h2>
                        <div class="text-muted small">
                            <code>{{ $user->email }}</code>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if ($user->is_active)
                        <span class="badge bg-success px-3 py-2 fs-6">
                            <i class="bi bi-check-circle me-1"></i> Cuenta Activa
                        </span>
                        @if (Auth::id() !== $user->id)
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalDeactivateUser">
                                <i class="bi bi-person-slash me-1"></i> Desactivar
                            </button>
                        @endif
                    @else
                        <span class="badge bg-danger px-3 py-2 fs-6">
                            <i class="bi bi-slash-circle me-1"></i> Cuenta Desactivada
                        </span>
                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalReactivateUser">
                            <i class="bi bi-person-check me-1"></i> Reactivar
                        </button>
                    @endif
                </div>
            </div>

            <div class="row g-3 py-3">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <span class="text-muted small d-block">Rol en la Plataforma:</span>
                        <strong class="text-dark text-capitalize">{{ str_replace('_', ' ', $user->role) }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <span class="text-muted small d-block">Fecha de Registro:</span>
                        <strong class="text-dark">{{ $user->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <span class="text-muted small d-block">Publicaciones Registradas:</span>
                        <strong class="text-dark">{{ $user->items->count() }} objetos</strong>
                    </div>
                </div>

                @if (!$user->is_active && $user->deactivation_reason)
                    <div class="col-12">
                        <div class="alert alert-danger mb-0 small">
                            <i class="bi bi-exclamation-octagon-fill me-2"></i>
                            <strong>Motivo registrado de desactivación (RF-87):</strong> {{ $user->deactivation_reason }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Historial de Movimientos de la Cuenta (RF-89, RF-90) --}}
        <div class="card card-custom bg-white border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold text-tecnm mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>Historial de Movimientos de la Cuenta (RF-89, RF-90)
                    </h5>
                    <small class="text-muted">
                        Trazabilidad administrativa interna: solo visible en el detalle de la cuenta; no existe auditoría global (RF-90).
                    </small>
                </div>
                <span class="badge bg-secondary">{{ $user->userLogs->count() }} movimientos</span>
            </div>

            @if ($user->userLogs->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-journal-check fs-2 opacity-50"></i>
                    <p class="small mt-2 mb-0">No se registran activaciones ni desactivaciones administrativas para esta cuenta.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha / Hora</th>
                                <th>Tipo de Acción (RF-89)</th>
                                <th>Administrador Responsable</th>
                                <th>Motivo Justificado (RF-91)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($user->userLogs as $log)
                                <tr>
                                    <td>
                                        <i class="bi bi-calendar3 me-1 text-muted"></i>
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td>
                                        @if ($log->action === 'desactivacion')
                                            <span class="badge bg-danger">Desactivación</span>
                                        @else
                                            <span class="badge bg-success">Reactivación</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $log->admin->name ?? 'Administrador General' }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $log->admin->email ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span class="text-dark">{{ $log->reason }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Publicaciones Registradas por este Usuario (RF-29) --}}
        <div class="card card-custom bg-white border-0 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h5 class="fw-bold text-tecnm mb-0">
                    <i class="bi bi-box2 me-2 text-warning"></i>Publicaciones de esta Cuenta (RF-29)
                </h5>
                <small class="text-muted">Las publicaciones permanecen vigentes aunque la cuenta esté desactivada</small>
            </div>

            @if ($user->items->isEmpty())
                <p class="text-muted small mb-0">Este usuario no ha registrado publicaciones aún.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Folio</th>
                                <th>Tipo</th>
                                <th>Nombre del Objeto</th>
                                <th>Área</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($user->items as $item)
                                <tr>
                                    <td>#{{ $item->id }}</td>
                                    <td>
                                        <span class="badge {{ $item->type === 'perdido' ? 'bg-danger' : 'bg-success' }} text-uppercase">
                                            {{ $item->type }}
                                        </span>
                                    </td>
                                    <td><strong>{{ $item->name }}</strong></td>
                                    <td>{{ $item->area }}</td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($item->status) }}</span></td>
                                    <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('items.show', $item) }}" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Ver objeto">
                                            <i class="bi bi-eye"></i>
                                        </a>
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

{{-- MODAL DESACTIVACIÓN --}}
@if ($user->is_active && Auth::id() !== $user->id)
    <div class="modal fade" id="modalDeactivateUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fs-6 fw-bold">Desactivar Cuenta - {{ $user->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Ingresa el motivo justificado de desactivación (RF-87, RF-91):</p>
                    <textarea name="reason" rows="3" class="form-control" placeholder="Motivo obligatorio..." maxlength="255" minlength="5" required></textarea>
                    <div class="form-text small">Máximo 255 caracteres.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm">Confirmar Desactivación</button>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- MODAL REACTIVACIÓN --}}
@if (!$user->is_active)
    <div class="modal fade" id="modalReactivateUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fs-6 fw-bold">Reactivar Cuenta - {{ $user->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Ingresa el motivo justificado de reactivación (RF-88, RF-91):</p>
                    <textarea name="reason" rows="3" class="form-control" placeholder="Motivo obligatorio..." maxlength="255" minlength="5" required></textarea>
                    <div class="form-text small">Máximo 255 caracteres.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm">Confirmar Reactivación</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

