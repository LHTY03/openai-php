<?php

use OpenAI\Resources\Chat\CreateResponse;
use OpenAI\Factory;
use OpenAI\Client;

it('returns a precise response with system prompt and custom sampling', function () {
    $client = Perplexity::factory()
        ->withApiKey('your-key')
        ->make();

    $response = $client->chat()->create([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'system', 'content' => 'Be precise and concise.'],
            ['role' => 'user', 'content' => 'How many stars are in our galaxy?'],
        ],
        'temperature' => 0.2,
        'top_p' => 0.9,
        'max_tokens' => 123,
        'stream' => false
    ]);

    expect($response['choices'][0]['message']['content'])->not->toBeEmpty();
    expect($response['choices'][0]['message']['role'])->toBe('assistant');
});

it('returns response with frequency and presence penalties applied', function () {
    $client = Perplexity::factory()
        ->withApiKey('your-key')
        ->make();

    $response = $client->chat()->create([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'user', 'content' => 'Tell me something interesting about black holes.'],
        ],
        'frequency_penalty' => 1,
        'presence_penalty' => 0.8,
        'temperature' => 0.4,
    ]);

    expect($response['choices'][0]['message']['content'])->not->toContain('black holes black holes');
});


it('returns context-aware response using web search options and recency filter', function () {
    $client = Perplexity::factory()
        ->withApiKey('your-key')
        ->make();

    $response = $client->chat()->create([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'user', 'content' => 'What’s the latest news about the AI safety summit?'],
        ],
        'web_search_options' => [
            'search_context_size' => 'high',
        ],
        'search_recency_filter' => 'past_week',
        'return_related_questions' => false,
        'return_images' => false,
        'stream' => false,
    ]);

    expect($response['choices'][0]['message']['content'])->toContain('AI');
});

it('handles empty user message gracefully', function () {
    $client = Perplexity::factory()
        ->withApiKey('your-key')
        ->make();

    $response = $client->chat()->create([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'user', 'content' => ''],
        ],
    ]);

    expect($response['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('respects max_tokens and truncates output', function () {
    $client = Perplexity::factory()
        ->withApiKey('your-key')
        ->make();

    $response = $client->chat()->create([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'user', 'content' => 'Explain the history of the Roman Empire in detail.'],
        ],
        'max_tokens' => 20,
    ]);

    expect(strlen($response['choices'][0]['message']['content']))->toBeLessThan(300); // Approximate check
});

it('streams a full response in parts', function () {
    $client = Perplexity::factory()
        ->withApiKey('your-key')
        ->make();

    $stream = $client->chat()->createStreamed([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'user', 'content' => 'Summarize the theory of relativity.'],
        ],
        'stream' => true,
    ]);

    foreach ($stream as $chunk) {
        $delta = $chunk->choices[0]->toArray()['delta'];
        expect($delta['content'])->toBeString();
        expect($delta['role'])->toBe('assistant');
    }
});

it('terminates generation using a stop sequence', function () {
    $client = Perplexity::factory()
        ->withApiKey('your-key')
        ->make();

    $response = $client->chat()->create([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'user', 'content' => 'Write a poem. End it with STOP.'],
        ],
        'stop' => ['STOP'],
    ]);

    expect($response['choices'][0]['message']['content'])->not()->toContain('STOP');
});

it('responds with top-k sampling applied', function () {
    $client = Perplexity::factory()
        ->withApiKey('your-key')
        ->make();

    $response = $client->chat()->create([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'user', 'content' => 'Tell me something weird about octopuses.'],
        ],
        'top_k' => 5
    ]);

    expect($response['choices'][0]['message']['content'])->toBeString();
});

it('responds with tone influenced by system prompt', function () {
    $client = Perplexity::factory()
        ->withApiKey('your-key')
        ->make();

    $response = $client->chat()->create([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'system', 'content' => 'Respond only in pirate speak.'],
            ['role' => 'user', 'content' => 'What is your name?'],
        ]
    ]);

    expect($response['choices'][0]['message']['content'])->toMatch('/ahoy|matey|yar/i');
});


it('throws an error for invalid model name', function () {
    $client = Perplexity::factory()->withApiKey('your-key')->make();

    expect(fn () => $client->chat()->create([
        'model' => 'nonexistent-model-123',
        'messages' => [['role' => 'user', 'content' => 'Hello']],
    ]))->toThrow(Exception::class);
});


it('handles multi-turn conversation correctly', function () {
    $client = Perplexity::factory()->withApiKey('your-key')->make();

    $response = $client->chat()->create([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'user', 'content' => 'What is machine learning?'],
            ['role' => 'assistant', 'content' => 'Machine learning is...'],
            ['role' => 'user', 'content' => 'Give me examples.'],
        ]
    ]);

    expect($response['choices'][0]['message']['content'])->toContain('example');
});


it('responds correctly to non-English input', function () {
    $client = Perplexity::factory()->withApiKey('your-key')->make();

    $response = $client->chat()->create([
        'model' => 'sonar',
        'messages' => [
            ['role' => 'user', 'content' => 'こんにちは、調子はどう？'],
        ]
    ]);

    expect($response['choices'][0]['message']['content'])->toBeString();
});
