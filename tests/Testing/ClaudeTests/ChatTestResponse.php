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

it('ignores presence_penalty and frequency_penalty parameters', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $response = $client->chat()->create([
        'model' => 'claude-3-sonnet-20240229',
        'messages' => [['role' => 'user', 'content' => 'Tell me something about penguins.']],
        'presence_penalty' => 2,
        'frequency_penalty' => 2,
    ]);

    expect($response['choices'][0]['message']['content'])->toBeString();
});

it('forces single completion output even when n > 1', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $response = $client->chat()->create([
        'model' => 'claude-3-opus-20240229',
        'messages' => [['role' => 'user', 'content' => 'List three facts about space.']],
        'n' => 3, // Claude will still return only one choice
    ]);

    expect(count($response['choices']))->toBe(1);
});

it('uses extended thinking mode for complex queries', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $response = $client->chat()->create([
        'model' => 'claude-3-opus-20240229',
        'messages' => [['role' => 'user', 'content' => 'Explain the economic consequences of climate change.']],
        'extra_body' => [
            'thinking' => ['type' => 'enabled', 'budget_tokens' => 2000]
        ]
    ]);

    expect($response['choices'][0]['message']['content'])->toBeString();
});

it('concatenates system messages into a single hoisted prompt', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $response = $client->chat()->create([
        'model' => 'claude-3-haiku-20240307',
        'messages' => [
            ['role' => 'system', 'content' => 'Be brief.'],
            ['role' => 'system', 'content' => 'Always answer as a pirate.'],
            ['role' => 'user', 'content' => 'What is your name?'],
        ],
    ]);

    expect($response['choices'][0]['message']['content'])->toMatch('/ahoy|yar|matey/i');
});

it('ignores unsupported OpenAI fields without failing', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $response = $client->chat()->create([
        'model' => 'claude-3-sonnet-20240229',
        'messages' => [['role' => 'user', 'content' => 'Describe a sunset.']],
        'logprobs' => true,
        'metadata' => ['tag' => 'sunset-test'],
        'seed' => 42
    ]);

    expect($response['choices'][0]['message']['content'])->toBeString();
});

it('ignores tool_choice parameter with no error', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $response = $client->chat()->create([
        'model' => 'claude-3-sonnet-20240229',
        'messages' => [['role' => 'user', 'content' => 'What’s the weather like in Paris?']],
        'tool_choice' => ['type' => 'function', 'function' => ['name' => 'get_weather']]
    ]);

    expect($response['choices'][0]['message']['content'])->toBeString();
});


it('returns only one choice when streaming with n > 1', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $stream = $client->chat()->createStreamed([
        'model' => 'claude-3-haiku-20240307',
        'messages' => [['role' => 'user', 'content' => 'Give me a haiku about clouds.']],
        'stream' => true,
        'n' => 2
    ]);

    foreach ($stream as $chunk) {
        $delta = $chunk->choices[0]->toArray()['delta'];
        expect($delta['content'])->toBeString();
    }
});

it('respects stop sequence to truncate Claude output', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $response = $client->chat()->create([
        'model' => 'claude-3-opus-20240229',
        'messages' => [['role' => 'user', 'content' => 'Finish this sentence: The sky is']],
        'stop' => ['blue']
    ]);

    expect($response['choices'][0]['message']['content'])->not()->toContain('blue');
});

it('returns valid token usage information', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $response = $client->chat()->create([
        'model' => 'claude-3-sonnet-20240229',
        'messages' => [['role' => 'user', 'content' => 'Summarize the story of Icarus.']],
    ]);

    expect($response['usage']['prompt_tokens'])->toBeInt();
    expect($response['usage']['completion_tokens'])->toBeInt();
    expect($response['usage']['total_tokens'])->toBeInt();
});

it('handles long multilingual prompt gracefully', function () {
    $client = OpenAI::factory()
        ->withApiKey('sk-ant-your-key')
        ->withProvider('claude')
        ->make();

    $content = 'Hello! Bonjour! こんにちは！Can you describe the Eiffel Tower in each language?';

    $response = $client->chat()->create([
        'model' => 'claude-3-opus-20240229',
        'messages' => [['role' => 'user', 'content' => $content]],
    ]);

    expect($response['choices'][0]['message']['content'])->toContain('Eiffel');
});
