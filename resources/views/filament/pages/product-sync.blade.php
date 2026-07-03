<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Tarjeta 1: Sincronización de Datos --}}
        <x-filament::card>
            <div class="prose dark:prose-invert">
                <h2 class="text-xl font-bold mb-2">Paso 1: Sincronizar Datos (Textos)</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Actualiza nombres, precios, descripciones y stock directamente leyendo tu hoja pública de Google Sheets en tiempo real. 
                    Usa el botón <strong>"Sincronizar desde Excel/CSV"</strong> que se encuentra arriba a la derecha.
                </p>
                
                <p class="text-sm text-gray-500 mb-4">
                    <strong>Columnas requeridas:</strong> SKU, Nombre, Precio, Precio Mayorista, Stock, Categoria, Marca, Descripcion.
                </p>
            </div>
        </x-filament::card>

        {{-- Tarjeta 2: Imágenes (WebP) --}}
        <x-filament::card>
            <div class="prose dark:prose-invert">
                <h2 class="text-xl font-bold mb-2">Paso 2: Subida y Optimización de Imágenes</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Arrastra aquí todas las fotos de los productos nuevos o actualizados. 
                    El sistema las convertirá automáticamente a formato <strong>WebP</strong> (súper ligero para SEO) y las asignará según el nombre del archivo (SKU).
                </p>
            </div>
            
            <form wire:submit="processImages" class="mt-4">
                {{ $this->form }}
                
                <div class="mt-4 flex justify-end">
                    <x-filament::button type="submit" color="primary" icon="heroicon-o-photo">
                        Procesar y Asignar Imágenes
                    </x-filament::button>
                </div>
            </form>
        </x-filament::card>
    </div>
</x-filament-panels::page>
