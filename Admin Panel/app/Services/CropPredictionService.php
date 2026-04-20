<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CropPredictionService
{
    public function predict(array $input): array
    {
        $payload = [
            'nitrogen' => (float) $input['nitrogen'],
            'phosphorus' => (float) $input['phosphorus'],
            'potassium' => (float) $input['potassium'],
            'temperature' => (float) $input['temperature'],
            'humidity' => (float) $input['humidity'],
            'ph' => (float) $input['ph_level'],
            'rainfall' => (float) $input['rainfall_mm'],
        ];

        $response = $this->request('post', '/predict', ['json' => $payload]);

        return [
            'crop' => (string) Arr::get($response, 'crop', Arr::get($response, 'prediction', '')),
            'confidence' => round((float) Arr::get($response, 'confidence', 0), 4),
            'request_id' => Arr::get($response, 'request_id'),
            'record_id' => Arr::get($response, 'record_id'),
            'timestamp' => Arr::get($response, 'timestamp'),
            'raw' => $response,
        ];
    }

    public function health(): array
    {
        return $this->request('get', '/health');
    }

    public function modelInfo(): array
    {
        return $this->request('get', '/model-info');
    }

    public function predictionLogs(int $limit = 20, int $offset = 0): array
    {
        return $this->request('get', '/admin/predictions', [
            'query' => [
                'limit' => max(1, min(200, $limit)),
                'offset' => max(0, $offset),
            ],
        ]);
    }

    public function stats(): array
    {
        return $this->request('get', '/admin/stats');
    }

    private function request(string $method, string $path, array $options = []): array
    {
        $baseUrl = rtrim((string) config('services.crop_prediction_api.base_url'), '/');
        $apiKey = (string) config('services.crop_prediction_api.api_key');
        $timeoutSeconds = (int) config('services.crop_prediction_api.timeout', 8);

        if ($baseUrl === '' || $apiKey === '') {
            throw new \RuntimeException('Crop prediction API is not configured.');
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
                default => throw new \InvalidArgumentException('Unsupported HTTP method for CropPredictionService.'),
            };

            if (!$response->successful()) {
                Log::warning('Crop prediction API non-success response.', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);
                $response->throw();
            }

            return $response->json() ?? [];
        } catch (RequestException $e) {
            Log::error('Crop prediction API request failed.', [
                'url' => $url,
                'status' => optional($e->response)->status(),
                'message' => $e->getMessage(),
                'response' => optional($e->response)->json(),
            ]);
            throw new \RuntimeException('Crop prediction API request failed.', previous: $e);
        } catch (\Throwable $e) {
            Log::error('Crop prediction integration error.', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Crop prediction integration error.', previous: $e);
        }
    }
}
