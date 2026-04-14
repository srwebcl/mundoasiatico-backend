<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Siembra los 7 productos reales del CSV "listado de productos - Hoja 1.csv"
 * con todas sus categorías, marcas de vehículos y modelos compatibles.
 */
class ProductionSeeder extends Seeder
{
    // ──────────────────────────────────────────────────────────────────────────
    // DATOS DEL CSV (parseados y organizados)
    // ──────────────────────────────────────────────────────────────────────────

    private array $categorias = [
        'encendido' => ['name' => 'Encendido',  'icon' => '⚡'],
        'filtros'   => ['name' => 'Filtros',    'icon' => '🔩'],
        'inyeccion' => ['name' => 'Inyección',  'icon' => '💉'],
        'frenos'    => ['name' => 'Frenos',     'icon' => '🛑'],
        'sensores'  => ['name' => 'Sensores',   'icon' => '📡'],
    ];

    // Marcas de VEHÍCULOS (Marca Compatible en CSV)
    private array $marcasVehiculo = [
        'Chery', 'Great Wall', 'MG', 'Geely', 'Changan', 'Jac',
        'Foton', 'Baic', 'DFM', 'Hafei', 'Lifan', 'Zotye',
        'Chevrolet', 'BYD', 'Gac Gonow', 'DFSK', 'Maxus',
    ];

    // Marca de REPUESTO (Marca del Repuesto en CSV) — solo "Torch" tiene repuesto
    private array $marcasRepuesto = ['Torch'];

    // ──────────────────────────────────────────────────────────────────────────
    // Productos con sus modelos de auto compatibles
    // ──────────────────────────────────────────────────────────────────────────
    private array $productos = [
        [
            'sku'             => '111233707100',
            'name'            => 'Bujías Juego Iridium',
            'description'     => 'Bujías punta Iridium de alta performance. Mayor durabilidad y eficiencia de combustión.',
            'categoria'       => 'encendido',
            'marca_repuesto'  => 'Torch',
            'regular_price'   => 40000,
            'wholesale_price' => 32000,
            'stock'           => 6,
            'garantia'        => '3 meses',
            'origen'          => 'Certificado',
            'modelos'         => [
                ['marca' => 'Chery',      'modelo' => 'Tiggo 2',     'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',      'modelo' => 'IQ',          'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',      'modelo' => 'Grand Tiggo', 'cilindrada' => '1.6', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',      'modelo' => 'Arrizo 3',    'cilindrada' => '1.5', 'year_start' => 2016, 'year_end' => 2017],
                ['marca' => 'Great Wall', 'modelo' => 'Haval H3',    'cilindrada' => '2.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Great Wall', 'modelo' => 'Haval H5',    'cilindrada' => '2.4', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',      'modelo' => 'Beat',        'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',      'modelo' => 'Fulwin',      'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',      'modelo' => 'Destiny',     'cilindrada' => '2.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',      'modelo' => 'Skin',        'cilindrada' => '1.6', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Great Wall', 'modelo' => 'Wingle 6',    'cilindrada' => '2.4', 'year_start' => null, 'year_end' => null],
                ['marca' => 'MG',         'modelo' => 'ZS',          'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'MG',         'modelo' => 'ZX',          'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'MG',         'modelo' => '350',         'cilindrada' => '1.5', 'year_start' => 2013, 'year_end' => 2018],
                ['marca' => 'Great Wall', 'modelo' => 'Haval H6',    'cilindrada' => '2.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Geely',      'modelo' => 'GS',          'cilindrada' => '1.8', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',      'modelo' => 'Tiggo 2 Pro', 'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
            ],
        ],
        [
            'sku'             => '111301109111',
            'name'            => 'Filtro de Aire',
            'description'     => 'Filtro aire 2.0 Tiggo 250x225x46. Compatible con motores 1.6 y 2.0.',
            'categoria'       => 'filtros',
            'marca_repuesto'  => null,
            'regular_price'   => 100000,
            'wholesale_price' => 80000,
            'stock'           => 10,
            'garantia'        => '3 meses',
            'origen'          => 'Certificado',
            'modelos'         => [
                ['marca' => 'Chery', 'modelo' => 'Tiggo',     'cilindrada' => '1.6/2.0', 'year_start' => 2008, 'year_end' => 2013],
                ['marca' => 'Chery', 'modelo' => 'New Tiggo', 'cilindrada' => '1.6', 'year_start' => 2008, 'year_end' => 2013],
            ],
        ],
        [
            'sku'             => '191181117010',
            'name'            => 'Filtro de Bencina',
            'description'     => 'Filtro de combustible bencina. Alta filtración para motores de inyección electrónica.',
            'categoria'       => 'filtros',
            'marca_repuesto'  => null,
            'regular_price'   => 100000,
            'wholesale_price' => 80000,
            'stock'           => 10,
            'garantia'        => '3 meses',
            'origen'          => 'Certificado',
            'modelos'         => [
                ['marca' => 'Changan', 'modelo' => 'CS35 Plus',  'cilindrada' => '1.6', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'CS55',       'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'CS55 Plus',  'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'MD201',      'cilindrada' => '1.2', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'MS201',      'cilindrada' => '1.2', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'Tiggo 3',    'cilindrada' => '1.6', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'Tiggo 4',    'cilindrada' => '2.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Jac',     'modelo' => 'S2',         'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Foton',   'modelo' => 'Midi',       'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Baic',    'modelo' => 'Plus',       'cilindrada' => '1.2', 'year_start' => null, 'year_end' => null],
            ],
        ],
        [
            'sku'             => '291101135011',
            'name'            => 'Sensor IAC',
            'description'     => 'Sensor IAC (Idle Air Control). Controla el ralentí del motor en condición de reposo.',
            'categoria'       => 'inyeccion',
            'marca_repuesto'  => null,
            'regular_price'   => 100000,
            'wholesale_price' => 80000,
            'stock'           => 5,
            'garantia'        => '3 meses',
            'origen'          => 'Certificado',
            'modelos'         => [
                ['marca' => 'DFM',       'modelo' => 'Cargo Van',            'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Geely',     'modelo' => 'MK',                   'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan',   'modelo' => 'New S100',             'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan',   'modelo' => 'S200',                 'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan',   'modelo' => 'S100',                 'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan',   'modelo' => 'S300 Old',             'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Hafei',     'modelo' => 'Ruiyi',                'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Hafei',     'modelo' => 'Zhongi',               'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Jac',       'modelo' => 'J3 Sport VVT',         'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Lifan',     'modelo' => '320',                  'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Zotye',     'modelo' => 'Hunter',               'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chevrolet', 'modelo' => 'N300 Max',             'cilindrada' => '1.2', 'year_start' => 2011, 'year_end' => 2013],
                ['marca' => 'BYD',       'modelo' => 'F0',                   'cilindrada' => '1.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Geely',     'modelo' => 'LC',                   'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Jac',       'modelo' => 'J2',                   'cilindrada' => '1.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Geely',     'modelo' => 'New CK',               'cilindrada' => '1.5', 'year_start' => 2010, 'year_end' => 2012],
                ['marca' => 'BYD',       'modelo' => 'G3',                   'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Geely',     'modelo' => 'MK Sedan',             'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan',   'modelo' => 'CV1',                  'cilindrada' => '1.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'DFSK',      'modelo' => 'Cargo Truck Serie V',  'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan',   'modelo' => 'MD201',                'cilindrada' => '1.2', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan',   'modelo' => 'MS201',                'cilindrada' => '1.2', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Geely',     'modelo' => 'CK Old',               'cilindrada' => '1.3', 'year_start' => 2008, 'year_end' => 2010],
                ['marca' => 'Foton',     'modelo' => 'New Midi Truck',       'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'DFSK',      'modelo' => 'Cargo Van 1.0',        'cilindrada' => '1.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'DFSK',      'modelo' => 'Cargo Van 1.1',        'cilindrada' => '1.1', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chevrolet', 'modelo' => 'New N300 Max',         'cilindrada' => '1.2', 'year_start' => 2014, 'year_end' => null],
                ['marca' => 'Changan',   'modelo' => 'M201',                 'cilindrada' => '1.2', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan',   'modelo' => 'CS1',                  'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',     'modelo' => 'IQ 1.1',               'cilindrada' => '1.1', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Foton',     'modelo' => 'Midi Cargo',           'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Foton',     'modelo' => 'TM3',                  'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
            ],
        ],
        [
            'sku'             => '111173501080',
            'name'            => 'Pastillas de Freno Delantera',
            'description'     => 'Pastillas de freno delantera certificadas. Alto rendimiento en frenado y baja generación de polvo.',
            'categoria'       => 'frenos',
            'marca_repuesto'  => null,
            'regular_price'   => 100000,
            'wholesale_price' => 80000,
            'stock'           => 3,
            'garantia'        => '3 meses',
            'origen'          => 'Certificado',
            'modelos'         => [
                ['marca' => 'Chery',   'modelo' => 'Fulwin',              'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'Skin',                'cilindrada' => '1.6', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'Tiggo',               'cilindrada' => '1.6', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'Tiggo 3',             'cilindrada' => null,  'year_start' => null, 'year_end' => 2019],
                ['marca' => 'Chery',   'modelo' => 'Tiggo 2',             'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'Tiggo 2 Pro',         'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'Tiggo 4',             'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'Tiggo 7',             'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'Arrizo 3',            'cilindrada' => null,  'year_start' => 2018, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'Arrizo 5',            'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Lifan',   'modelo' => 'X60',                 'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'New Tiggo 3',         'cilindrada' => null,  'year_start' => 2020, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'Alsvin',              'cilindrada' => '1.4', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',   'modelo' => 'K60',                 'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Geely',   'modelo' => 'EC7',                 'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Baic',    'modelo' => 'X35',                 'cilindrada' => null,  'year_start' => null, 'year_end' => null],
            ],
        ],
        [
            'sku'             => '111101205110',
            'name'            => 'Sensor Oxígeno',
            'description'     => 'Sensor de oxígeno (sonda lambda). Mide el contenido de O2 en los gases de escape para optimizar la mezcla aire-combustible.',
            'categoria'       => 'sensores',
            'marca_repuesto'  => null,
            'regular_price'   => 100000,
            'wholesale_price' => 80000,
            'stock'           => 3,
            'garantia'        => '3 meses',
            'origen'          => 'Certificado',
            'modelos'         => [
                ['marca' => 'DFM',     'modelo' => 'Cargo Van 1.3',  'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'New S100',       'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'New S200',       'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'S100',           'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'S200',           'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'S300 Old',       'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'New S300',       'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Changan', 'modelo' => 'New CK',         'cilindrada' => '1.5', 'year_start' => 2010, 'year_end' => 2012],
                ['marca' => 'Foton',   'modelo' => 'Midi Truck',     'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
                ['marca' => 'DFM',     'modelo' => 'Cargo Van 1.0',  'cilindrada' => '1.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Foton',   'modelo' => 'Midi Cargo',     'cilindrada' => '1.3', 'year_start' => null, 'year_end' => null],
            ],
        ],
        [
            'sku'             => '111171012010',
            'name'            => 'Filtro de Aceite',
            'description'     => 'Filtro de aceite de motor. Elimina partículas e impurezas para proteger el motor y prolongar su vida útil.',
            'categoria'       => 'filtros',
            'marca_repuesto'  => null,
            'regular_price'   => 100000,
            'wholesale_price' => 80000,
            'stock'           => 5,
            'garantia'        => '3 meses',
            'origen'          => 'Certificado',
            'modelos'         => [
                ['marca' => 'Chery',  'modelo' => 'Tiggo 2',      'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'Fulwin',       'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'Fulwin 2',     'cilindrada' => '1.5', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'Beat',         'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'Face',         'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'S21',          'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'Arrizo 3',     'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'Tiggo 2 Pro',  'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'New Tiggo 3',  'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'Tiggo 7 Pro',  'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'K60',          'cilindrada' => null,  'year_start' => null, 'year_end' => null],
                ['marca' => 'Chery',  'modelo' => 'Arrizo 5',     'cilindrada' => null,  'year_start' => 2019, 'year_end' => null],
                ['marca' => 'Maxus',  'modelo' => 'T60',          'cilindrada' => '2.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Maxus',  'modelo' => 'T90',          'cilindrada' => '2.0', 'year_start' => null, 'year_end' => null],
                ['marca' => 'Maxus',  'modelo' => 'V90',          'cilindrada' => null,  'year_start' => null, 'year_end' => null],
            ],
        ],
    ];

    // ──────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $now = now();

        $this->command->info('🌱 Iniciando siembra de datos de producción...');

        // 1. Categorías
        $this->command->info('  → Creando categorías...');
        $categoryIds = [];
        foreach ($this->categorias as $slug => $data) {
            $categoryIds[$slug] = DB::table('categories')->insertGetId([
                'name'       => $data['name'],
                'slug'       => $slug,
                'icon'       => $data['icon'],
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. Marcas de Vehículos
        $this->command->info('  → Creando marcas de vehículos...');
        $brandIds = [];
        foreach ($this->marcasVehiculo as $marca) {
            $brandIds[$marca] = DB::table('brands')->insertGetId([
                'name'       => $marca,
                'slug'       => Str::slug($marca),
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Marcas de repuestos (Torch, etc.) — se insertan también en brands
        foreach ($this->marcasRepuesto as $marca) {
            if (!isset($brandIds[$marca])) {
                $brandIds[$marca] = DB::table('brands')->insertGetId([
                    'name'       => $marca,
                    'slug'       => Str::slug($marca),
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 3. Modelos de autos (únicos por marca+modelo)
        $this->command->info('  → Creando modelos de autos...');
        $carModelIds = [];
        foreach ($this->productos as $producto) {
            foreach ($producto['modelos'] as $m) {
                $key = Str::slug($m['marca'] . ' ' . $m['modelo']);
                if (isset($carModelIds[$key])) continue;

                $brandId = $brandIds[$m['marca']] ?? null;
                if (!$brandId) {
                    // Insertar marca nueva si aparece solo en modelos
                    $brandId = DB::table('brands')->insertGetId([
                        'name'       => $m['marca'],
                        'slug'       => Str::slug($m['marca']),
                        'is_active'  => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $brandIds[$m['marca']] = $brandId;
                }

                $carModelIds[$key] = DB::table('car_models')->insertGetId([
                    'brand_id'   => $brandId,
                    'name'       => $m['modelo'],
                    'slug'       => $key,
                    'year_start' => $m['year_start'],
                    'year_end'   => $m['year_end'],
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 4. Productos y tabla pivote
        $this->command->info('  → Creando productos y compatibilidades...');
        foreach ($this->productos as $i => $p) {
            $categoryId = $categoryIds[$p['categoria']] ?? null;
            $brandId    = $p['marca_repuesto'] ? ($brandIds[$p['marca_repuesto']] ?? null) : null;

            $productId = DB::table('products')->insertGetId([
                'sku'             => $p['sku'],
                'name'            => $p['name'],
                'slug'            => Str::slug($p['name']) . '-' . Str::lower(substr($p['sku'], -4)),
                'description'     => $p['description'],
                'regular_price'   => $p['regular_price'],
                'wholesale_price' => $p['wholesale_price'],
                'stock'           => $p['stock'],
                'is_active'       => true,
                'is_featured'     => $i < 3, // Los primeros 3 son destacados
                'category_id'     => $categoryId,
                'brand_id'        => $brandId,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            // Insertar pivote: producto ↔ modelos de autos
            foreach ($p['modelos'] as $m) {
                $key        = Str::slug($m['marca'] . ' ' . $m['modelo']);
                $carModelId = $carModelIds[$key] ?? null;
                if ($carModelId) {
                    DB::table('car_model_product')->insert([
                        'product_id'   => $productId,
                        'car_model_id' => $carModelId,
                    ]);
                }
            }

            $this->command->line("    ✅ #{$productId} {$p['name']} ({$p['sku']})");
        }

        $this->command->info('');
        $this->command->info('✅ Siembra completada:');
        $this->command->info('   ' . count($this->categorias) . ' categorías');
        $this->command->info('   ' . count($brandIds) . ' marcas');
        $this->command->info('   ' . count($carModelIds) . ' modelos de autos');
        $this->command->info('   ' . count($this->productos) . ' productos');
    }
}
