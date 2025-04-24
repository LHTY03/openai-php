<?php

namespace OpenAI;

use Exception;

final class MultiProviderClient
{
    private array $clients = [];
    private array $sessionProviderMap = [];
    private array $capabilities;

    public function __construct(array $providers, array $capabilities)
    {
        $this->capabilities = $capabilities;

        foreach ($providers as $provider => $apiKey) {
            $tmpClient = (new Factory())
                ->withApiKey($apiKey)
                ->withOrganization('')
                ->withProject('')
                ->make();

            $usage_map = [
                'max_tokens' => 0,
                'tokens_used' => 0,
                'tokens_remaining' => 0,
                'max_requests' => 0,
                'requests_used' => 0,
                'requests_remaining' => 0,
                'window_start' => 0
            ];

            $this->clients[$provider] = [
                'client' => $tmpClient,
                'usage' => $usage_map
            ];
        }
    }

    public function getClient(string $provider)
    {
        return $this->clients[$provider]['client'] ?? null;
    }

    public function requestWithRetry(string $sessionId, string $taskType, string $model, array $callChain, array $params, int $maxRetries = 2)
    {
        $primaryProvider = $this->sessionProviderMap[$sessionId] ?? array_key_first($this->capabilities[$taskType]);
        $providerList = array_keys($this->capabilities[$taskType]);

        foreach ($providerList as $provider) {
            if (!isset($this->clients[$provider])) continue;

            $usage = $this->clients[$provider]['usage'];
            if ($usage['tokens_used'] >= 0.9 * $usage['max_tokens'] || $usage['requests_used'] >= 0.9 * $usage['max_requests']) {
                continue; // skip overloaded provider
            }

            try {
                $response = $this->makeChainedCall($provider, $callChain, $params);
                $this->sessionProviderMap[$sessionId] = $provider;
                return $response;
            } catch (Exception $e) {
                if (!$this->isRetryable($e)) continue;
                for ($retry = 0; $retry < $maxRetries; $retry++) {
                    sleep(pow(2, $retry));
                    try {
                        return $this->makeChainedCall($provider, $callChain, $params);
                    } catch (Exception $e) {
                        if ($retry === $maxRetries - 1) break;
                    }
                }
            }
        }

        throw new Exception("All providers failed or are over quota.");
    }

    private function makeChainedCall(string $provider, array $callChain, array $params)
    {
        $client = $this->clients[$provider]['client'];
        foreach ($callChain as $method) {
            if (method_exists($client, $method)) {
                $client = $client->$method();
            } else {
                throw new Exception("Method $method not found for provider $provider");
            }
        }

        $response = $client->create($params);

        $tokensUsed = $response['usage']['total_tokens'] ?? 5;
        $this->updateUsageStats($provider, $tokensUsed);

        return $response;
    }

    private function isRetryable(Exception $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, 'Timeout') || str_contains($message, '503') || str_contains($message, '502');
    }

    private function updateUsageStats(string $provider, int $requestTokens)
    {
        $now = time();
        $usage = &$this->clients[$provider]['usage'];

        if ($usage['window_start'] === 0) {
            $usage['window_start'] = $now;
            return;
        }

        $elapsed = $now - $usage['window_start'];

        if ($elapsed >= 60) {
            if ($usage['tokens_used'] > $usage['max_tokens']) {
                $usage['max_tokens'] = $usage['tokens_used'];
            }

            if ($usage['requests_used'] > $usage['max_requests']) {
                $usage['max_requests'] = $usage['requests_used'];
            }

            $usage['tokens_used'] = 0;
            $usage['requests_used'] = 0;
            $usage['window_start'] = $now;
        }

        $usage['tokens_used'] += $requestTokens;
        $usage['requests_used'] += 1;
    }

    public function getUsageStats(string $provider): array
    {
        return $this->clients[$provider]['usage'] ?? [];
    }
}
