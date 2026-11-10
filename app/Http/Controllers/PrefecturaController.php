<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PrefecturaController extends Controller
{
    /**
     * Panel Principal de Gestión de Prefectura (RF-50, RF-51, RF-44).
     */
    public function dashboard(Request $request): View
    {
        // El middleware 'auth' garantiza usuario autenticado

        // Sección 1: Encontrados pendientes de aprobación (RF-50)
        // Invisibles en el muro público, se muestran con datos del autor (RF-44)
        $pendingFound = Item::with(['user', 'logs.user'])
            ->where('type', 'encontrado')
            ->where('status', 'pendiente')
            ->latest()
            ->get();

        // Sección 2: Solicitudes de cierre pendientes (RF-50)
        // Objetos perdidos donde el autor solicitó el cierre
        $pendingClosures = Item::with(['user', 'logs.user'])
            ->where('type', 'perdido')
            ->where('status', 'cierre_pendiente')
            ->latest()
            ->get();

        // Sección 3: Publicaciones bajo gestión con filtros y buscador (RF-51)
        $managedQuery = Item::with(['user', 'logs.user'])
            ->whereIn('status', ['publicado', 'devuelto', 'rechazado', 'cerrado']);

        // Filtro por tipo
        if ($request->filled('type') && in_array($request->type, ['perdido', 'encontrado'])) {
            $managedQuery->where('type', $request->type);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $statusInput = $request->status === 'activo' ? 'publicado' : $request->status;
            if (in_array($statusInput, ['publicado', 'devuelto', 'rechazado', 'cerrado'])) {
                $managedQuery->where('status', $statusInput);
            }
        }

        // Buscador por nombre del objeto
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $managedQuery->where('name', 'like', '%' . $searchTerm . '%');
        }

        $managedItems = $managedQuery->latest()->paginate(10)->withQueryString();

        // Registros globales recientes de auditoría para supervisión (RF-56)
        $recentAuditLogs = ItemLog::with(['item', 'user'])
            ->latest()
            ->take(15)
            ->get();

        return view('prefectura.dashboard', compact(
            'pendingFound',
            'pendingClosures',
            'managedItems',
            'recentAuditLogs'
        ));
    }

    /**
     * Sección 1 - Acción: Aprobar objeto encontrado con confirmación de recepción física (RF-53, RF-56).
     */
    public function approveFound(Request $request, Item $item): RedirectResponse
    {
        if ($item->type !== 'encontrado' || $item->status !== 'pendiente') {
            return back()->with('error', 'El objeto seleccionado no es válido para aprobación.');
        }

        $request->validate([
            'confirm_physical_receipt' => ['required', 'accepted'],
        ], [
            'confirm_physical_receipt.accepted' => 'Es obligatorio confirmar que el objeto fue recibido físicamente en la oficina de Prefectura.',
        ]);

        $item->update([
            'status' => 'publicado',
            'rejection_reason' => null,
        ]);

        $auditReason = 'Objeto recibido físicamente en Prefectura y aprobado para publicación en muro.';

        // Registrar auditoría interna (RF-56)
        $this->recordAuditLog(
            item: $item,
            action: 'aprobacion_recepcion',
            reason: $auditReason,
            ipAddress: $request->ip()
        );

        return back()->with('success', "El objeto '{$item->name}' fue aprobado y ya está visible en el muro.");
    }

    /**
     * Sección 1 - Acción: Rechazar objeto encontrado exigiendo motivo obligatorio (RF-21, RF-56).
     */
    public function rejectFound(Request $request, Item $item): RedirectResponse
    {
        if ($item->status !== 'pendiente') {
            return back()->with('error', 'El objeto seleccionado no es válido para rechazo.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'Debe ingresar obligatoriamente un motivo para rechazar la publicación.',
            'rejection_reason.min' => 'El motivo de rechazo debe contener al menos 5 caracteres.',
            'rejection_reason.max' => 'El motivo de rechazo no puede exceder los 500 caracteres.',
        ]);

        $item->update([
            'status' => 'rechazado',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Registrar auditoría interna (RF-56)
        $this->recordAuditLog(
            item: $item,
            action: 'rechazo_encontrado',
            reason: $validated['rejection_reason'],
            ipAddress: $request->ip()
        );

        return back()->with('warning', "La publicación '{$item->name}' fue rechazada.");
    }

    /**
     * Sección 2 - Acción: Aprobar solicitud de cierre de objeto perdido (RF-50, RF-56).
     */
    public function approveClosure(Request $request, Item $item): RedirectResponse
    {
        if ($item->type !== 'perdido' || $item->status !== 'cierre_pendiente') {
            return back()->with('error', 'La publicación no cuenta con una solicitud de cierre pendiente válida.');
        }

        $item->update([
            'status' => 'cerrado',
        ]);

        // Registrar auditoría interna (RF-56)
        $this->recordAuditLog(
            item: $item,
            action: 'cierre_aprobado',
            reason: 'Solicitud de cierre aprobada. Objeto reportado como recuperado por el autor.',
            ipAddress: $request->ip()
        );

        return back()->with('success', "La solicitud de cierre para '{$item->name}' fue aprobada. La publicación ha quedado cerrada.");
    }

    /**
     * Sección 2 - Acción: Rechazar solicitud de cierre con motivo obligatorio (RF-50, RF-56).
     */
    public function rejectClosure(Request $request, Item $item): RedirectResponse
    {
        if ($item->type !== 'perdido' || $item->status !== 'cierre_pendiente') {
            return back()->with('error', 'La publicación no cuenta con una solicitud de cierre pendiente válida.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'Debe ingresar el motivo por el cual se rechaza el cierre de la publicación.',
            'rejection_reason.min' => 'El motivo de rechazo debe contener al menos 5 caracteres.',
            'rejection_reason.max' => 'El motivo de rechazo no puede exceder los 500 caracteres.',
        ]);

        // Regresa a estado publicado (activo en el muro)
        $item->update([
            'status' => 'publicado',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Registrar auditoría interna (RF-56)
        $this->recordAuditLog(
            item: $item,
            action: 'cierre_rechazado',
            reason: $validated['rejection_reason'],
            ipAddress: $request->ip()
        );

        return back()->with('warning', "La solicitud de cierre de '{$item->name}' fue rechazada. El objeto vuelve a estar activo en el muro.");
    }

    /**
     * Sección 3 - Acción: Marcar Encontrado como Devuelto con confirmación presencial (RF-14, RF-54, RF-56).
     */
    public function markReturned(Request $request, Item $item): RedirectResponse
    {
        if ($item->type !== 'encontrado' || $item->status === 'devuelto') {
            return back()->with('error', 'Solo publicaciones de tipo Encontrado no devueltas pueden marcarse como devueltas.');
        }

        $validated = $request->validate([
            'confirm_in_person_delivery' => ['required', 'accepted'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'confirm_in_person_delivery.accepted' => 'Debe confirmar que la entrega del objeto se realizó de manera presencial en Prefectura.',
        ]);

        $item->update([
            'status' => 'devuelto',
        ]);

        $reason = 'Entrega presencial confirmada con el propietario.';
        if (!empty($validated['delivery_notes'])) {
            $reason .= ' Nota: ' . $validated['delivery_notes'];
        }

        // Registrar auditoría interna (RF-56)
        $this->recordAuditLog(
            item: $item,
            action: 'objeto_devuelto',
            reason: $reason,
            ipAddress: $request->ip()
        );

        return back()->with('success', "El objeto '{$item->name}' ha sido marcado como Devuelto al propietario.");
    }

    /**
     * Sección 3 - Acción: Revertir un objeto Devuelto a Activo con motivo obligatorio (RF-55, RF-56).
     */
    public function revertReturned(Request $request, Item $item): RedirectResponse
    {
        if ($item->status !== 'devuelto') {
            return back()->with('error', 'Solo los objetos marcados previamente como devueltos pueden revertirse.');
        }

        $validated = $request->validate([
            'revert_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'revert_reason.required' => 'Debe especificar obligatoriamente el motivo de la reversión de entrega.',
            'revert_reason.min' => 'El motivo de reversión debe tener al menos 5 caracteres.',
            'revert_reason.max' => 'El motivo de reversión no puede superar los 500 caracteres.',
        ]);

        $item->update([
            'status' => 'publicado',
        ]);

        // Registrar auditoría interna (RF-56)
        $this->recordAuditLog(
            item: $item,
            action: 'reversion_devolucion',
            reason: $validated['revert_reason'],
            ipAddress: $request->ip()
        );

        return back()->with('warning', "Se revirtió la devolución de '{$item->name}'. El objeto vuelve a figurar como Activo.");
    }

    /**
     * Registro centralizado del Historial de Auditoría Interna (RF-56).
     */
    protected function recordAuditLog(Item $item, string $action, ?string $reason = null, ?string $ipAddress = null): ItemLog
    {
        return ItemLog::create([
            'item_id' => $item->id,
            'user_id' => Auth::id() ?? 1, // Fallback en prototipo si no hay sesión activa
            'action' => $action,
            'reason' => $reason,
            'ip_address' => $ipAddress,
        ]);
    }
}

