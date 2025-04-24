<?php

use OpenAI\Resources\Chat\CreateStreamedResponse;
use OpenAI\Factory;
use OpenAI\Client;

test('streams chat completion responses', function () {
    $client = makeClient();
    
    $stream = $client->chat()->createStreamed([
        'model' => 'perplexity/gpt-4',
        'messages' => [
            ['role' => 'user', 'content' => 'Count from 1 to 5 slowly.']
        ],
        'stream' => true
    ]);
    
    $chunks = [];
    foreach ($stream as $response) {
        expect($response)->toBeInstanceOf(CreateStreamedResponse::class);
        $chunks[] = $response->choices[0]->delta->content;
    }
    
    expect($chunks)->toBeArray();
    expect(count($chunks))->toBeGreaterThan(1);
});

test('handles streaming with function calls', function () {
    $client = makeClient();
    
    $functions = [
        [
            'name' => 'get_weather',
            'description' => 'Get the current weather in a location',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'location' => [
                        'type' => 'string',
                        'description' => 'The city and state, e.g. San Francisco, CA'
                    ]
                ],
                'required' => ['location']
            ]
        ]
    ];
    
    $stream = $client->chat()->createStreamed([
        'model' => 'perplexity/gpt-4',
        'messages' => [
            ['role' => 'user', 'content' => 'What\'s the weather like in London?']
        ],
        'functions' => $functions,
        'stream' => true
    ]);
    
    $functionCall = null;
    foreach ($stream as $response) {
        if (isset($response->choices[0]->delta->functionCall)) {
            $functionCall = $response->choices[0]->delta->functionCall;
            break;
        }
    }
    
    expect($functionCall)->not->toBeNull();
    expect($functionCall->name)->toBe('get_weather');
}); 