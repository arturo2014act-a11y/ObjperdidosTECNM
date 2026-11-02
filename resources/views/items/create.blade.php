@extends('layouts.app')

@section('title', 'Reportar Objeto Perdido o Encontrado - TecNM Mérida')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card card-custom bg-white border-0 shadow-sm p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                <div>
                    <h2 class="h4 fw-bold text-tecnm mb-1">
                        <i class="bi bi-pencil-square me-2 text-warning"></i>Nuevo Reporte de Objeto
                    </h2>
                    <p class="text-muted small mb-0">
                        Completa la información para difundir un objeto extraviado o registrar un hallazgo en el campus.
                    </p>
                </div>
                <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg"></i> Cancelar
                </a>
            </div>

            <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" id="reportItemForm">
                @csrf

                {{-- Tipo de Publicación (RF-18, RF-19, RF-20) --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary">
                        1. Tipo de Publicación <span class="text-danger">*</span>
                    </label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="type" id="type_perdido" value="perdido" {{ old('type', 'perdido') === 'perdido' ? 'checked' : '' }} required onchange="toggleTypeRules()">
                            <label class="btn btn-outline-danger w-100 py-3 text-start d-flex align-items-center gap-3 rounded-3" for="type_perdido">
                                <i class="bi bi-search fs-3"></i>
                                <div>
                                    <div class="fw-bold">Perdí un objeto</div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Se publicará de forma inmediata en el muro institucional.</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="type" id="type_encontrado" value="encontrado" {{ old('type') === 'encontrado' ? 'checked' : '' }} required onchange="toggleTypeRules()">
                            <label class="btn btn-outline-success w-100 py-3 text-start d-flex align-items-center gap-3 rounded-3" for="type_encontrado">
                                <i class="bi bi-box-seam fs-3"></i>
                                <div>
                                    <div class="fw-bold">Encontré un objeto</div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Requiere entrega física y aprobación de Prefectura.</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Aviso contextual según el tipo seleccionado --}}
                <div id="foundNotice" class="alert alert-warning small d-none mb-4 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <strong>Protocolo para Objetos Encontrados:</strong>
                    Tu reporte quedará <em>Pendiente e invisible en el muro</em> hasta que entregues físicamente el objeto en la oficina de Prefectura del Campus Poniente para constatar su custodia.
                </div>

                {{-- Nombre del Objeto (RF-38) --}}
                <div class="mb-3">
                    <label for="name" class="form-label small fw-bold">
                        Nombre o Identificador del Objeto <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        placeholder="Ej: Mochila negra, Credencial escolar, Calculadora científica..." 
                        value="{{ old('name') }}" 
                        maxlength="100" 
                        required>
                    <div class="d-flex justify-content-between form-text small">
                        <span>Máximo 100 caracteres.</span>
                        <span id="nameCount" class="text-muted">0/100</span>
                    </div>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Descripción (RF-38) --}}
                <div class="mb-3">
                    <label for="description" class="form-label small fw-bold">
                        Descripción Detallada <span class="text-danger">*</span>
                    </label>
                    <textarea 
                        name="description" 
                        id="description" 
                        rows="3" 
                        class="form-control @error('description') is-invalid @enderror" 
                        placeholder="Describe características distintivas: marca, modelo, color, marcas particulares, calcomanías, contenido..." 
                        maxlength="500" 
                        required>{{ old('description') }}</textarea>
                    <div class="d-flex justify-content-between form-text small">
                        <span>Máximo 500 caracteres.</span>
                        <span id="descCount" class="text-muted">0/500</span>
                    </div>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Categoría y Especificación (RF-30, RF-31) --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="category" class="form-label small fw-bold">
                            Categoría del Objeto <span class="text-danger">*</span>
                        </label>
                        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required onchange="toggleCategorySpec()">
                            <option value="">-- Selecciona una categoría --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6" id="specContainer" style="display: {{ old('category') === 'Otros' ? 'block' : 'none' }};">
                        <label for="category_specification" class="form-label small fw-bold">
                            Especifica la Categoría <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="category_specification" 
                            id="category_specification" 
                            class="form-control @error('category_specification') is-invalid @enderror" 
                            placeholder="Describe el tipo de objeto..." 
                            value="{{ old('category_specification') }}" 
                            maxlength="100">
                        @error('category_specification')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Área y Espacio Específico Dependiente (RF-32, RF-33, RF-35, RF-36) --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="area" class="form-label small fw-bold">
                            Edificio o Área General (Campus Poniente) <span class="text-danger">*</span>
                        </label>
                        <select name="area" id="area" class="form-select @error('area') is-invalid @enderror" required onchange="handleAreaChange()">
                            <option value="">-- Selecciona el edificio o área --</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area }}" {{ old('area') === $area ? 'selected' : '' }}>{{ $area }}</option>
                            @endforeach
                        </select>
                        <div class="form-text small">Catálogo obligatorio sin entrada libre.</div>
                        @error('area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Segundo catálogo dependiente y opcional de espacios (RF-32) --}}
                    <div class="col-md-6" id="spaceCatalogWrapper">
                        <label for="academic_class" class="form-label small fw-bold" id="spaceLabel">
                            Espacio Específico (Aula / Lab) (Opcional)
                        </label>
                        
                        {{-- Selector dependiente para edificios con espacios registrados (H y G) --}}
                        <select name="academic_class_select" id="academic_class_select" class="form-select mb-2" onchange="syncSpecificSpace()">
                            <option value="">-- Sin especificar --</option>
                        </select>

                        {{-- Input real enviado en el formulario --}}
                        <input 
                            type="hidden" 
                            name="academic_class" 
                            id="academic_class" 
                            value="{{ old('academic_class') }}">

                        <div id="spaceHelpText" class="form-text small text-muted">
                            Selecciona el aula o laboratorio si aplica.
                        </div>
                    </div>
                </div>

                {{-- Fecha del Suceso y Fotografía (RF-18, RF-34, RNF-02) --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="event_date" class="form-label small fw-bold">
                            Fecha del Suceso <span id="eventDateReqMark" class="text-danger d-none">*</span>
                        </label>
                        <input 
                            type="date" 
                            name="event_date" 
                            id="event_date" 
                            class="form-control @error('event_date') is-invalid @enderror" 
                            value="{{ old('event_date', date('Y-m-d')) }}" 
                            max="{{ date('Y-m-d') }}">
                        <div class="form-text small" id="eventDateHelp">
                            Obligatoria para Encontrados, opcional para Perdidos.
                        </div>
                        @error('event_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="image" class="form-label small fw-bold">
                            Fotografía del Objeto <span id="imageReqMark" class="text-danger d-none">*</span>
                        </label>
                        <input 
                            type="file" 
                            name="image" 
                            id="image" 
                            class="form-control @error('image') is-invalid @enderror" 
                            accept=".jpg,.jpeg,.png,.webp">
                        <div class="form-text small" id="imageHelp">
                            JPG, PNG o WEBP. Máx 5 MB.
                        </div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Botón de Envío con Protección de Doble Envío (RF-58) --}}
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" id="btnSubmitReport" class="btn btn-tecnm px-4 fw-bold">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Publicar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let formIsDirty = false;

    // Detectar cambios en formulario para advertencia de abandono
    const reportForm = document.getElementById('reportItemForm');
    reportForm.querySelectorAll('input, textarea, select').forEach(element => {
        element.addEventListener('change', () => { formIsDirty = true; });
        element.addEventListener('input', () => { formIsDirty = true; });
    });

    window.addEventListener('beforeunload', function (e) {
        if (formIsDirty) {
            e.preventDefault();
            e.returnValue = 'Tienes cambios no guardados en el reporte. ¿Seguro que deseas salir?';
        }
    });

    // Contador de caracteres
    const nameInput = document.getElementById('name');
    const nameCount = document.getElementById('nameCount');
    nameInput.addEventListener('input', () => {
        nameCount.textContent = `${nameInput.value.length}/100`;
    });

    const descInput = document.getElementById('description');
    const descCount = document.getElementById('descCount');
    descInput.addEventListener('input', () => {
        descCount.textContent = `${descInput.value.length}/500`;
    });

    // Catálogo dependiente de espacios específicos
    const spacesCatalog = @json($spaces ?? []);
    const selectedAcademicClass = @json(old('academic_class', ''));

    function handleAreaChange() {
        const areaSelect = document.getElementById('area');
        const area = areaSelect.value;
        const select = document.getElementById('academic_class_select');
        const hiddenInput = document.getElementById('academic_class');
        const helpText = document.getElementById('spaceHelpText');

        select.innerHTML = '<option value="">-- Sin especificar --</option>';

        if (spacesCatalog[area] && spacesCatalog[area].length > 0) {
            select.disabled = false;
            select.classList.remove('bg-light');
            spacesCatalog[area].forEach(space => {
                const opt = document.createElement('option');
                opt.value = space;
                opt.textContent = `${area} - ${space}`;
                if (space === selectedAcademicClass || hiddenInput.value === space) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });
            helpText.textContent = `Espacios identificados para ${area} (opcional).`;
        } else if (area === 'Edificio B' || area === 'Edificio C') {
            select.disabled = true;
            select.classList.add('bg-light');
            select.innerHTML = '<option value="">-- Espacios pendientes de levantar --</option>';
            helpText.textContent = `Aulas y laboratorios de ${area} pendientes de levantar. Se registra con espacio sin especificar.`;
            hiddenInput.value = '';
        } else if (area === 'Otra área del campus') {
            select.disabled = true;
            select.classList.add('bg-light');
            select.innerHTML = '<option value="">-- No requiere espacio específico --</option>';
            helpText.textContent = 'No se solicita texto adicional ni espacio específico.';
            hiddenInput.value = '';
        } else {
            select.disabled = true;
            select.classList.add('bg-light');
            select.innerHTML = '<option value="">-- Sin especificar --</option>';
            helpText.textContent = 'Espacio específico sin registrar. Se guarda como sin especificar.';
            hiddenInput.value = '';
        }
        syncSpecificSpace();
    }

    function syncSpecificSpace() {
        const select = document.getElementById('academic_class_select');
        const hiddenInput = document.getElementById('academic_class');
        if (!select.disabled) {
            hiddenInput.value = select.value;
        }
    }

    // Inicializar catálogo dependiente al cargar
    handleAreaChange();

    // Alternar especificación de otros
    function toggleCategorySpec() {
        const cat = document.getElementById('category').value;
        const spec = document.getElementById('specContainer');
        const specInput = document.getElementById('category_specification');
        if (cat === 'Otros') {
            spec.style.display = 'block';
            specInput.setAttribute('required', 'required');
        } else {
            spec.style.display = 'none';
            specInput.removeAttribute('required');
        }
    }

    // Reglas dinámicas por Tipo
    function toggleTypeRules() {
        const isFound = document.getElementById('type_encontrado').checked;
        const notice = document.getElementById('foundNotice');
        const eventDateReq = document.getElementById('eventDateReqMark');
        const imageReq = document.getElementById('imageReqMark');
        const eventDateInput = document.getElementById('event_date');
        const imageInput = document.getElementById('image');

        if (isFound) {
            notice.classList.remove('d-none');
            eventDateReq.classList.remove('d-none');
            imageReq.classList.remove('d-none');
            eventDateInput.setAttribute('required', 'required');
            imageInput.setAttribute('required', 'required');
        } else {
            notice.classList.add('d-none');
            eventDateReq.classList.add('d-none');
            imageReq.classList.add('d-none');
            eventDateInput.removeAttribute('required');
            imageInput.removeAttribute('required');
        }
    }

    // Inicializar reglas según selección inicial
    toggleTypeRules();

    // Protección de doble envío
    reportForm.addEventListener('submit', function () {
        formIsDirty = false; // Evita el aviso de beforeunload al enviar
        const btn = document.getElementById('btnSubmitReport');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Procesando reporte...';
    });
</script>
@endpush
@endsection

