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
