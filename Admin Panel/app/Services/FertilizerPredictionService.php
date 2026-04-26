<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FertilizerPredictionService
{
    public function predict(array $input): array
    {
        $payload = [
            'nitrogen'    => (int) $input['nitrogen'],
            'potassium'   => (int) $input['potassium'],
            'phosphorus'  => (int) ($input['phosphorus'] ?? $input['phosphorous']),
        ];

        $response = $this->request('post', '/fertilizer/predict', ['json' => $payload]);

        return [
            'fertilizer' => (string) Arr::get($response, 'fertilizer', Arr::get($response, 'prediction', '')),
            'confidence' => Arr::get($response, 'confidence') !== null ? round((float) Arr::get($response, 'confidence'), 4) : null,
            'request_id' => Arr::get($response, 'request_id'),
            'timestamp' => Arr::get($response, 'timestamp'),
            'model_name' => Arr::get($response, 'model_name'),
            'model_version' => Arr::get($response, 'model_version'),
            'raw' => $response,
        ];
    }

    private function request(string $method, string $path, array $options = []): array
    {
        $baseUrl = rtrim((string) config('services.fertilizer_prediction_api.base_url'), '/');
        $apiKey = (string) config('services.fertilizer_prediction_api.api_key');
        $timeoutSeconds = (int) config('services.fertilizer_prediction_api.timeout', 8);

        if ($baseUrl === '' || $apiKey === '') {
            throw new \RuntimeException('Fertilizer prediction API is not configured.');
        }

        $url = $baseUrl . '/' . ltrim($path, '/');

        try {
            $client = Http::acceptJson()
                ->asJson()
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'X-Request-Id' => (string) str()->uuid(),
                ])
                ->timeout($timeoutSeconds)
                ->retry(1, 200);

            $response = match (strtolower($method)) {
                'get' => $client->get($url, $options['query'] ?? []),
                'post' => $client->post($url, $options['json'] ?? []),
                default => throw new \InvalidArgumentException('Unsupported HTTP method for FertilizerPredictionService.'),
            };

            if (!$response->successful()) {
                Log::warning('Fertilizer prediction API non-success response.', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);
                $response->throw();
            }

            return $response->json() ?? [];
        } catch (RequestException $e) {
            Log::error('Fertilizer prediction API request failed.', [
                'url' => $url,
                'status' => optional($e->response)->status(),
                'message' => $e->getMessage(),
                'response' => optional($e->response)->json(),
            ]);
            throw new \RuntimeException('Fertilizer prediction API request failed.', previous: $e);
        } catch (\Throwable $e) {
            Log::error('Fertilizer prediction integration error.', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Fertilizer prediction integration error.', previous: $e);
        }
    }
}
