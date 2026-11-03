<?php

namespace App\Services\PipePlanner;

use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin server-side client for OpenRouter's chat completions API
 * (https://openrouter.ai/docs/api-reference/chat-completion).
 * The API key is read from config only and never leaves this class.
 */
class OpenRouterClient
{
    public function __construct(protected PlannerSettings $settings) {}

    protected function http(): PendingRequest
    {
        $key = config('pipe_planner.openrouter.api_key');
        if (blank($key)) {
            throw new OpenRouterException('not_configured', 'OpenRouter API key is not configured.');
        }

        return Http::baseUrl(rtrim(config('pipe_planner.openrouter.base_url'), '/'))
            ->withToken($key)
            ->withHeaders([
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('pipe_planner.openrouter.app_title'),
            ])
            ->acceptJson()
            ->timeout(config('pipe_planner.openrouter.timeout'));
    }

    /**
     * Look up the configured model in OpenRouter's model list and verify it accepts images.
     *
     * @return array{id:string, structured_outputs:bool, response_format:bool}
     */
    public function resolveModel(): array
    {
        $model = $this->settings->get('model');
        if (blank($model)) {
            throw new OpenRouterException('not_configured', 'No OpenRouter model is configured.');
        }

        $models = Cache::remember('pipe_planner.openrouter.models', now()->addHours(6), function () {
            try {
                $response = $this->http()->get('/models');
            } catch (ConnectionException|TransferException $e) {
                throw $this->transportFailure($e);
            }

            return $response->successful() ? $response->json('data') : null;
        });

        if (! is_array($models)) {
            Cache::forget('pipe_planner.openrouter.models');
            throw new OpenRouterException('provider_unavailable', 'Could not load the OpenRouter model list.', true);
        }

        if (str_ends_with($model, ':batch')) {
            // Batch variants are only served by OpenRouter's asynchronous batch API.
            throw new OpenRouterException('model_unavailable', "Model [{$model}] is a batch-only variant; use the id without ':batch'.");
        }

        $info = collect($models)->firstWhere('id', $model);
        if (! $info) {
            throw new OpenRouterException('model_unavailable', "Model [{$model}] is not available on OpenRouter.");
        }
        if (! in_array('image', $info['architecture']['input_modalities'] ?? [], true)) {
            throw new OpenRouterException('model_not_vision', "Model [{$model}] does not accept image input.");
        }

        $params = $info['supported_parameters'] ?? [];

        return [
            'id' => $model,
            'structured_outputs' => in_array('structured_outputs', $params, true),
            'response_format' => in_array('response_format', $params, true),
            'temperature' => in_array('temperature', $params, true),
        ];
    }

    /**
     * Send one chat completion request with bounded retries.
     *
     * @return array{content:string, request_id:?string, model:?string, prompt_tokens:?int, completion_tokens:?int, cost:?float, attempts:int}
     */
    public function complete(array $model, array $messages, array $jsonSchema): array
    {
        $payload = [
            'model' => $model['id'],
            'messages' => $messages,
            'max_tokens' => $this->settings->get('max_tokens'),
            'usage' => ['include' => true],
        ];
        // Only send parameters the model supports: with require_parameters an unsupported
        // one leaves OpenRouter with no eligible provider at all.
        if ($model['temperature'] ?? false) {
            $payload['temperature'] = 0.1;
        }

        if ($model['structured_outputs']) {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => ['name' => 'pipe_plan', 'strict' => true, 'schema' => $jsonSchema],
            ];
            // Only route to providers that honour the response_format parameter.
            $payload['provider'] = ['require_parameters' => true];
        } elseif ($model['response_format']) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $maxAttempts = 1 + max(0, (int) config('pipe_planner.openrouter.retries'));
        $attempt = 0;

        while (true) {
            $attempt++;
            try {
                return $this->send($payload) + ['attempts' => $attempt];
            } catch (OpenRouterException $e) {
                Log::warning('pipe_planner.openrouter.failure', [
                    'category' => $e->category, 'attempt' => $attempt, 'model' => $model['id'],
                ]);
                if (! $e->retryable || $attempt >= $maxAttempts) {
                    throw $e;
                }
                usleep((int) (config('pipe_planner.openrouter.retry_backoff_ms', 1500) * 1000 * $attempt));
            }
        }
    }

    /**
     * Classify a failure that happened before any HTTP response arrived. cURL/Guzzle
     * messages contain only the URL and error text, so they are safe to log.
     */
    protected function transportFailure(\Throwable $e): OpenRouterException
    {
        $message = mb_substr($e->getMessage(), 0, 300);
        Log::warning('pipe_planner.openrouter.transport_error', ['error' => $message]);

        if (preg_match('/cURL error (35|51|58|59|60|77|83)\b|SSL certificate/i', $message)) {
            // Server CA bundle / TLS setup problem: retrying cannot help.
            return new OpenRouterException('tls_error', 'TLS certificate verification failed on this server.');
        }
        if ($e instanceof ConnectionException && preg_match('/cURL error 28\b|timed out/i', $message)) {
            return new OpenRouterException('timeout', 'The AI provider did not respond in time.', true);
        }

        return new OpenRouterException('network_error', 'Could not reach the AI provider.', true);
    }

    protected function send(array $payload): array
    {
        try {
            $response = $this->http()->post('/chat/completions', $payload);
        } catch (ConnectionException|TransferException $e) {
            throw $this->transportFailure($e);
        }

        $status = $response->status();
        if ($status === 429) {
            throw new OpenRouterException('rate_limited', 'The AI provider is rate limiting requests.', true);
        }
        if ($status === 402) {
            throw new OpenRouterException('insufficient_credits', 'The AI provider account has insufficient credits.');
        }
        if (in_array($status, [401, 403], true)) {
            throw new OpenRouterException('auth_failed', 'The AI provider rejected the credentials.');
        }
        if ($status >= 500 || in_array($status, [408, 502, 503], true)) {
            throw new OpenRouterException('provider_error', "The AI provider returned HTTP {$status}.", true);
        }
        if ($status >= 400) {
            // Provider error text explains routing/parameter problems; it never contains our key.
            Log::warning('pipe_planner.openrouter.rejected', [
                'status' => $status, 'error' => mb_substr((string) $response->json('error.message', ''), 0, 500),
            ]);
            throw new OpenRouterException('bad_request', "The AI provider rejected the request (HTTP {$status}).");
        }

        $body = $response->json();
        if (! is_array($body)) {
            throw new OpenRouterException('malformed_response', 'The AI provider returned a non-JSON body.', true);
        }
        if (isset($body['error'])) {
            $code = (int) ($body['error']['code'] ?? 0);
            throw new OpenRouterException('provider_error', 'The AI provider returned an error.', $code === 429 || $code >= 500);
        }

        $content = $body['choices'][0]['message']['content'] ?? null;
        if (is_array($content)) {
            $content = collect($content)->where('type', 'text')->pluck('text')->implode('');
        }
        if (! is_string($content) || trim($content) === '') {
            throw new OpenRouterException('empty_response', 'The AI provider returned an empty answer.', true);
        }

        return [
            'content' => $content,
            'request_id' => $body['id'] ?? $response->header('x-request-id'),
            'model' => $body['model'] ?? null,
            'prompt_tokens' => $body['usage']['prompt_tokens'] ?? null,
            'completion_tokens' => $body['usage']['completion_tokens'] ?? null,
            'cost' => isset($body['usage']['cost']) ? (float) $body['usage']['cost'] : null,
        ];
    }
}
