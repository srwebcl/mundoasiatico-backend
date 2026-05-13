<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Enviar a Brevo al registrarse
        $apiKey = config('services.brevo.key');
        
        if (empty($apiKey)) {
            return;
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://api.brevo.com/v3/contacts', [
                'email' => $user->email,
                'attributes' => [
                    'NOMBRE' => $user->name,
                    'TELEFONO' => $user->phone ?? '',
                ],
                'listIds' => [2], // Lista por defecto (generalmente la ID 2 es la primera que se crea, o ID 1)
                'updateEnabled' => true,
            ]);

            if (!$response->successful()) {
                Log::warning('No se pudo añadir contacto a Brevo. HTTP: ' . $response->status() . ' - ' . $response->body());
            } else {
                Log::info("Contacto sincronizado con Brevo: {$user->email}");
            }
        } catch (\Exception $e) {
            Log::error('Excepción al conectar con Brevo Contact API: ' . $e->getMessage());
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Opcional: remover de Brevo
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
