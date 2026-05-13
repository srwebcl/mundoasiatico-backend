<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS — Cross-Origin Resource Sharing
    |--------------------------------------------------------------------------
    | Permite que el frontend en Vercel (Next.js) se comunique con esta API.
    | En producción, reemplaza los dominios por los reales.
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'https://mundoasiatico.cl'),
        'https://mundoasiatico.cl',                  // Sin www
        'https://www.mundoasiatico.cl',              // Con www (Vercel)
        'https://mundoasiatico.vercel.app',          // Preview de Vercel
        'http://localhost:3000',                      // Desarrollo local Next.js
        'http://localhost:3001',
    ],

    'allowed_origins_patterns' => [
        // Permite todos los preview URLs de Vercel (mundoasiatico-*.vercel.app)
        '#^https://mundoasiatico-[a-z0-9-]+\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
