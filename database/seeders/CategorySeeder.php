<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Colección Clásica',
                'slug' => 'coleccion-clasica',
                'description' => 'Vestidos atemporales con líneas elegantes y cortes clásicos para la novia tradicional.',
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Colección Moderna',
                'slug' => 'coleccion-moderna',
                'description' => 'Diseños contemporáneos que fusionan tendencias actuales con elegancia nupcial.',
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Colección Bohemia',
                'slug' => 'coleccion-bohemia',
                'description' => 'Vestidos románticos con encajes florales, siluetas fluidas y detalles artesanales.',
                'status' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => 'Alta Costura',
                'slug' => 'alta-costura',
                'description' => 'Piezas únicas confeccionadas a medida con los tejidos más exclusivos.',
                'status' => 1,
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
