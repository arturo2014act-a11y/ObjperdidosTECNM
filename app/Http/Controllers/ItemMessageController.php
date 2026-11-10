<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemMessageController extends Controller
{
    /**
     * Obtener mensajes de orientación asociados a una pertenencia (API / AJAX).
     */
    public function index(Item $item): JsonResponse
    {
        $user = Auth::user();

        // Solo el autor o personal (Prefectura / Admin) pueden consultar la conversación
        $isAuthor = $user && $user->id === $item->user_id;
        $isStaff = $user && in_array($user->role, ['prefectura', 'admin'], true);

        if (!$isAuthor && !$isStaff) {
            return response()->json(['error' => 'No autorizado para ver estos mensajes.'], 403);
        }

        $messages = $item->messages()->with('sender:id,name,role')->get()->map(function ($msg) use ($user) {
            return [
                'id' => $msg->id,
                'message' => e($msg->message),
                'is_me' => $user && $msg->sender_id === $user->id,
                'sender_name' => $msg->is_prefectura ? 'Prefectura (Oficina)' : $msg->sender->name,
                'is_prefectura' => $msg->is_prefectura,
                'created_at' => $msg->created_at->format('d/m/Y h:i A'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'messages' => $messages,
        ]);
    }

    /**
     * Enviar un mensaje de orientación sobre el objeto reportado.
     */
    public function store(Request $request, Item $item): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $isAuthor = $user && $user->id === $item->user_id;
        $isStaff = $user && in_array($user->role, ['prefectura', 'admin'], true);

        if (!$isAuthor && !$isStaff) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'No autorizado para enviar mensajes en este reporte.'], 403);
            }
            return back()->with('error', 'No autorizado.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ], [
            'message.required' => 'Debes escribir un mensaje.',
            'message.max' => 'El mensaje no debe superar los 500 caracteres.',
        ]);

        $message = ItemMessage::create([
            'item_id' => $item->id,
            'sender_id' => $user->id,
            'message' => trim($validated['message']),
            'is_prefectura' => $user->role === 'prefectura',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => [
                    'id' => $message->id,
                    'message' => e($message->message),
                    'is_me' => true,
                    'sender_name' => $message->is_prefectura ? 'Prefectura (Oficina)' : $user->name,
                    'is_prefectura' => $message->is_prefectura,
                    'created_at' => $message->created_at->format('d/m/Y h:i A'),
                ],
            ], 201);
        }

        return back()->with('success', 'Mensaje enviado correctamente.');
    }
}

