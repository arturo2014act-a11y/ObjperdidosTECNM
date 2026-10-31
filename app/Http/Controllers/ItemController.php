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
     * Catálogo institucional de áreas del campus (RF-32, RF-33).
     */
    public const AREAS = [
        'Campus Poniente - Edificio A',
        'Campus Poniente - Edificio B',
        'Campus Poniente - Edificio C',
        'Campus Poniente - Edificio D',
        'Campus Poniente - Edificio E',
        'Campus Poniente - Edificio F',
        'Campus Poniente - Edificio G',
        'Campus Poniente - Edificio H (Laboratorios)',
        'Campus Poniente - Cafetería Principal',
        'Campus Poniente - Biblioteca Central',
        'Campus Poniente - Canchas Deportivas',
        'Campus Poniente - Explanada Cívica',
        'Campus Central - Edificio Administrativo',
        'Campus Central - Biblioteca',
        'Otra área del campus',
    ];

    /**
     * Muro cronológico único de publicaciones (RF-12, RF-23, RF-39, RF-40, RF-41, RF-43).
     */
    public function index(Request $request): View
    {
        // En prototipo: asegurar usuario autenticado predeterminado si no hay sesión
        if (!Auth::check()) {
            $defaultUser = \App\Models\User::first();
            if ($defaultUser) {
                Auth::login($defaultUser);
            }
        }

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
        $isStaff = $user && in_array($user->role, ['prefectura', 'admin']);

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
        return view('items.create', [
            'categories' => self::CATEGORIES,
            'areas' => self::AREAS,
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

        // Reglas de validación dinámicas según el tipo (RF-18)
        $rules = [
            'type' => ['required', 'in:perdido,encontrado'],
            'name' => ['required', 'string', 'max:100'], // RF-38
            'description' => ['required', 'string', 'max:500'], // RF-38
            'category' => ['required', 'in:' . implode(',', self::CATEGORIES)], // RF-30
            'category_specification' => ['nullable', 'string', 'max:100'], // RF-31
            'area' => ['required', 'in:' . implode(',', self::AREAS)], // RF-32, RF-33
            'academic_class' => ['nullable', 'string', 'max:100'], // RF-35, RF-36
        ];

        // Especificación obligatoria si selecciona Otros (RF-31)
        if ($request->input('category') === 'Otros') {
            $rules['category_specification'] = ['required', 'string', 'max:100'];
        }

        // Imagen y fecha son OBLIGATORIAS para Encontrado, y OPCIONALES para Perdido (RF-18, RF-34, RNF-02)
        if ($request->input('type') === 'encontrado') {
            $rules['event_date'] = ['required', 'date', 'before_or_equal:today'];
            // Para Encontrado la imagen es obligatoria (salvo si prefectura la publica sin ella)
            $rules['image'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']; // máx 5MB (RNF-02)
        } else {
            $rules['event_date'] = ['nullable', 'date', 'before_or_equal:today'];
            $rules['image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
        }

        $messages = [
            'name.required' => 'El nombre del objeto es obligatorio.',
            'name.max' => 'El nombre no debe superar los 100 caracteres.',
            'description.required' => 'La descripción del objeto es obligatoria.',
            'description.max' => 'La descripción no debe exceder los 500 caracteres.',
            'category.required' => 'Debes seleccionar una categoría del catálogo.',
            'category_specification.required' => 'Al seleccionar la categoría Otros, debes especificar el tipo de objeto.',
            'area.required' => 'Debes seleccionar el área o edificio donde ocurrió el suceso.',
            'event_date.required' => 'La fecha del hallazgo es obligatoria para objetos encontrados.',
            'event_date.before_or_equal' => 'La fecha no puede ser posterior al día de hoy.',
            'image.required' => 'Es obligatorio subir una fotografía para objetos encontrados.',
            'image.image' => 'El archivo debe ser una imagen válida (JPG, PNG o WEBP).',
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
        if ($validated['type'] === 'perdido') {
            // RF-20: Un reporte Perdido válido se publica inmediatamente
            $status = 'publicado';
            $successMessage = 'Tu publicación se ha publicado correctamente.'; // RF-59
        } else {
            // Es de tipo 'encontrado'
            if ($user->role === 'prefectura') {
                // RF-22: Prefectura publica directamente cuando el objeto ya está en custodia
                $status = 'publicado';
                $successMessage = 'Publicación de objeto encontrado registrada directamente en el muro por Prefectura.';
            } else {
                // RF-19: Encontrado por usuario general queda pendiente e invisible en el muro
                $status = 'pendiente';
                $successMessage = 'Tu reporte fue registrado y está pendiente de entrega y aprobación por prefectura.'; // RF-59
            }
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
            abort(403, 'No tienes autorización para solicitar el cierre de esta publicación.');
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

