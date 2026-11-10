<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Item;
use App\Models\ItemMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConversationController extends Controller
{
    /**
     * Bandeja "Mis Mensajes" (Requisito 6).
     * Muestra todas las conversaciones donde el usuario es autor o interesado.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Conversaciones donde participa el usuario
        $conversations = Conversation::with(['item.user', 'user', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('item', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->latest('updated_at')
            ->paginate(12);

        return view('messages.index', compact('conversations'));
    }

    /**
     * Iniciar o abrir conversación desde una publicación (Requisitos 1, 2, 9, 10, 11).
     */
    public function start(Item $item): RedirectResponse
    {
        $user = Auth::user();

        // Requisito 10: usuario autenticado y activo
        if (!$user || !$user->is_active) {
            return redirect()->route('login')->with('error', 'Tu cuenta debe estar activa para enviar mensajes.');
        }

        // Requisito 9: No se permite iniciar conversaciones consigo mismo
        if ($item->user_id === $user->id) {
            return redirect()->route('items.show', $item)
                ->with('error', 'No puedes iniciar una conversación sobre tu propia publicación.');
        }

        // Requisito 10 y 11: Solo sobre publicaciones visibles (publicado o cierre_pendiente que aún es visible)
        if (!in_array($item->status, ['publicado', 'cierre_pendiente'], true)) {
            return redirect()->route('items.index')
                ->with('error', 'Esta publicación no está disponible para recibir mensajes.');
        }

        // Buscar conversación existente o crearla
        $conversation = Conversation::firstOrCreate([
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);

        return redirect()->route('messages.show', $conversation);
    }

    /**
     * Ver una conversación específica (Requisitos 7, 8).
     */
    public function show(Conversation $conversation): View
    {
        $user = Auth::user();

        // Requisito 7: Únicamente los participantes pueden visualizar y responder
        $isInitiator = $conversation->user_id === $user->id;
        $isAuthor = $conversation->item && $conversation->item->user_id === $user->id;

        if (!$isInitiator && !$isAuthor) {
            abort(404);
        }

        $conversation->load(['item.user', 'user', 'messages.sender']);

        $otherUser = $conversation->otherParticipant($user);

        return view('messages.show', compact('conversation', 'otherUser'));
    }

    /**
     * Endpoint API para obtener los mensajes de una conversación (AJAX / Sondeo).
     */
    public function getMessages(Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        $isInitiator = $conversation->user_id === $user->id;
        $isAuthor = $conversation->item && $conversation->item->user_id === $user->id;

        if (!$isInitiator && !$isAuthor) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $messages = $conversation->messages()->with('sender')->get()->map(function ($msg) use ($user) {
            return [
                'id' => $msg->id,
                'message' => e($msg->message),
                'is_me' => $msg->sender_id === $user->id,
                'sender_name' => $msg->sender->name, // Requisito 8: Solo nombre, sin correo ni teléfono
                'created_at' => $msg->created_at->format('d/m/Y h:i A'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'is_closed' => in_array($conversation->item->status, ['cerrado', 'devuelto'], true),
            'messages' => $messages,
        ]);
    }

    /**
     * Enviar mensaje dentro de la conversación (Requisitos 3, 5, 7, 12).
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (!$user || !$user->is_active) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Cuenta inactiva o no autenticada.'], 403);
            }
            return back()->with('error', 'Cuenta inactiva.');
        }

        // Requisito 7: Solo los participantes pueden responder
        $isInitiator = $conversation->user_id === $user->id;
        $isAuthor = $conversation->item && $conversation->item->user_id === $user->id;

        if (!$isInitiator && !$isAuthor) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'No eres participante de esta conversación.'], 403);
            }
            abort(404);
        }

        // Requisito 12: Al cerrar una publicación, se conservará el historial, pero se impedirán nuevos mensajes
        if (in_array($conversation->item->status, ['cerrado', 'devuelto'], true)) {
            $msg = 'La publicación ha sido cerrada o devuelta; no se permiten nuevos mensajes.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ], [
            'message.required' => 'Debes escribir un mensaje.',
            'message.max' => 'El mensaje no debe superar los 500 caracteres.',
        ]);

        // Determinar el destinatario
        $receiverId = $isAuthor ? $conversation->user_id : $conversation->item->user_id;

        $message = ItemMessage::create([
            'conversation_id' => $conversation->id,
            'item_id' => $conversation->item_id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => trim($validated['message']),
            'is_prefectura' => false,
        ]);

        // Actualizar timestamp de la conversación para ordenarla arriba en la bandeja
        $conversation->touch();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => [
                    'id' => $message->id,
                    'message' => e($message->message),
                    'is_me' => true,
                    'sender_name' => $user->name,
                    'created_at' => $message->created_at->format('d/m/Y h:i A'),
                ],
            ], 201);
        }

        return back()->with('success', 'Mensaje enviado correctamente.');
    }
}

