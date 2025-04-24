<?php

use OpenAI\Resources\Chat\CreateResponse;
use OpenAI\Factory;
use OpenAI\Client;

it('returns a valid Claude response using OpenAI SDK interface', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key') // Claude key format
        ->withProvider('claude')        // Assuming provider is settable
        ->make();

    $response = $client->chat()->create([
        'model' => 'claude-3-sonnet-20240229',
        'messages' => [
            ['role' => 'user', 'content' => 'Explain what transformers are in machine learning.'],
        ],
    ]);

    expect($response['choices'][0]['message']['role'])->toBe('assistant');
    expect($response['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('throws error when attempting function call on Claude model', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    expect(function () use ($client) {
        $client->chat()->create([
            'model' => 'claude-3-haiku-20240307',
            'messages' => [['role' => 'user', 'content' => 'What is the weather in SF?']],
            'functions' => [[
                'name' => 'get_weather',
                'description' => 'Get weather',
                'parameters' => ['type' => 'object', 'properties' => ['location' => ['type' => 'string']]],
            ]],
        ]);
    })->toThrow(Exception::class); // or specific SDK exception if known
});

it('handles multilingual input with Claude model', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $response = $client->chat()->create([
        'model' => 'claude-3-opus-20240229',
        'messages' => [
            ['role' => 'user', 'content' => 'Bonjour, peux-tu m’expliquer la physique quantique ?'],
        ],
    ]);

    expect($response['choices'][0]['message']['content'])->toContain('quantique');
});