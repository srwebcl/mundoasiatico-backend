<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class BrevoBatchService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.brevo.com/v3/smtp/email';

    public function __construct()
    {
        $this->apiKey = config('services.brevo.key');
    }

    /**
     * Enviar correos transaccionales en lote (Batch).
     * Ideal para enviar un mismo mensaje o plantilla a múltiples destinatarios
     * con variables personalizadas, ahorrando recursos y llamadas a la API.
     * 
     * @param array $recipients Array de destinatarios. Ej: [['email' => 'a@a.com', 'name' => 'A', 'params' => ['code' => 123]]]
     * @param array $sender Array del remitente. Ej: ['email' => 'admin@mundoasiatico.cl', 'name' => 'Mundo Asiático']
     * @param string $subject Asunto del correo (opcional si se usa templateId con subject propio)
     * @param string|null $htmlContent Contenido HTML (null si se usa templateId)
     * @param int|null $templateId ID de la plantilla en Brevo
     * @return bool
     */
    public function sendBatch(array $recipients, array $sender, string $subject, ?string $htmlContent = null, ?int $templateId = null): bool
    {
        if (empty($this->apiKey)) {
            Log::error('BrevoBatchService: No se encontró BREVO_API_KEY en el entorno.');
            return false;
        }

        // Construir la estructura messageVersions para Batch Send según la documentación de Brevo
        $messageVersions = [];

        foreach ($recipients as $recipient) {
            $version = [
                'to' => [
                    [
                        'email' => $recipient['email'],
                        'name' => $recipient['name'] ?? '',
                    ]
                ]
            ];

            // Si hay parámetros dinámicos para este destinatario (ej. nombre, token de descuento)
            if (isset($recipient['params']) && is_array($recipient['params'])) {
                $version['params'] = $recipient['params'];
            }

            $messageVersions[] = $version;
        }

        $payload = [
            'sender' => $sender,
            'messageVersions' => $messageVersions,
        ];

        if ($subject) {
            $payload['subject'] = $subject;
        }

        if ($templateId) {
            $payload['templateId'] = $templateId;
        } elseif ($htmlContent) {
            $payload['htmlContent'] = $htmlContent;
        } else {
            throw new Exception("Debes proveer un templateId o htmlContent para enviar correos.");
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                Log::info('BrevoBatchService: Correos en lote enviados exitosamente.', ['response' => $response->json()]);
                return true;
            }

            Log::error('BrevoBatchService: Error al enviar correos en lote.', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('BrevoBatchService Excepción: ' . $e->getMessage());
            return false;
        }
    }
}
