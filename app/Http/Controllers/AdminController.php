<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Listado y búsqueda de cuentas institucionales (RF-27).
     */
    public function index(Request $request): View
    {
        $query = User::with('userLogs');

        // Búsqueda por nombre o correo institucional
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        // Filtro por rol
        if ($request->filled('role') && in_array($request->role, ['usuario_general', 'prefectura', 'admin'])) {
            $query->where('role', $request->role);
        }

        // Filtro por estado activo/desactivado
        if ($request->filled('status')) {
            if ($request->status === 'activa') {
                $query->where('is_active', true);
            } elseif ($request->status === 'desactivada') {
                $query->where('is_active', false);
            }
        }

        $users = $query->orderBy('name', 'asc')->paginate(12)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Detalle individual de cuenta con su historial administrativo (RF-89, RF-90).
     */
    public function show(User $user): View
    {
        $user->load(['userLogs.admin', 'items']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Desactivar cuenta con motivo justificado obligatorio (RF-67, RF-87, RF-91).
     */
    public function deactivate(Request $request, User $user): RedirectResponse
    {
        // Regla de seguridad: el administrador no puede desactivar su propia cuenta
        if (Auth::id() === $user->id) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta de administrador.');
        }

        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'min:5', 'max:255'], // RF-91: máx 255 caracteres
        ], [
            'reason.required' => 'Debe ingresar obligatoriamente un motivo para desactivar la cuenta (RF-87).',
            'reason.min' => 'El motivo debe tener al menos 5 caracteres.',
            'reason.max' => 'El motivo no puede superar los 255 caracteres (RF-91).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $user->update([
            'is_active' => false,
            'deactivation_reason' => $validated['reason'],
        ]);

        // Guardar movimiento de cuenta (RF-89)
        UserLog::create([
            'user_id' => $user->id,
            'admin_id' => Auth::id() ?? 1,
            'action' => 'desactivacion',
            'reason' => $validated['reason'],
        ]);

        return back()->with('warning', "La cuenta de '{$user->name}' ha sido desactivada correctamente.");
    }

    /**
     * Reactivar cuenta con motivo justificado obligatorio (RF-88, RF-91).
     */
    public function reactivate(Request $request, User $user): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'min:5', 'max:255'], // RF-91
        ], [
            'reason.required' => 'Debe ingresar obligatoriamente un motivo para reactivar la cuenta (RF-88).',
            'reason.min' => 'El motivo debe tener al menos 5 caracteres.',
            'reason.max' => 'El motivo no puede superar los 255 caracteres (RF-91).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $user->update([
            'is_active' => true,
            'deactivation_reason' => null,
        ]);

        // Guardar movimiento de cuenta (RF-89)
        UserLog::create([
            'user_id' => $user->id,
            'admin_id' => Auth::id() ?? 1,
            'action' => 'activacion',
            'reason' => $validated['reason'],
        ]);

        return back()->with('success', "La cuenta de '{$user->name}' ha sido reactivada exitosamente.");
    }
}

