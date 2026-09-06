<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name', 'value' => 'HiloBlanco', 'type' => 'string', 'label' => 'Nombre del sitio', 'group' => 'general', 'is_public' => 1],
            ['key' => 'site_tagline', 'value' => 'Vestidos de novia únicos', 'type' => 'string', 'label' => 'Eslogan', 'group' => 'general', 'is_public' => 1],
            ['key' => 'currency', 'value' => 'COP', 'type' => 'string', 'label' => 'Moneda', 'group' => 'general', 'is_public' => 1],

            // Contacto
            ['key' => 'whatsapp_number', 'value' => '+573001234567', 'type' => 'string', 'label' => 'Número de WhatsApp', 'group' => 'contact', 'is_public' => 1],
            ['key' => 'email_contact', 'value' => 'info@hiloblanco.com', 'type' => 'string', 'label' => 'Email de contacto', 'group' => 'contact', 'is_public' => 1],
            ['key' => 'address', 'value' => 'Medellín, Colombia', 'type' => 'string', 'label' => 'Dirección', 'group' => 'contact', 'is_public' => 1],

            // Redes sociales
            ['key' => 'instagram_handle', 'value' => '@hiloblanco', 'type' => 'string', 'label' => 'Instagram', 'group' => 'social', 'is_public' => 1],

            // Catálogo
            ['key' => 'show_prices', 'value' => '0', 'type' => 'boolean', 'label' => 'Mostrar precios al público', 'group' => 'catalog', 'is_public' => 1],

            // Funcionalidades
            ['key' => 'appointment_enabled', 'value' => '0', 'type' => 'boolean', 'label' => 'Módulo de citas activo', 'group' => 'features', 'is_public' => 0],

            // SEO
            ['key' => 'meta_description', 'value' => 'HiloBlanco — Boutique de vestidos de novia únicos en Medellín. Diseños exclusivos, confección a medida.', 'type' => 'string', 'label' => 'Meta descripción', 'group' => 'seo', 'is_public' => 0],

            // Imagen header hero
            ['key' => 'header_image', 'value' => '', 'type' => 'string', 'label' => 'Imagen del hero (URL)', 'group' => 'general', 'is_public' => 1],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
