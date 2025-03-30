<?php

namespace OpenAI;

use Exception;

final class MultiProviderClient
{    
    private array $clients = []; // Removed nullable `?`

    public function __construct(array $providers)
    {
        foreach ($providers as $provider => $apiKey) {
            // Ensure Factory is correctly instantiated
            $tmpClient = (new Factory())
                ->withApiKey($apiKey)
                ->withOrganization('')
                ->withProject('')
                ->make();

            // Initialize usage tracking
            $usage_map = [
                'max_tokens' => 0,
                'tokens_used' => 0,
                'tokens_remaining' => 0,
                'max_requests' => 0,
                'requests_used' => 0,
                'requests_remaining' => 0,
                'window_start' => 0
            ];

            // Store client and usage separately
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

    // Dynamic function calling with unlimited method arguments
    public function request(string $provider, ...$args)
    {
        if (!isset($this->clients[$provider])) {
            throw new Exception("Provider $provider not found.");
        }
    
        $client = $this->clients[$provider]['client'];
    
        // Extract the final parameter set (last argument must be an array)
        $params = array_pop($args);
        $final_func = array_pop($args);
    
        // Dynamically call chained functions, except for the last one
        foreach ($args as $index => $method) {
            if (!method_exists($client, $method)) {
                throw new Exception("Method $method does not exist in provider $provider.");
            }
    
            // If this is the last method in the chain, pass $params as an argument
            if ($index === array_key_last($args)) {
                $client = $client->$method($params);
            } else {
                $client = $client->$method();
            }
        }

        // Final function call with parameters
        $response = $client->$final_func($params);

        // Extract token usage if available
        $tokens_used = $response['usage']['total_tokens'] ?? 5;
        updateUsageStats($provider, $tokens_used);

        return $response;
    }

    public function getUsageStats(string $provider): array
    {
        return $this->clients[$provider]['usage'] ?? null;
    }
    
    private function updateUsageStats(string $provider, int $requestTokens)
    {
        $now = time();
        $usage = &$this->clients[$provider]['usage'];

        // If usage tracking hasn't started yet, start now
        if ($usage['window_start'] === 0) {
            $usage['window_start'] = $now;
            return;
        }

        $elapsed = $now - $usage['window_start'];

        if ($elapsed >= 60) {
            // Update max if a new high was reached in the last window
            if ($usage['tokens_used'] > $usage['max_tokens']) {
                $usage['max_tokens'] = $usage['tokens_used'];
            }

            if ($usage['requests_used'] > $usage['max_requests']) {
                $usage['max_requests'] = $usage['requests_used'];
            }

            // Reset for next window
            $usage['tokens_used'] = 0;
            $usage['requests_used'] = 0;
            $usage['window_start'] = $now;
        }
        
        $usage['tokens_used'] += $requestTokens;
        $usage['requests_used'] += 1;
    }

    
}
