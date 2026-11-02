@extends('layouts.app')

@section('title', 'Muro de Objetos Perdidos y Encontrados - TecNM Mérida Poniente')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-tecnm mb-1">
                <i class="bi bi-grid-fill me-2 text-warning"></i>Muro Cronológico de Objetos
            </h1>
            <p class="text-muted mb-0 small">
                Espacio institucional de consulta y difusión de objetos perdidos y encontrados en el TecNM Campus Poniente.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('items.create') }}" class="btn btn-warning fw-bold shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Publicar Reporte</span>
            </a>
        </div>
    </div>
</div>

{{-- Barra de Búsqueda, Filtro y Ordenación (RF-23, RF-39, RF-40) --}}
<div class="card card-custom mb-4 border-0 shadow-sm bg-white">
    <div class="card-body p-3">
        <form action="{{ route('items.index') }}" method="GET" class="row g-2 align-items-center">
            {{-- Búsqueda solo en nombre y descripción (RF-39) --}}
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control border-start-0 bg-light" 
                        placeholder="Buscar por nombre o descripción del objeto..." 
                        value="{{ request('search') }}">
                </div>
            </div>

            {{-- Filtro por Tipo: Todos, Perdido, Encontrado (RF-23) --}}
            <div class="col-6 col-md-3">
                <select name="type" class="form-select bg-light">
                    <option value="">-- Todos los reportes --</option>
                    <option value="perdido" {{ request('type') === 'perdido' ? 'selected' : '' }}>Objetos Perdidos</option>
                    <option value="encontrado" {{ request('type') === 'encontrado' ? 'selected' : '' }}>Objetos Encontrados</option>
                </select>
            </div>

            {{-- Orden cronológico: Más recientes / Más antiguas (RF-40) --}}
            <div class="col-6 col-md-2">
                <select name="order" class="form-select bg-light">
                    <option value="recientes" {{ request('order', 'recientes') === 'recientes' ? 'selected' : '' }}>Más recientes</option>
                    <option value="antiguas" {{ request('order') === 'antiguas' ? 'selected' : '' }}>Más antiguas</option>
                </select>
            </div>

            {{-- Botones de Filtrar y Limpiar --}}
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-tecnm flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Filtrar
                </button>
                @if (request()->hasAny(['search', 'type', 'order']))
                    <a href="{{ route('items.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Listado de Tarjetas del Muro (RF-13, RF-42, RF-43) --}}
@if ($items->isEmpty())
    <div class="card card-custom p-5 text-center bg-white shadow-sm">
        <div class="py-4">
            <i class="bi bi-search fs-1 text-muted"></i>
            <h5 class="fw-bold text-dark mt-3">No se encontraron publicaciones activas</h5>
            <p class="text-muted small mb-4">
                No hay objetos que coincidan con los criterios de búsqueda o no se han publicado reportes recientemente.
            </p>
            <a href="{{ route('items.create') }}" class="btn btn-tecnm btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Reportar un Objeto Ahora
            </a>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach ($items as $item)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card card-custom h-100 bg-white border-0 shadow-sm d-flex flex-column">
                    {{-- Imagen de la publicación (RF-13, RF-42) --}}
                    <div class="position-relative bg-light rounded-top text-center overflow-hidden" style="height: 200px;">
                        @if ($item->image_path && Storage::disk('public')->exists($item->image_path))
                            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="w-100 h-100 object-fit-cover">
                        @else
                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                <i class="bi {{ $item->type === 'perdido' ? 'bi-question-circle' : 'bi-box-seam' }} fs-1 opacity-50"></i>
                                <span class="small mt-1 text-secondary">Sin imagen disponible</span>
                            </div>
                        @endif

                        {{-- Badge de Tipo (Perdido / Encontrado) --}}
                        <div class="position-absolute top-0 start-0 m-3">
                            @if ($item->type === 'perdido')
                                <span class="badge bg-danger shadow-sm px-3 py-2 text-uppercase fw-bold">
                                    <i class="bi bi-search me-1"></i> Perdido
                                </span>
                            @else
                                <span class="badge bg-success shadow-sm px-3 py-2 text-uppercase fw-bold">
                                    <i class="bi bi-check-circle me-1"></i> Encontrado
                                </span>
                            @endif
                        </div>

                        {{-- Folio institucional --}}
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-dark bg-opacity-75 shadow-sm">
                                Folio #{{ $item->id }}
                            </span>
                        </div>
                    </div>

                    {{-- Cuerpo de la Tarjeta --}}
                    <div class="card-body p-4 d-flex flex-column flex-grow-1">
                        <div class="text-muted small mb-1">
                            <i class="bi bi-tag-fill me-1 text-warning"></i>{{ $item->category }}
                            @if ($item->category_specification)
                                <span class="fst-italic">({{ $item->category_specification }})</span>
                            @endif
                        </div>

                        <h5 class="card-title fw-bold text-tecnm mb-2">
                            {{ $item->name }}
                        </h5>

                        {{-- Descripción abreviada (RF-42) --}}
                        <p class="card-text text-muted small flex-grow-1 mb-3">
                            {{ Str::limit($item->description, 140) }}
                        </p>

                        {{-- Ubicación del Campus --}}
                        <div class="small text-secondary mb-2">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            <strong>Área:</strong> {{ $item->area }}
                            @if ($item->academic_class)
                                <span class="text-muted">({{ $item->academic_class }})</span>
                            @endif
                        </div>

                        {{-- Fecha de publicación y suceso (RF-13, RF-34) --}}
                        <div class="small text-muted d-flex justify-content-between align-items-center pt-2 border-top">
                            <span>
                                <i class="bi bi-clock me-1"></i> {{ $item->created_at->format('d/m/Y') }}
                            </span>
                            @if ($item->event_date)
                                <span>
                                    <i class="bi bi-calendar-event me-1"></i> Suceso: {{ $item->event_date->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Pie de Tarjeta con botón de Detalle --}}
                    <div class="card-footer bg-light border-0 p-3 pt-0 text-end">
                        <a href="{{ route('items.show', $item) }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                            <i class="bi bi-eye me-1"></i> Ver Detalle Completo
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Paginación de 10 elementos por página (RF-41) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top gap-2">
        <div class="text-muted small">
            Mostrando <strong>{{ $items->firstItem() ?? 0 }}</strong> a <strong>{{ $items->lastItem() ?? 0 }}</strong> de <strong>{{ $items->total() }}</strong> publicaciones
        </div>
        <div>
            {{ $items->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endif
@endsection

