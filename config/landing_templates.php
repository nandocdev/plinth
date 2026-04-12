<?php

declare(strict_types=1);

return [
   'corporate' => [
      'name' => 'Clásica Corporativa',
      'vibe' => 'Profesional · B2B',
      'font_family' => 'instrument',
      'primary_color' => '#2563eb',
      'global_settings' => [
         'bg_mode' => 'light',
         'color_neutral' => '#e2e8f0',
         'color_accent' => '#0f172a',
      ],
      'blocks' => [
         ['type' => 'hero', 'defaults' => ['headline' => 'Tu operación, más clara y más rentable.', 'subheadline' => 'Una plataforma preparada para equipos que necesitan control, visibilidad y escalabilidad.', 'cta_text' => 'Solicitar demo', 'cta_url' => '/register']],
         ['type' => 'services', 'defaults' => ['title' => 'Qué resuelves con nosotros', 'items' => [['title' => 'Implementación guiada', 'description' => 'Activación con acompañamiento y buenas prácticas desde el día uno.'], ['title' => 'Procesos centralizados', 'description' => 'Unifica operaciones, usuarios y visibilidad en un solo workspace.'], ['title' => 'Métricas accionables', 'description' => 'Toma decisiones con información útil, no con intuición.']]]],
         ['type' => 'testimonials', 'defaults' => ['title' => 'Equipos que ya operan mejor', 'items' => [['quote' => 'Pasamos de hojas sueltas a una operación ordenada en pocas semanas.', 'author' => 'Andrea Gómez', 'role' => 'Directora de Operaciones']]]],
         ['type' => 'cta', 'defaults' => ['title' => 'Consolida tu operación', 'subtitle' => 'Empieza con un workspace listo para crecer con tu equipo.', 'button_text' => 'Crear workspace', 'button_url' => '/register']]
      ],
   ],
   'visual' => [
      'name' => 'Visual / Experiencia',
      'vibe' => 'Restaurantes · Spas',
      'font_family' => 'sans',
      'primary_color' => '#db2777',
      'global_settings' => [
         'bg_mode' => 'soft',
         'color_neutral' => '#f5d0fe',
         'color_accent' => '#7c3aed',
      ],
      'blocks' => [
         ['type' => 'hero', 'defaults' => ['headline' => 'Diseña una experiencia que se vea tan bien como se siente.', 'subheadline' => 'Convierte tu landing en una vitrina elegante para reservas, servicios y diferenciación.', 'cta_text' => 'Reservar ahora', 'cta_url' => '/register']],
         ['type' => 'services', 'defaults' => ['title' => 'Experiencias destacadas', 'items' => [['title' => 'Atención premium', 'description' => 'Presenta tus servicios con una estética cuidada y memorable.'], ['title' => 'Marca consistente', 'description' => 'Colores, mensajes y bloques alineados con tu identidad visual.'], ['title' => 'Conversión sin ruido', 'description' => 'Un recorrido simple para que el visitante tome acción.']]]],
         ['type' => 'testimonials', 'defaults' => ['title' => 'Lo que perciben tus clientes', 'items' => [['quote' => 'Nuestra marca se siente mucho más premium desde la primera visita.', 'author' => 'Lucía Méndez', 'role' => 'Founder']]]],
         ['type' => 'cta', 'defaults' => ['title' => 'Haz que tu marca destaque', 'subtitle' => 'Publica una landing visualmente coherente y lista para convertir.', 'button_text' => 'Comenzar ahora', 'button_url' => '/register']]
      ],
   ],
   'conversion' => [
      'name' => 'Conversión Directa',
      'vibe' => 'Ventas · Urgencia',
      'font_family' => 'sans',
      'primary_color' => '#dc2626',
      'global_settings' => [
         'bg_mode' => 'light',
         'color_neutral' => '#fee2e2',
         'color_accent' => '#111827',
      ],
      'blocks' => [
         ['type' => 'hero', 'defaults' => ['headline' => 'Reduce fricción y convierte más rápido.', 'subheadline' => 'Una landing enfocada en propuesta de valor, prueba social y CTA inmediato.', 'cta_text' => 'Empezar gratis', 'cta_url' => '/register']],
         ['type' => 'services', 'defaults' => ['title' => 'Por qué convierte mejor', 'items' => [['title' => 'Mensaje directo', 'description' => 'La propuesta principal aparece clara desde el primer scroll.'], ['title' => 'Señales de confianza', 'description' => 'Servicios y testimonios trabajan juntos para sostener la decisión.'], ['title' => 'CTA persistente', 'description' => 'La acción principal está presente sin distraer.']]]],
         ['type' => 'testimonials', 'defaults' => ['title' => 'Resultados reales', 'items' => [['quote' => 'La nueva landing nos permitió aumentar los registros desde la primera semana.', 'author' => 'Carlos Ruiz', 'role' => 'Growth Lead']]]],
         ['type' => 'cta', 'defaults' => ['title' => 'Optimiza tu conversión hoy', 'subtitle' => 'Activa una base lista para captar leads y moverlos a la acción.', 'button_text' => 'Crear cuenta', 'button_url' => '/register']]
      ],
   ],
   'storytelling' => [
      'name' => 'Storytelling',
      'vibe' => 'Personal · Coaches',
      'font_family' => 'slab',
      'primary_color' => '#7c3aed',
      'global_settings' => [
         'bg_mode' => 'soft',
         'color_neutral' => '#ede9fe',
         'color_accent' => '#581c87',
      ],
      'blocks' => [
         ['type' => 'hero', 'defaults' => ['headline' => 'Cuenta una historia que conecte antes de vender.', 'subheadline' => 'Construye una narrativa clara sobre tu propuesta, tu experiencia y el cambio que ofreces.', 'cta_text' => 'Quiero conocer más', 'cta_url' => '/register']],
         ['type' => 'services', 'defaults' => ['title' => 'Cómo acompañas a tus clientes', 'items' => [['title' => 'Diagnóstico inicial', 'description' => 'Alinea expectativas y detecta oportunidades concretas.'], ['title' => 'Proceso estructurado', 'description' => 'Muestra un método que inspira confianza y seguimiento.'], ['title' => 'Transformación visible', 'description' => 'Explica el antes y el después que pueden esperar.']]]],
         ['type' => 'testimonials', 'defaults' => ['title' => 'Historias de transformación', 'items' => [['quote' => 'La landing transmite exactamente la profundidad del proceso que ofrezco.', 'author' => 'Paola Herrera', 'role' => 'Coach Ejecutiva']]]],
         ['type' => 'cta', 'defaults' => ['title' => 'Empieza a contar mejor tu historia', 'subtitle' => 'Convierte tu experiencia en una landing que inspire confianza.', 'button_text' => 'Crear mi landing', 'button_url' => '/register']]
      ],
   ],
   'catalog' => [
      'name' => 'Marketplace / Catálogo',
      'vibe' => 'Tiendas · Grids',
      'font_family' => 'sans',
      'primary_color' => '#16a34a',
      'global_settings' => [
         'bg_mode' => 'light',
         'color_neutral' => '#dcfce7',
         'color_accent' => '#14532d',
      ],
      'blocks' => [
         ['type' => 'hero', 'defaults' => ['headline' => 'Organiza tu oferta y hazla fácil de explorar.', 'subheadline' => 'Ideal para catálogos de servicios, paquetes o productos con una propuesta clara.', 'cta_text' => 'Ver catálogo', 'cta_url' => '#services']],
         ['type' => 'services', 'defaults' => ['title' => 'Categorías destacadas', 'items' => [['title' => 'Productos estrella', 'description' => 'Agrupa la oferta con foco comercial.'], ['title' => 'Servicios recurrentes', 'description' => 'Presenta tus opciones con una estructura clara.'], ['title' => 'Nuevas incorporaciones', 'description' => 'Da visibilidad rápida a lo más reciente.']]]],
         ['type' => 'testimonials', 'defaults' => ['title' => 'Compradores satisfechos', 'items' => [['quote' => 'Ahora nuestros servicios se entienden mejor y se venden más fácil.', 'author' => 'Javier Soto', 'role' => 'Ecommerce Manager']]]],
         ['type' => 'cta', 'defaults' => ['title' => 'Publica tu catálogo', 'subtitle' => 'Empieza con una base lista para ordenar tu oferta y llevar tráfico a conversión.', 'button_text' => 'Activar catálogo', 'button_url' => '/register']]
      ],
   ],
   'minimal' => [
      'name' => 'One Page Minimal',
      'vibe' => 'Simple · MVP',
      'font_family' => 'mono',
      'primary_color' => '#0f172a',
      'global_settings' => [
         'bg_mode' => 'light',
         'color_neutral' => '#e5e7eb',
         'color_accent' => '#334155',
      ],
      'blocks' => [
         ['type' => 'hero', 'defaults' => ['headline' => 'Una landing limpia para salir rápido al mercado.', 'subheadline' => 'Menos adornos, más claridad. Ideal para validar propuesta y captar interés.', 'cta_text' => 'Probar ahora', 'cta_url' => '/register']],
         ['type' => 'services', 'defaults' => ['title' => 'Lo esencial', 'items' => [['title' => 'Mensaje claro', 'description' => 'Explica qué haces sin saturar al visitante.'], ['title' => 'Estructura simple', 'description' => 'Una sola página con recorrido corto y efectivo.'], ['title' => 'Salida rápida', 'description' => 'Perfecta para MVPs y lanzamientos iniciales.']]]],
         ['type' => 'testimonials', 'defaults' => ['title' => 'Validación temprana', 'items' => [['quote' => 'Nos permitió salir rápido con una presencia profesional y editable.', 'author' => 'Nicolás Vega', 'role' => 'Founder']]]],
         ['type' => 'cta', 'defaults' => ['title' => 'Lanza sin fricción', 'subtitle' => 'Empieza con una base mínima y mejora iterativamente.', 'button_text' => 'Crear workspace', 'button_url' => '/register']]
      ],
   ],
];
