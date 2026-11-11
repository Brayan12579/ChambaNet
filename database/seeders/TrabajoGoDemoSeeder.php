<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\District;
use App\Models\JobOffer;
use App\Models\Resource;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrabajoGoDemoSeeder extends Seeder
{
    /**
     * Carga los datos iniciales de la base de datos.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $districts = collect(['Cusco', 'Wanchaq', 'Santiago', 'San Sebastián', 'San Jerónimo', 'Saylla', 'Poroy', 'Ccorca'])
                ->mapWithKeys(function (string $name): array {
                    $district = District::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'active' => true]);

                    return [$name => $district];
                });

            $categories = collect([
                'Gastronomía' => 'utensils',
                'Ventas y atención' => 'store',
                'Hotelería y turismo' => 'hotel',
                'Logística y almacén' => 'truck',
                'Limpieza y mantenimiento' => 'sparkles',
                'Administración' => 'briefcase',
            ])->mapWithKeys(function (string $icon, string $name): array {
                $category = Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'icon' => $icon, 'active' => true]);

                return [$name => $category];
            });

            $plan = SubscriptionPlan::firstOrCreate(['slug' => 'comunidad'], [
                'name' => 'Comunidad', 'monthly_price' => 0, 'vacancy_limit' => 5,
                'features' => ['Publicación de ofertas', 'Gestión de postulaciones'], 'active' => true,
            ]);

            $companies = [];
            foreach ([
                ['Café Inti', 'Cusco', 'Gastronomía', 'Una cafetería de barrio con sabor cusqueño y un equipo cercano.'],
                ['Andes Market', 'Wanchaq', 'Comercio', 'Una tienda de productos de uso diario para las familias de Cusco.'],
                ['Hotel Kantu', 'Cusco', 'Hotelería', 'Un alojamiento local que recibe viajeros con calidez y atención.'],
                ['Ruta Sur Logística', 'San Sebastián', 'Logística', 'Un equipo dedicado a la distribución y organización de mercadería.'],
                ['Muyu Servicios', 'Santiago', 'Servicios', 'Un negocio local especializado en mantenimiento y limpieza de espacios.'],
                ['Panadería Tanta', 'San Jerónimo', 'Gastronomía', 'Un obrador de barrio que prepara pan y productos artesanales.'],
            ] as [$name, $district, $sector, $description]) {
                $slug = 'demo-'.Str::slug($name);
                $user = User::firstOrCreate(['email' => $slug.'@example.test'], [
                    'name' => $name.' · demostración', 'password' => Str::password(48),
                    'role' => 'employer', 'active' => true, 'is_demo' => true,
                ]);
                $companies[$name] = Company::firstOrCreate(['slug' => $slug], [
                    'user_id' => $user->id, 'district_id' => $districts[$district]->id,
                    'subscription_plan_id' => $plan->id, 'name' => $name,
                    'description' => $description.' Empresa ficticia para demostración académica.',
                    'sector' => $sector, 'address' => 'Cusco, Perú · ubicación de demostración',
                    'verified' => in_array($name, ['Café Inti', 'Hotel Kantu', 'Andes Market']), 'is_demo' => true,
                ]);
            }

            $jobs = [
                ['Ayudante de cocina', 'Café Inti', 'Gastronomía', 'Cusco', 'full_time', 1300, 1600, true],
                ['Asesor de ventas', 'Andes Market', 'Ventas y atención', 'Wanchaq', 'full_time', 1400, 1800, true],
                ['Recepcionista de hotel', 'Hotel Kantu', 'Hotelería y turismo', 'Cusco', 'shifts', 1600, 2000, true],
                ['Auxiliar de almacén', 'Ruta Sur Logística', 'Logística y almacén', 'San Sebastián', 'full_time', 1400, 1700, false],
                ['Personal de limpieza', 'Muyu Servicios', 'Limpieza y mantenimiento', 'Santiago', 'part_time', 800, 1000, false],
                ['Atención en cafetería', 'Café Inti', 'Gastronomía', 'Cusco', 'part_time', 800, 1100, false],
                ['Ayudante de panadería', 'Panadería Tanta', 'Gastronomía', 'San Jerónimo', 'shifts', 1300, 1600, false],
                ['Cajero de tienda', 'Andes Market', 'Ventas y atención', 'Wanchaq', 'part_time', 850, 1100, false],
                ['Asistente administrativo', 'Ruta Sur Logística', 'Administración', 'San Sebastián', 'full_time', 1600, 2000, true],
                ['Asistente de reservas', 'Hotel Kantu', 'Hotelería y turismo', 'Cusco', 'full_time', 1500, 1900, false],
                ['Operario de mantenimiento', 'Muyu Servicios', 'Limpieza y mantenimiento', 'Santiago', 'full_time', 1500, 1800, false],
                ['Auxiliar de reparto', 'Ruta Sur Logística', 'Logística y almacén', 'San Jerónimo', 'shifts', 1400, 1800, false],
            ];

            foreach ($jobs as $index => [$title, $company, $category, $district, $schedule, $min, $max, $featured]) {
                JobOffer::firstOrCreate(['slug' => 'demo-'.Str::slug($title)], [
                    'company_id' => $companies[$company]->id, 'category_id' => $categories[$category]->id,
                    'district_id' => $districts[$district]->id, 'title' => $title,
                    'description' => 'Buscamos una persona responsable para integrarse al equipo de '.$companies[$company]->name.'. Participarás en las actividades diarias del negocio, colaborando con un equipo cercano y con orientación inicial. Esta es una oferta ficticia de demostración académica; no representa una vacante real.',
                    'requirements' => "Responsabilidad y puntualidad.\nComunicación amable y disposición para trabajar en equipo.\nDisponibilidad para la jornada indicada.\nSe valora experiencia en funciones similares; algunas posiciones permiten aprender en el puesto.",
                    'conditions' => "Remuneración mensual referencial de S/ {$min} a S/ {$max}.\nFunciones, horario y modalidad de contratación se detallarán durante el proceso.\nInducción inicial y coordinación directa con el responsable del área.\nInformación ficticia para demostrar el sistema; no enviar datos a terceros.",
                    'schedule' => $schedule, 'contract_type' => $index % 3 === 0 ? 'permanent' : 'temporary',
                    'salary_min' => $min, 'salary_max' => $max, 'vacancies' => ($index % 3) + 1,
                    'expires_at' => today()->addDays(30 + $index), 'status' => 'published',
                    'featured' => $featured, 'is_demo' => true, 'created_at' => now()->subHours($index * 8),
                ]);
            }

            foreach ([
                ['Tu primer CV, paso a paso', 'tu-primer-cv', 'Presenta tus habilidades con claridad, incluso si todavía no tienes experiencia laboral.', "Empieza por tus datos de contacto\nIncluye tu nombre, teléfono y correo. Comparte únicamente la información necesaria para el proceso.\n\nCuenta lo que sabes hacer\nDescribe estudios, cursos, proyectos, voluntariado y tareas en las que hayas desarrollado responsabilidad, atención al cliente o trabajo en equipo.\n\nHazlo fácil de leer\nUsa una página si es posible, frases breves y fechas claras. Revisa la ortografía y exporta una copia en PDF.\n\nAdapta tu CV\nDestaca las habilidades que se relacionan con la oferta y conserva una copia actualizada. Nunca inventes experiencia."],
                ['Prepárate para tu próxima entrevista', 'prepara-tu-entrevista', 'Consejos prácticos para contar tu experiencia y llegar con confianza.', "Conoce la oferta\nRevisa las funciones, el distrito y la jornada. Prepara ejemplos de situaciones en las que resolviste un problema o ayudaste a un equipo.\n\nOrganiza tu llegada\nConfirma la dirección y la hora, calcula el traslado y avisa con anticipación si surge un imprevisto.\n\nConversa con claridad\nEscucha cada pregunta y responde con ejemplos concretos. Si no conoces una tarea, explica cómo te prepararías para aprenderla.\n\nPregunta antes de decidir\nConsulta las funciones, horario, remuneración, condiciones de contratación y siguientes pasos. Toma tiempo para revisar la información."],
                ['Busca empleo con más seguridad', 'busca-empleo-seguro', 'Aprende a reconocer señales de alerta y a reportar una oferta sospechosa.', "Verifica quién publica\nRevisa el nombre de la empresa y contrasta sus canales de contacto antes de compartir documentos personales.\n\nProtege tus datos\nUn CV no necesita contraseñas, datos bancarios ni copias de documentos sensibles. Comparte solo lo necesario durante cada etapa.\n\nPresta atención a las condiciones\nDesconfía de ofertas que prometen ingresos sin explicar las funciones o solicitan dinero para participar. Pide información clara por escrito.\n\nUsa el reporte\nSi detectas información sospechosa, utiliza el formulario de reporte del detalle de la oferta. El equipo administrador podrá revisarlo."],
            ] as [$title, $slug, $excerpt, $body]) {
                Resource::firstOrCreate(['slug' => $slug], compact('title', 'excerpt', 'body') + ['published' => true]);
            }
        });

        $this->command?->info('Datos DEMO cargados: empresas y ofertas ficticias de Cusco. No se crearon credenciales de acceso públicas.');
    }
}
