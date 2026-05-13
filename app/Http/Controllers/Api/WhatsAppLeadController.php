<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppLeadController extends Controller
{
    /**
     * Recibe los datos del modal de WhatsApp y los envía a Brevo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'patente' => 'nullable|string|max:20',
            'message' => 'nullable|string',
        ]);

        // Guardar el Lead en la base de datos local
        $lead = \App\Models\Lead::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'patente' => $validated['patente'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => 'new',
        ]);

        $apiKey = config('services.brevo.key');

        if (empty($apiKey)) {
            Log::warning('Brevo API Key no configurada. No se pudo capturar el lead de WhatsApp.');
            return response()->json(['status' => 'success', 'note' => 'Local only']);
        }

        try {
            // Brevo exige un email O un identificador SMS (telefono). 
            // Como no pedimos email en WhatsApp, usamos el telefono como identificador principal.
            // Para asegurar la creación en Brevo, el teléfono debe tener código de país.
            $phoneFormatted = $this->formatPhone($validated['phone']);

            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://api.brevo.com/v3/contacts', [
                'email' => strtolower(str_replace(' ', '', $validated['patente'])) . '@patente.mundoasiatico.cl', // Fake email fallback
                'attributes' => [
                    'NOMBRE' => $validated['name'],
                    'TELEFONO' => $validated['phone'],
                    'SMS' => $phoneFormatted,
                    'PATENTE' => strtoupper($validated['patente']),
                ],
                'listIds' => [2], // Asumiendo que la lista 2 es la principal
                'updateEnabled' => true,
            ]);

            if (!$response->successful()) {
                Log::warning('Error de Brevo al guardar lead WhatsApp: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Excepción guardando lead WhatsApp en Brevo: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Intenta formatear el teléfono a formato internacional de Chile (+56)
     */
    private function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) == 8) {
            return '+569' . $phone; // Fijo o sin el 9
        }
        if (strlen($phone) == 9) {
            return '+56' . $phone; // Ya tiene el 9
        }
        if (strlen($phone) == 11 && str_starts_with($phone, '569')) {
            return '+' . $phone; // Ya tiene 569
        }

        // Si no cumple, retornamos con + por si acaso
        return '+' . $phone;
    }
}
