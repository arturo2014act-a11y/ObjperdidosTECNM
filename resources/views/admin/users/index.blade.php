@extends('layouts.app')

@section('title', 'Administración de Cuentas Institucionales - TecNM')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h1 class="h3 fw-bold text-tecnm mb-1">
                <i class="bi bi-people-fill me-2 text-info"></i>Gestión de Cuentas Institucionales
            </h1>
            <p class="text-muted mb-0 small">
                Consulta de usuarios, control de acceso y activación/desactivación justificada con auditoría.
            </p>
        </div>
        <span class="badge bg-danger px-3 py-2">
            <i class="bi bi-shield-lock me-1"></i> Módulo Exclusivo de Administrador
        </span>
    </div>
</div>

{{-- Filtros y Buscador de Cuentas (RF-27) --}}
<div class="card card-custom bg-white border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Buscar por nombre o correo institucional..." 
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">-- Todos los roles --</option>
                    <option value="usuario_general" {{ request('role') === 'usuario_general' ? 'selected' : '' }}>Usuario General</option>
                    <option value="prefectura" {{ request('role') === 'prefectura' ? 'selected' : '' }}>Prefectura</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Todos los estados --</option>
                    <option value="activa" {{ request('status') === 'activa' ? 'selected' : '' }}>Cuentas Activas</option>
                    <option value="desactivada" {{ request('status') === 'desactivada' ? 'selected' : '' }}>Desactivadas</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-tecnm flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Filtrar
                </button>
                @if (request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tabla de Cuentas --}}
<div class="card card-custom bg-white border-0 shadow-sm">
    <div class="card-body p-0">
        @if ($users->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-person-x display-4 text-muted"></i>
                <p class="text-muted mt-2">No se encontraron cuentas con los criterios especificados.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nombre Completo</th>
                            <th>Correo Institucional</th>
                            <th>Rol Asignado</th>
                            <th>Estado de Acceso</th>
                            <th>Historial Movimientos</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="ps-4 text-muted small">#{{ $user->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $user->name }}</div>
                                    @if (!$user->is_active && $user->deactivation_reason)
                                        <div class="small text-danger fst-italic">
                                            <strong>Motivo:</strong> {{ $user->deactivation_reason }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $user->email }}</code>
                                </td>
                                <td>
                                    @switch($user->role)
                                        @case('admin')
                                            <span class="badge bg-danger"><i class="bi bi-gear-fill me-1"></i>Administrador</span>
                                            @break
                                        @case('prefectura')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-shield-check me-1"></i>Prefectura</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary"><i class="bi bi-person me-1"></i>Usuario General</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Activa
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-slash-circle me-1"></i>Desactivada
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-info border border-info-subtle px-2 py-1 small" title="Eventos registrados en bitácora">
                                        <i class="bi bi-clock-history me-1"></i>{{ $user->userLogs->count() }} eventos
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        {{-- Ver Detalle e Historial (RF-90) --}}
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary btn-sm" title="Consultar detalle individual">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- Desactivar cuenta (RF-87) --}}
                                        @if ($user->is_active)
                                            @if (Auth::id() !== $user->id)
                                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalDeactivateUser{{ $user->id }}" title="Desactivar cuenta con motivo justificado">
                                                    <i class="bi bi-person-slash"></i> Desactivar
                                                </button>
                                            @endif
                                        @else
                                            {{-- Reactivar cuenta (RF-88) --}}
                                            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalReactivateUser{{ $user->id }}" title="Reactivar cuenta con motivo justificado">
                                                <i class="bi bi-person-check"></i> Reactivar
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- MODAL DESACTIVAR CUENTA CON MOTIVO OBLIGATORIO (RF-87, RF-91) --}}
                            @if ($user->is_active && Auth::id() !== $user->id)
                                <div class="modal fade" id="modalDeactivateUser{{ $user->id }}" tabindex="-1" aria-labelledby="modalDeactivateUserLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="modal-content">
                                            @csrf
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title fs-6 fw-bold" id="modalDeactivateUserLabel{{ $user->id }}">
                                                    <i class="bi bi-person-slash me-1"></i> Desactivar Cuenta - {{ $user->name }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <p class="small text-muted mb-2">
 La cuenta no podrá iniciar sesión en la plataforma. Sus publicaciones activas permanecerán vigentes.
                                                </p>
                                                <div class="mb-3">
                                                    <label for="reason_deact_{{ $user->id }}" class="form-label small fw-bold">
                                                        Motivo justificado obligatorio de desactivación <span class="text-danger">*</span>
                                                    </label>
                                                    <textarea 
                                                        name="reason" 
                                                        id="reason_deact_{{ $user->id }}" 
                                                        rows="3" 
                                                        class="form-control form-control-sm" 
                                                        placeholder="Ej: Baja temporal institucional, solicitud de prefectura por mal uso..." 
                                                        maxlength="255" 
                                                        minlength="5" 
                                                        required></textarea>
                                                    <div class="form-text small">Máximo 255 caracteres. No se aceptan motivos vacíos.</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Confirmar Desactivación
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            {{-- MODAL REACTIVAR CUENTA CON MOTIVO OBLIGATORIO (RF-88, RF-91) --}}
                            @if (!$user->is_active)
                                <div class="modal fade" id="modalReactivateUser{{ $user->id }}" tabindex="-1" aria-labelledby="modalReactivateUserLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="modal-content">
                                            @csrf
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title fs-6 fw-bold" id="modalReactivateUserLabel{{ $user->id }}">
                                                    <i class="bi bi-person-check me-1"></i> Reactivar Cuenta - {{ $user->name }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <p class="small text-muted mb-2">
 Se restaurará el acceso de la cuenta al sistema con sus credenciales institucionales existentes.
                                                </p>
                                                <div class="mb-3">
                                                    <label for="reason_react_{{ $user->id }}" class="form-label small fw-bold">
                                                        Motivo justificado obligatorio de reactivación <span class="text-danger">*</span>
                                                    </label>
                                                    <textarea 
                                                        name="reason" 
                                                        id="reason_react_{{ $user->id }}" 
                                                        rows="3" 
                                                        class="form-control form-control-sm" 
                                                        placeholder="Ej: Aclaración presencial en administración, término de suspensión académica..." 
                                                        maxlength="255" 
                                                        minlength="5" 
                                                        required></textarea>
                                                    <div class="form-text small">Máximo 255 caracteres.</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    Confirmar Reactivación
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

            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <small class="text-muted">Mostrando {{ $users->firstItem() ?? 0 }} a {{ $users->lastItem() ?? 0 }} de {{ $users->total() }} cuentas</small>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

