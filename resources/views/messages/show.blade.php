@extends('layouts.app')

@section('title', 'Chat Privado - ' . $conversation->item->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        {{-- Barra superior de la conversación --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver a Mis Mensajes
            </a>
            <a href="{{ route('items.show', $conversation->item) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i> Ver Publicación Original
            </a>
        </div>

        {{-- Tarjeta informativa del objeto vinculado --}}
        <div class="card card-custom bg-white border-0 shadow-sm p-3 mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 overflow-hidden bg-light border d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                        @if ($conversation->item->image_path && Storage::disk('public')->exists($conversation->item->image_path))
                            <img src="{{ asset('storage/' . $conversation->item->image_path) }}" alt="{{ $conversation->item->name }}" class="w-100 h-100 object-fit-cover">
                        @else
                            <i class="bi {{ $conversation->item->type === 'perdido' ? 'bi-search text-danger' : 'bi-box-seam text-success' }} fs-4"></i>
                        @endif
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $conversation->item->type === 'perdido' ? 'badge-subtle-perdido' : 'badge-subtle-encontrado' }} rounded-pill">
                                {{ strtoupper($conversation->item->type) }}
                            </span>
                            <span class="fw-bold text-dark">{{ $conversation->item->name }}</span>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-geo-alt me-1"></i>{{ $conversation->item->area }} · 
                            Conversando con: <strong class="text-dark">{{ $otherUser->name }}</strong>
                        </small>
                    </div>
                </div>

                @php
                    $isClosed = in_array($conversation->item->status, ['cerrado', 'devuelto'], true);
                @endphp
                @if ($isClosed)
                    <span class="badge bg-secondary px-3 py-2 rounded-pill">
                        <i class="bi bi-lock-fill me-1"></i> Publicación Cerrada (Solo Lectura)
                    </span>
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Conversación Activa
                    </span>
                @endif
            </div>
        </div>

        {{-- Contenedor del Chat Privado --}}
        <div class="card card-custom bg-white border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold text-tecnm mb-0">
                        <i class="bi bi-chat-dots-fill me-2 text-warning"></i>Chat Seguro de la Comunidad
                    </h5>
                    <small class="text-muted">
                        Protección de privacidad: no se comparten teléfonos, correos ni datos sensibles.
                    </small>
                </div>
                <div class="spinner-border spinner-border-sm text-secondary d-none" id="chatPollSpinner" role="status">
                    <span class="visually-hidden">Actualizando...</span>
                </div>
            </div>

            <div class="card-body p-3">
                {{-- Aviso Importante Institucional --}}
                <div class="alert alert-light border-warning small p-2 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-warning fs-5"></i>
                    <div>
                        <strong>Recordatorio:</strong> Este chat es para coordinar detalles generales. La entrega física oficial y la acreditación de pertenencias deben realizarse en el <strong>Módulo de Prefectura</strong>.
                    </div>
                </div>

                {{-- Ventana de Mensajes --}}
                <div id="chatMessagesWindow" class="p-3 bg-light rounded-3 mb-3" style="min-height: 280px; max-height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">
                    <div class="text-center text-muted small py-5" id="initialLoadingNotice">
                        <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                        Cargando mensajes...
                    </div>
                </div>

                {{-- Formulario para enviar mensaje (Requisitos 3, 5, 12) --}}
                @if (!$isClosed)
                    <form id="chatSendForm" class="d-flex gap-2">
                        @csrf
                        <input 
                            type="text" 
                            id="chatInputMessage" 
                            class="form-control" 
                            placeholder="Escribe tu mensaje privado..." 
                            maxlength="500" 
                            required 
                            autocomplete="off">
                        <button type="submit" id="chatSubmitBtn" class="btn btn-tecnm px-4 fw-bold flex-shrink-0">
                            <i class="bi bi-send-fill me-1"></i> Enviar
                        </button>
                    </form>
                    <div class="form-text small text-muted mt-1">Máximo 500 caracteres por mensaje.</div>
                @else
                    <div class="alert alert-secondary mb-0 small text-center">
                        <i class="bi bi-lock-fill me-1"></i> Esta publicación ha sido cerrada o devuelta. El historial se conserva únicamente como consulta (Requisito 12).
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatWindow = document.getElementById('chatMessagesWindow');
    const sendForm = document.getElementById('chatSendForm');
    const inputMessage = document.getElementById('chatInputMessage');
    const submitBtn = document.getElementById('chatSubmitBtn');
    const pollSpinner = document.getElementById('chatPollSpinner');
    const pollUrl = "{{ route('messages.poll', $conversation) }}";
    const sendUrl = "{{ route('messages.send', $conversation) }}";
    const csrfToken = "{{ csrf_token() }}";

    let lastMessagesCount = -1;

    function renderMessages(messages) {
        if (!messages || messages.length === 0) {
            chatWindow.innerHTML = `
                <div class="text-center text-muted py-5 small">
                    <i class="bi bi-chat-heart text-secondary fs-2 d-block mb-2"></i>
                    Aún no hay mensajes en esta conversación. Envía el primer mensaje para coordinar la entrega o consulta.
                </div>`;
            return;
        }

        let html = '';
        messages.forEach(msg => {
            const isMe = msg.is_me;
            const alignClass = isMe ? 'align-self-end text-end' : 'align-self-start text-start';
            const bubbleBg = isMe ? 'bg-tecnm text-white' : 'bg-white text-dark border';

            html += `
                <div class="${alignClass}" style="max-width: 80%;">
                    <div class="small text-muted mb-1" style="font-size: 0.75rem;">
                        <strong>${msg.sender_name}</strong> · ${msg.created_at}
                    </div>
                    <div class="p-2 px-3 rounded-3 shadow-sm ${bubbleBg}" style="word-break: break-word; font-size: 0.9rem; text-align: left;">
                        ${msg.message}
                    </div>
                </div>
            `;
        });

        chatWindow.innerHTML = html;
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function fetchMessages() {
        if (pollSpinner) pollSpinner.classList.remove('d-none');

        fetch(pollUrl, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.messages && data.messages.length !== lastMessagesCount) {
                lastMessagesCount = data.messages.length;
                renderMessages(data.messages);
            }
        })
        .catch(err => {
            console.error('Error al actualizar mensajes:', err);
        })
        .finally(() => {
            if (pollSpinner) pollSpinner.classList.add('d-none');
        });
    }

    // Carga inicial y actualización automática periódica cada 4 segundos
    fetchMessages();
    const pollInterval = setInterval(fetchMessages, 4000);

    // Enviar mensaje con AJAX
    if (sendForm) {
        sendForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const text = inputMessage.value.trim();
            if (!text) return;

            submitBtn.disabled = true;

            fetch(sendUrl, {
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
                if (data.status === 'success') {
                    inputMessage.value = '';
                    fetchMessages();
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(err => {
                alert('No se pudo enviar el mensaje. Verifica tu conexión.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                inputMessage.focus();
            });
        });
    }
});
</script>
@endpush
@endsection

