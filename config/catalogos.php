<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Catálogo institucional de Categorías (RF-30, RF-31)
    |--------------------------------------------------------------------------
    | Catálogo configurable mediante datos iniciales.
    | NOTA PARA EL EQUIPO:
    | [PENDIENTE DE CONCRETAR] El catálogo definitivo de categorías completas
    | está pendiente de acordar con los interesados del TecNM.
    | 'Otros' es obligatorio y requiere especificación adicional (RF-31).
    */
    'categories' => [
        'Electrónica',
        'Documentos Oficiales y Credenciales',
        'Mochilas y Bolsos',
        'Prendas de Ropa y Calzado',
        'Material Escolar y Libros',
        'Accesorios Personales y Joyería',
        'Artículos Deportivos',
        'Llaves',
        'Otros',
    ],

    /*
    |--------------------------------------------------------------------------
    | Catálogo de Ubicaciones Generales (Campus Poniente) (RF-32, RF-33, Anexo A)
    |--------------------------------------------------------------------------
    | Incluye edificios H, G, B y C y las áreas comunes iniciales del Anexo A.
    | NOTA PARA EL EQUIPO:
    | [PROVISIONAL] El catálogo de áreas comunes está marcado como provisional
    | hasta validar su denominación institucional definitiva.
    | 'Otra área del campus' no solicita texto ni espacio específico (RF-33).
    */
    'areas' => [
        // Edificios principales
        'Edificio H',
        'Edificio G',
        'Edificio B',
        'Edificio C',
        // Áreas comunes iniciales (Provisionales - Anexo A)
        'Cafetería Principal (Provisional)',
        'Biblioteca Central (Provisional)',
        'Canchas Deportivas (Provisional)',
        'Explanada Cívica (Provisional)',
        // Ubicación especial sin espacio específico (RF-33)
        'Otra área del campus',
    ],

    /*
    |--------------------------------------------------------------------------
    | Catálogo Dependiente de Espacios Específicos (RF-32)
    |--------------------------------------------------------------------------
    | - Edificio H: H1 a H12.
    | - Edificio G: G1 a G8 y LCOM1 a LCOM4.
    | - Edificios B y C: Sin aulas ficticias; sus espacios están pendientes
    |   de levantamiento en campo.
    | - Áreas comunes: Espacio específico sin especificar.
    */
    'spaces' => [
        'Edificio H' => [
            'H1', 'H2', 'H3', 'H4', 'H5', 'H6',
            'H7', 'H8', 'H9', 'H10', 'H11', 'H12',
        ],
        'Edificio G' => [
            'G1', 'G2', 'G3', 'G4', 'G5', 'G6', 'G7', 'G8',
            'LCOM1', 'LCOM2', 'LCOM3', 'LCOM4',
        ],
        // Edificio B y C intencionalmente vacíos (sin aulas ficticias - pendientes de levantar)
        'Edificio B' => [],
        'Edificio C' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dominios de Correo Institucionales Autorizados (RF-25, RF-78, RF-79)
    |--------------------------------------------------------------------------
    | Se restringe únicamente a @merida.tecnm.mx para evitar duplicidad de
    | cuentas por persona (eliminando @itmerida.edu.mx).
    */
    'allowed_email_domains' => [
        'merida.tecnm.mx',
    ],

    /*
    |--------------------------------------------------------------------------
    | Elementos pendientes de acordar / Notas para el equipo (SCR-33 / Anexo A)
    |--------------------------------------------------------------------------
    */
    'pending_agreements' => [
        'categories_catalog' => 'Catálogo completo y definitivo de categorías de objetos pendiente de acordar.',
        'category_other_max_length' => 'Longitud máxima definitiva del campo especificación de categoría Otros pendiente.',
        'common_areas_naming' => 'Denominación oficial de las áreas comunes del Campus Poniente (Anexo A) con carácter provisional hasta su validación.',
        'buildings_b_c_spaces' => 'Levantamiento físico de aulas y laboratorios correspondientes a los Edificios B y C.',
        'email_domain_unification' => 'Dominio unificado a @merida.tecnm.mx para prevenir cuentas duplicadas por usuario.',
    ],

];
