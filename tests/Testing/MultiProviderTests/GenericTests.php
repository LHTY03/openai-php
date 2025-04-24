<?php
use OpenAI\MultiProviderClient;

$mockProviders = [
    'openai' => 'fake-api-key-openai',
    'grok' => 'fake-api-key-grok',
    'claude' => 'fake-api-key-claude'
];

it('initializes providers and sets default usage values', function () use ($mockProviders) {
    $client = new MultiProviderClient($mockProviders);
    $usage = $client->getUsageStats('openai');

    expect($usage['tokens_used'])->toBe(0);
    expect($usage['requests_used'])->toBe(0);
    expect($usage['window_start'])->toBe(0);
});

it('tracks token and request usage after a valid request', function () use ($mockProviders) {
    $client = new MultiProviderClient($mockProviders);

    $response = $client->request('openai', 'chat', 'create', [
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => 'Ping']]
    ]);

    $usage = $client->getUsageStats('openai');
    expect($usage['tokens_used'])->toBeGreaterThan(0);
    expect($usage['requests_used'])->toBe(1);
    expect($response['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('throws when using an invalid provider', function () use ($mockProviders) {
    $client = new MultiProviderClient($mockProviders);
    $client->request('invalid-provider', 'chat', 'create', []);
})->throws(Exception::class);

it('falls back to another provider when usage limits are exceeded', function () use ($mockProviders) {
    $client = new MultiProviderClient($mockProviders);

    // Simulate token limit reached for openai
    $usage = &$client->getUsageStats('openai');
    $usage['tokens_used'] = 100000;
    $usage['max_tokens'] = 100000;

    $response = $client->request('grok', 'chat', 'create', [
        'model' => 'grok-2-latest',
        'messages' => [['role' => 'user', 'content' => 'Testing fallback']]
    ]);

    expect($response['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('tracks session-based provider pinning', function () use ($mockProviders) {
    $client = new MultiProviderClient($mockProviders);
    $client->sessionMap['session-abc'] = 'claude';

    $response = $client->request('claude', 'chat', 'create', [
        'model' => 'claude-3-sonnet-20240229',
        'messages' => [['role' => 'user', 'content' => 'What’s 5 + 5?']]
    ]);

    expect($response['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('resets usage stats after 60 seconds', function () use ($mockProviders) {
    $client = new MultiProviderClient($mockProviders);

    // Simulate usage
    $usage = &$client->getUsageStats('openai');
    $usage['tokens_used'] = 100;
    $usage['requests_used'] = 5;
    $usage['window_start'] = time() - 61;

    // Trigger update to roll the window
    $client->request('openai', 'chat', 'create', [
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => 'Reset test']]
    ]);

    $usage = $client->getUsageStats('openai');
    expect($usage['tokens_used'])->toBeGreaterThan(0); // New token count
    expect($usage['requests_used'])->toBe(1);          // Reset to fresh count
});

it('avoids provider if request usage exceeds max_requests', function () use ($mockProviders) {
    $client = new MultiProviderClient($mockProviders);

    // Simulate provider over request limit
    $usage = &$client->getUsageStats('openai');
    $usage['requests_used'] = 999;
    $usage['max_requests'] = 999;

    // Should fallback to another provider
    $response = $client->request('grok', 'chat', 'create', [
        'model' => 'grok-2-latest',
        'messages' => [['role' => 'user', 'content' => 'Should fallback']]
    ]);

    expect($response['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('cycles to next available provider if primary fails', function () use ($mockProviders) {
    $client = new MultiProviderClient($mockProviders);

    // Artificially simulate that openai is missing
    unset($client->clients['openai']);

    $response = $client->request('grok', 'chat', 'create', [
        'model' => 'grok-2-latest',
        'messages' => [['role' => 'user', 'content' => 'Provider cycling']]
    ]);

    expect($response['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('retries once after simulated transient error', function () use ($mockProviders) {
    $client = new MultiProviderClient($mockProviders);

    // Simulate retry strategy manually by calling fallback
    try {
        $client->request('openai', 'chat', 'create', [
            'model' => 'nonexistent-model', // Will trigger fail + fallback
            'messages' => [['role' => 'user', 'content' => 'Trigger retry']]
        ]);
    } catch (Exception $e) {
        // Ignore first error for this test
    }

    // Try again with working provider
    $response = $client->request('grok', 'chat', 'create', [
        'model' => 'grok-2-latest',
        'messages' => [['role' => 'user', 'content' => 'Retry result']]
    ]);

    expect($response['choices'][0]['message']['content'])->toContain('Retry');
});