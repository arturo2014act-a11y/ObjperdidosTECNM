@extends('layouts.app')

@section('title', 'Mis Mensajes - EncuentraTEC')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h1 class="h3 fw-bold text-tecnm mb-1">
                <i class="bi bi-chat-left-dots-fill me-2 text-warning"></i>Mis Conversaciones Privadas
            </h1>
            <p class="text-muted mb-0 small">
                Conversaciones directas con otros miembros del campus sobre objetos publicados.
            </p>
        </div>
        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver al Muro
        </a>
    </div>
</div>

<div class="card card-custom bg-white border-0 shadow-sm">
    <div class="card-body p-0">
        @if ($conversations->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-chat-square-dots display-4 text-muted opacity-50"></i>
                <h5 class="fw-bold text-muted mt-3">No tienes conversaciones activas</h5>
                <p class="text-muted small mb-4">
                    Cuando contactes al autor de un objeto o te contacten a ti sobre tus reportes, tus chats aparecerán aquí.
                </p>
                <a href="{{ route('items.index') }}" class="btn btn-tecnm btn-sm">
                    <i class="bi bi-search me-1"></i> Explorar Muro de Objetos
                </a>
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach ($conversations as $conv)
                    @php
                        $other = $conv->otherParticipant(Auth::user());
                        $lastMessage = $conv->messages->first();
                        $isClosed = in_array($conv->item->status, ['cerrado', 'devuelto'], true);
                    @endphp
                    <a href="{{ route('messages.show', $conv) }}" class="list-group-item list-group-item-action p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            {{-- Miniatura o ícono del objeto --}}
                            <div class="rounded-3 overflow-hidden bg-light border d-flex align-items-center justify-content-center flex-shrink-0" style="width: 58px; height: 58px;">
                                @if ($conv->item->image_path && Storage::disk('public')->exists($conv->item->image_path))
                                    <img src="{{ asset('storage/' . $conv->item->image_path) }}" alt="{{ $conv->item->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <i class="bi {{ $conv->item->type === 'perdido' ? 'bi-search text-danger' : 'bi-box-seam text-success' }} fs-3"></i>
                                @endif
                            </div>

                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <span class="badge {{ $conv->item->type === 'perdido' ? 'badge-subtle-perdido' : 'badge-subtle-encontrado' }} rounded-pill">
                                        {{ strtoupper($conv->item->type) }}
                                    </span>
                                    <span class="fw-bold text-dark fs-6">{{ $conv->item->name }}</span>
                                    @if ($isClosed)
                                        <span class="badge bg-secondary rounded-pill small">Publicación Cerrada</span>
                                    @endif
                                </div>
                                
                                {{-- Participante --}}
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-person-fill text-secondary me-1"></i>Conversación con: <strong>{{ $other->name }}</strong>
                                </div>

                                {{-- Último mensaje --}}
                                <div class="text-truncate small text-dark opacity-75" style="max-width: 480px;">
                                    @if ($lastMessage)
                                        <span class="fw-semibold">{{ $lastMessage->sender_id === Auth::id() ? 'Tú: ' : '' }}</span>{{ $lastMessage->message }}
                                    @else
                                        <em>Conversación iniciada. Aún sin mensajes.</em>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="text-md-end text-muted small flex-shrink-0">
                            <div><i class="bi bi-clock me-1"></i>{{ $conv->updated_at->diffForHumans() }}</div>
                            <span class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-chat-text me-1"></i> Abrir Chat
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="p-3 border-top">
                {{ $conversations->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

