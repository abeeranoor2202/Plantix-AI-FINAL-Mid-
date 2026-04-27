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
        // N, P, K, temperature, humidity, rainfall must be integers (Flask validation rule).
        // ph is the only field that may be decimal.
        $payload = [
            'nitrogen'    => (int) $input['nitrogen'],
            'phosphorus'  => (int) $input['phosphorus'],
            'potassium'   => (int) $input['potassium'],
            'temperature' => (int) $input['temperature'],
            'humidity'    => (int) $input['humidity'],
            'ph'          => (float) $input['ph_level'],
            'rainfall'    => (int) $input['rainfall_mm'],
        ];

        $response = $this->request('post', '/predict', ['json' => $payload]);

        // Support both response shapes:
        //
        // New shape (Flask code updated):
        //   {"status":"success","data":{"crop":...,"confidence":...}}
        //
        // Old/legacy shape (Flask not yet restarted with new code):
        //   {"success":true,"crop":...,"confidence":...,"request_id":...}
        $status = Arr::get($response, 'status');

        if ($status === 'low_confidence') {
            throw new \RuntimeException(
                Arr::get($response, 'message', 'Unable to confidently recommend a crop.')
            );
        }

        if ($status === 'invalid') {
            throw new \RuntimeException(
                Arr::get($response, 'message', 'Invalid input provided to crop prediction API.')
            );
        }

        // New shape: data is nested under 'data' key
        if ($status === 'success' && isset($response['data'])) {
            $data = $response['data'];
            return [
                'crop'       => (string) Arr::get($data, 'crop', ''),
                'confidence' => round((float) Arr::get($data, 'confidence', 0), 4),
                'request_id' => Arr::get($data, 'request_id'),
                'record_id'  => Arr::get($data, 'record_id'),
                'timestamp'  => Arr::get($data, 'timestamp'),
                'raw'        => $response,
            ];
        }

        // Legacy/flat shape: crop and confidence at root level
        $crop = (string) Arr::get($response, 'crop', Arr::get($response, 'prediction', ''));
        $confidence = round((float) Arr::get($response, 'confidence', 0), 4);

        if ($crop === '') {
            throw new \RuntimeException('Crop prediction API returned an empty crop name.');
        }

        return [
            'crop'       => $crop,
            'confidence' => $confidence,
            'request_id' => Arr::get($response, 'request_id'),
            'record_id'  => Arr::get($response, 'record_id'),
            'timestamp'  => Arr::get($response, 'timestamp'),
            'raw'        => $response,
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
