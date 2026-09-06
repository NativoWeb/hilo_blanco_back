<?php

namespace Database\Seeders;

use App\Models\TrunkShow;
use Illuminate\Database\Seeder;

class TrunkShowSeeder extends Seeder
{
    public function run(): void
    {
        $shows = [
            [
                'title' => 'Trunk Show Colección Otoño 2024',
                'slug' => 'trunk-show-otono-2024',
                'subtitle' => 'Colección Otoño 2024',
                'description' => 'Evento exclusivo donde los diseñadores traen la colección completa de otoño a nuestra boutique. Vestidos que normalmente no están disponibles, con la posibilidad de personalizaciones directamente con el equipo de diseño.',
                'city' => 'Bucaramanga',
                'location' => 'Cra 37 No. 36-22, El Prado',
                'start_date' => '2024-11-15',
                'end_date' => '2024-11-18',
                'schedule' => '10:00 AM — 6:00 PM',
                'session_duration' => 90,
                'max_guests' => 3,
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'title' => 'Trunk Show Primavera 2025',
                'slug' => 'trunk-show-primavera-2025',
                'subtitle' => 'Colección Primavera 2025',
                'description' => 'La nueva colección de primavera llega a Medellín. Descubre los diseños más frescos y románticos de la temporada en sesiones privadas con champagne de bienvenida.',
                'city' => 'Medellín',
                'location' => 'El Poblado, Calle 10 #43-12',
                'start_date' => '2025-03-21',
                'end_date' => '2025-03-24',
                'schedule' => '10:00 AM — 7:00 PM',
                'session_duration' => 90,
                'max_guests' => 3,
                'status' => 1,
                'sort_order' => 2,
            ],
        ];

        foreach ($shows as $show) {
            TrunkShow::firstOrCreate(['slug' => $show['slug']], $show);
        }
    }
}
