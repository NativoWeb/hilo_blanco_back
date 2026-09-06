<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(4, true);

        return [
            'category_id' => Category::factory(),
            'sku' => 'HB-'.strtoupper(Str::random(6)),
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.Str::random(4),
            'description' => $this->faker->paragraph(),
            'details' => [
                'tallas' => ['S', 'M', 'L', 'personalizado'],
                'telas' => ['satén', 'encaje'],
                'colores' => ['blanco', 'marfil'],
                'tiempo_confeccion' => '4 meses',
            ],
            'price' => $this->faker->optional(0.6)->randomFloat(2, 500000, 8000000),
            'is_featured' => $this->faker->boolean(20),
            'is_customizable' => $this->faker->boolean(50),
            'status' => 1,
            'sort_order' => $this->faker->numberBetween(0, 50),
        ];
    }
}
