<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ambiente de Transbank
    |--------------------------------------------------------------------------
    | 'integration' para pruebas, 'production' para producción real.
    | Se configura desde el .env con TRANSBANK_ENV.
    */
    'env' => env('TRANSBANK_ENV', 'integration'),

    /*
    |--------------------------------------------------------------------------
    | Credenciales de Webpay Plus
    |--------------------------------------------------------------------------
    | Las credenciales de integración son públicas (para pruebas).
    | Las de producción las entrega Transbank al activar el comercio.
    */
    'commerce_code' => env('TRANSBANK_COMMERCE_CODE', '597055555532'),
    'api_key'       => env('TRANSBANK_API_KEY', '579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C'),
];
