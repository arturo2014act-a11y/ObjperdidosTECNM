<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Catálogo institucional de categorías (RF-30).
     */
    public const CATEGORIES = [
        'Electrónica',
        'Documentos Oficiales y Credenciales',
        'Mochilas y Bolsos',
        'Prendas de Ropa y Calzado',
        'Material Escolar y Libros',
        'Accesorios Personales y Joyería',
        'Artículos Deportivos',
        'Llaves',
        'Otros',
    ];

    /**
     * Catálogo institucional de áreas generales del Campus Poniente (RF-32, RF-33).
     */
    public const AREAS = [
        'Edificio H',
        'Edificio G',
        'Edificio B',
        'Edificio C',
        'Cafetería Principal',
        'Biblioteca Central',
        'Canchas Deportivas',
        'Explanada Cívica',
        'Otra área del campus',
    ];

    /**
     * Catálogo dependiente de espacios específicos (aulas / laboratorios) por edificio (RF-32).
     */
    public const SPACES = [
        'Edificio H' => [
            'H1', 'H2', 'H3', 'H4', 'H5', 'H6',
            'H7', 'H8', 'H9', 'H10', 'H11', 'H12'
        ],
        'Edificio G' => [
            'G1', 'G2', 'G3', 'G4', 'G5', 'G6', 'G7', 'G8',
            'LCOM1', 'LCOM2', 'LCOM3', 'LCOM4'
        ],
    ];

    /**
     * Muro cronológico único de publicaciones (RF-12, RF-23, RF-39, RF-40, RF-41, RF-43).
     */
    public function index(Request $request): View
    {
        // Muro público accesible para invitados y usuarios autenticados

        // Solo publicaciones aprobadas / activas en el muro
        $query = Item::with('user')->where('status', 'publicado');

        // Filtro por tipo: Todos, Perdido, Encontrado (RF-23)
        if ($request->filled('type') && in_array($request->type, ['perdido', 'encontrado'])) {
            $query->where('type', $request->type);
        }

        // Búsqueda por palabras clave: ÚNICAMENTE en nombre y descripción (RF-39)
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // Orden cronológico: Más recientes (predeterminado) o Más antiguas (RF-40)
        $order = $request->get('order', 'recientes');
        if ($order === 'antiguas') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginación tradicional de 10 publicaciones por página (RF-41)
        $items = $query->paginate(10)->withQueryString();

        return view('items.index', compact('items', 'order'));
    }

    /**
     * Detalle completo de una publicación (RF-42, RF-43, RF-44).
     */
    public function show(Item $item): View
    {
        // Asegurar que solo publicaciones activas se vean públicamente (salvo si es el autor, prefectura o admin)
        $user = Auth::user();
        $isAuthor = $user && $user->id === $item->user_id;
        $isStaff = $user && in_array($user->role, ['prefectura', 'admin'], true);

        if ($item->status !== 'publicado' && !$isAuthor && !$isStaff) {
            abort(404);
        }

        return view('items.show', compact('item', 'isAuthor', 'isStaff'));
    }

    /**
     * Formulario para reportar objeto Perdido o Encontrado (RF-13, RF-18, RF-30 a RF-36).
     */
    public function create(): View
    {
        // Calcular 14 días hábiles atrás (lunes a viernes) para la fecha mínima permitida
        $subBusinessDays = function (\Carbon\Carbon $date, int $days) {
            $d = $date->copy();
            while ($days > 0) {
                $d->subDay();
                if (!$d->isWeekend()) {
                    $days--;
                }
            }
            return $d;
        };

        $minDatePerdido = $subBusinessDays(now(), 14)->format('Y-m-d');

        return view('items.create', [
            'categories' => config('catalogos.categories', self::CATEGORIES),
            'areas' => config('catalogos.areas', self::AREAS),
            'spaces' => config('catalogos.spaces', self::SPACES),
            'minDatePerdido' => $minDatePerdido,
        ]);
    }

    /**
     * Registrar la publicación en la base de datos (RF-18, RF-19, RF-20, RF-22, RNF-02, RF-59).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('items.index')->with('error', 'Debes iniciar sesión para reportar un objeto.');
        }

        $categories = config('catalogos.categories', self::CATEGORIES);
        $areas = config('catalogos.areas', self::AREAS);

        // Reglas de validación dinámicas según el tipo (RF-18)
        $rules = [
            'type' => ['required', 'in:perdido,encontrado'],
            'name' => ['required', 'string', 'max:100'], // RF-38
            'description' => ['required', 'string', 'max:500'], // RF-38
            'category' => ['required', 'in:' . implode(',', $categories)], // RF-30
            'category_specification' => ['nullable', 'string', 'max:100'], // RF-31
            'area' => ['required', 'in:' . implode(',', $areas)], // RF-32, RF-33
            'academic_class' => ['nullable', 'string', 'max:100'], // RF-35, RF-36
        ];

        // Especificación obligatoria si selecciona Otros (RF-31)
        if ($request->input('category') === 'Otros') {
            $rules['category_specification'] = ['required', 'string', 'max:100'];
        }

        // Función auxiliar para calcular días hábiles hacia atrás (lunes a viernes)
        $subBusinessDays = function (\Carbon\Carbon $date, int $days) {
            $d = $date->copy();
            while ($days > 0) {
                $d->subDay();
                if (!$d->isWeekend()) {
                    $days--;
                }
            }
            return $d;
        };

        $minDatePerdido = $subBusinessDays(now(), 14)->format('Y-m-d');

        // Imagen OBLIGATORIA para cualquier tipo de publicación (Perdido o Encontrado)
        $rules['image'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']; // máx 5MB (RNF-02)

        // Fecha según el tipo de objeto
        if ($request->input('type') === 'encontrado') {
            $rules['event_date'] = ['required', 'date', 'before_or_equal:today'];
        } else {
            // Perdido: Fecha del extravío OBLIGATORIA y válida solo hasta 14 días hábiles atrás
            $rules['event_date'] = [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . $minDatePerdido,
            ];
        }

        $messages = [
            'name.required' => 'El nombre del objeto es obligatorio.',
            'name.max' => 'El nombre no debe superar los 100 caracteres.',
            'description.required' => 'La descripción del objeto es obligatoria.',
            'description.max' => 'La descripción no debe exceder los 500 caracteres.',
            'category.required' => 'Debes seleccionar una categoría del catálogo.',
            'category_specification.required' => 'Al seleccionar la categoría Otros, debes especificar el tipo de objeto.',
            'area.required' => 'Debes seleccionar el área o edificio donde ocurrió el suceso.',
            'event_date.required' => 'La fecha del suceso es obligatoria.',
            'event_date.before_or_equal' => 'La fecha no puede ser posterior al día de hoy.',
            'event_date.after_or_equal' => "La fecha del reporte de extravío no puede superar los 14 días hábiles de antigüedad (máximo desde el {$minDatePerdido}).",
            'image.required' => 'Es obligatorio subir una fotografía del objeto reportado.',
            'image.image' => 'El archivo debe ser una imagen válida (JPG, PNG o WEBP).',
            'image.mimes' => 'La imagen debe ser de tipo: JPG, JPEG, PNG o WEBP.',
            'image.max' => 'La imagen no puede pesar más de 5 MB (RNF-02).',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Procesar subida de imagen a storage/app/public/items
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('items', 'public');
        }

        // Determinación del estado según Tipo y Rol
        $user = $request->user();
        if ($user->role === 'prefectura') {
            // SCR-47 / RF-22: Prefectura publica directamente en el muro sin pasar por aprobación
            $status = 'publicado';
            $successMessage = $validated['type'] === 'perdido' 
                ? 'Tu publicación se ha publicado correctamente en el muro.'
                : 'Publicación de objeto encontrado registrada y publicada directamente en el muro por Prefectura.';
        } else {
            // Rol Comunidad / Usuario General: Ambos tipos pasan a validación previa por Prefectura
            $status = 'pendiente';
            $successMessage = $validated['type'] === 'perdido'
                ? 'Tu reporte de objeto perdido fue registrado y ha sido enviado al panel de Prefectura para su validación antes de publicarse en el muro.'
                : 'Tu reporte fue registrado y está pendiente de entrega y aprobación por prefectura.';
        }

        $item = Item::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'category_specification' => $validated['category_specification'] ?? null,
            'area' => $validated['area'],
            'academic_class' => $validated['academic_class'] ?? null,
            'image_path' => $imagePath,
            'event_date' => $validated['event_date'] ?? null,
            'status' => $status,
        ]);

        // Si fue prefectura quien publicó directamente un encontrado, auditar la acción (RF-56)
        if ($user->role === 'prefectura' && $validated['type'] === 'encontrado') {
            ItemLog::create([
                'item_id' => $item->id,
                'user_id' => $user->id,
                'action' => 'publicacion_directa_prefectura',
                'reason' => 'Objeto en custodia física registrado y publicado directamente por Prefectura.',
                'ip_address' => $request->ip(),
            ]);
        }

        // Redirigir a "Mis publicaciones" (RF-59)
        return redirect()->route('items.my-items')->with('success', $successMessage);
    }

    /**
     * Sección "Mis publicaciones" del usuario autenticado (RF-24, RF-49).
     */
    public function myItems(Request $request): View
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('items.index')->with('error', 'Debes iniciar sesión para consultar tus publicaciones.');
        }

        $myItems = Item::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('items.my-items', compact('myItems'));
    }

    /**
     * Acción "Ya encontré mi objeto": Solicitud de cierre por el autor de un Perdido (RF-17, RF-49).
     */
    public function requestClosure(Request $request, Item $item): RedirectResponse
    {
        $user = Auth::user();

        // Validar que el usuario sea el autor de la publicación
        if (!$user || $item->user_id !== $user->id) {
            return redirect()->route('items.my')->with('error', 'No tienes autorización para solicitar el cierre de esta publicación.');
        }

        // Solo objetos perdidos en estado activo/publicado pueden solicitar cierre
        if ($item->type !== 'perdido' || $item->status !== 'publicado') {
            return back()->with('error', 'Solo publicaciones de objetos perdidos activos pueden solicitar cierre.');
        }

        // Cambiar a cierre_pendiente (RF-49: Cierre pendiente de revisión)
        $item->update([
            'status' => 'cierre_pendiente',
        ]);

        return back()->with('success', 'Has solicitado el cierre de tu publicación ("Ya encontré mi objeto"). Prefectura validará la solicitud a la brevedad.');
    }
}

