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
                            <div class="small mt-2 px-3">Estamos buscando la imagen por favor espere...</div>
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
                                <i class="bi bi-shield-lock-fill me-1"></i>Datos del Autor (Visible solo para Prefectura y Autor)
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

        {{-- Módulo de Comunicación y Orientación Institucional --}}
        @if ($isStaff || $isAuthor)
            <div class="card card-custom bg-white border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0 text-tecnm">
                            <i class="bi bi-chat-dots text-warning me-2"></i>Canal de Comunicación y Orientación
                        </h5>
                        <p class="text-muted small mb-0">
                            Comunícate directamente con la oficina de Prefectura respecto a este reporte.
                        </p>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Enlace Activo
                    </span>
                </div>
                <div class="card-body p-3">
                    {{-- Caja de mensajes --}}
                    <div id="chatMessagesBox" class="p-3 bg-light rounded-3 mb-3" style="max-height: 320px; min-height: 180px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
                        <div class="text-center text-muted small py-4" id="loadingChatNotice">
                            <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                            Cargando mensajes del canal...
                        </div>
                    </div>

                    {{-- Formulario para enviar mensaje --}}
                    <form id="chatMessageForm" class="d-flex gap-2">
                        @csrf
                        <input type="text" id="chatMessageInput" class="form-control" placeholder="Escribe un mensaje de consulta o indicación sobre el bien..." maxlength="500" required>
                        <button type="submit" class="btn btn-primary px-4 fw-bold" id="chatSendBtn">
                            <i class="bi bi-send-fill me-1"></i> Enviar
                        </button>
                    </form>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const messagesBox = document.getElementById('chatMessagesBox');
                    const form = document.getElementById('chatMessageForm');
                    const input = document.getElementById('chatMessageInput');
                    const sendBtn = document.getElementById('chatSendBtn');
                    const fetchUrl = "{{ route('items.messages.index', $item) }}";
                    const postUrl = "{{ route('items.messages.store', $item) }}";
                    const csrfToken = "{{ csrf_token() }}";

                    function renderMessages(messages) {
                        if (!messages || messages.length === 0) {
                            messagesBox.innerHTML = `
                                <div class="text-center text-muted py-4 small">
                                    <i class="bi bi-chat-heart text-secondary fs-4 d-block mb-1"></i>
                                    Aún no hay mensajes en este canal. Puedes iniciar la consulta o dejar instrucciones.
                                </div>`;
                            return;
                        }

                        let html = '';
                        messages.forEach(msg => {
                            const isMe = msg.is_me;
                            const alignClass = isMe ? 'align-self-end text-end' : 'align-self-start text-start';
                            const badgeBg = msg.is_prefectura ? 'bg-warning text-dark' : (isMe ? 'bg-primary text-white' : 'bg-white text-dark border');

                            html += `
                                <div class="${alignClass}" style="max-width: 80%;">
                                    <div class="small fw-bold text-muted mb-1" style="font-size: 0.75rem;">
                                        ${msg.sender_name} <span class="fw-normal">· ${msg.created_at}</span>
                                    </div>
                                    <div class="p-2 px-3 rounded-3 shadow-sm ${badgeBg}" style="word-break: break-word; font-size: 0.9rem; text-align: left;">
                                        ${msg.message}
                                    </div>
                                </div>
                            `;
                        });

                        messagesBox.innerHTML = html;
                        messagesBox.scrollTop = messagesBox.scrollHeight;
                    }

                    function loadMessages() {
                        fetch(fetchUrl, {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.messages) {
                                renderMessages(data.messages);
                            }
                        })
                        .catch(err => {
                            messagesBox.innerHTML = '<div class="text-danger small text-center">Error al cargar mensajes.</div>';
                        });
                    }

                    // Carga inicial y sondeo periódico cada 4 segundos
                    loadMessages();
                    setInterval(loadMessages, 4000);

                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const text = input.value.trim();
                        if (!text) return;

                        sendBtn.disabled = true;

                        fetch(postUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ message: text })
                        })
                        .then(res => res.json())
                        .then(data => {
                            input.value = '';
                            loadMessages();
                        })
                        .catch(err => {
                            alert('No se pudo enviar el mensaje.');
                        })
                        .finally(() => {
                            sendBtn.disabled = false;
                            input.focus();
                        });
                    });
                });
            </script>
        @endif
    </div>
</div>
@endsection

