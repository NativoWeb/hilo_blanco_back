<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        $products = [
            // Clásica
            [
                'category_id' => $categories['coleccion-clasica'],
                'sku' => 'HB-CL-001',
                'name' => 'Aurora Clásica',
                'slug' => 'aurora-clasica',
                'description' => 'Vestido corte A en satén marfil con escote corazón y cola catedral. La elegancia atemporal en su máxima expresión.',
                'details' => [
                    'tallas' => ['XS', 'S', 'M', 'L', 'XL', 'personalizado'],
                    'telas' => ['satén', 'encaje'],
                    'colores' => ['blanco', 'marfil'],
                    'tiempo_confeccion' => '4 meses',
                ],
                'is_featured' => 1,
                'is_customizable' => 1,
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'category_id' => $categories['coleccion-clasica'],
                'sku' => 'HB-CL-002',
                'name' => 'Serenata Marfil',
                'slug' => 'serenata-marfil',
                'description' => 'Silueta sirena en mikado con manga larga de encaje y espalda descubierta. Refinada y sofisticada.',
                'details' => [
                    'tallas' => ['S', 'M', 'L', 'XL', 'personalizado'],
                    'telas' => ['mikado', 'encaje'],
                    'colores' => ['marfil', 'champagne'],
                    'tiempo_confeccion' => '5 meses',
                ],
                'is_featured' => 0,
                'is_customizable' => 1,
                'status' => 1,
                'sort_order' => 2,
            ],
            // Moderna
            [
                'category_id' => $categories['coleccion-moderna'],
                'sku' => 'HB-MO-001',
                'name' => 'Lumière',
                'slug' => 'lumiere',
                'description' => 'Vestido minimalista en crepé con escote halter y abertura lateral. Moderno y audaz.',
                'details' => [
                    'tallas' => ['XS', 'S', 'M', 'L', 'personalizado'],
                    'telas' => ['crepé'],
                    'colores' => ['blanco', 'nude'],
                    'tiempo_confeccion' => '3 meses',
                ],
                'is_featured' => 1,
                'is_customizable' => 0,
                'status' => 1,
                'sort_order' => 1,
            ],
            // Bohemia
            [
                'category_id' => $categories['coleccion-bohemia'],
                'sku' => 'HB-BO-001',
                'name' => 'Violeta Bohemia',
                'slug' => 'violeta-bohemia',
                'description' => 'Vestido fluido en chiffon con bordados florales a mano y cinturón de flores secas. Romántico y artístico.',
                'details' => [
                    'tallas' => ['XS', 'S', 'M', 'L', 'XL', 'personalizado'],
                    'telas' => ['chiffon', 'encaje'],
                    'colores' => ['blanco', 'marfil', 'champagne'],
                    'tiempo_confeccion' => '6 meses',
                ],
                'is_featured' => 1,
                'is_customizable' => 1,
                'status' => 1,
                'sort_order' => 1,
            ],
            // Alta Costura
            [
                'category_id' => $categories['alta-costura'],
                'sku' => 'HB-AC-001',
                'name' => 'Diamante',
                'slug' => 'diamante',
                'description' => 'Obra maestra nupcial en tul bordado a mano con cristales Swarovski. Pieza única, confeccionada exclusivamente para ti.',
                'details' => [
                    'tallas' => ['personalizado'],
                    'telas' => ['tul', 'encaje de Bruselas', 'satén duquesa'],
                    'colores' => ['blanco puro', 'marfil'],
                    'tiempo_confeccion' => '8 meses',
                    'notas' => 'Requiere mínimo 3 pruebas presenciales',
                ],
                'is_featured' => 1,
                'is_customizable' => 1,
                'status' => 1,
                'sort_order' => 1,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['sku' => $product['sku']], $product);
        }
    }
}
