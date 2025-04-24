<?php

use OpenAI\Resources\Chat\CreateResponse;
use OpenAI\Factory;
use OpenAI\Client;

test('analyzes image content', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('anthropic')
        ->withProvider('claude')
        ->withProject('anthropic')
        ->make();
    
    // Using a test image of a cat
    $imageUrl = 'https://example.com/test-images/cat.jpg';
    
    $response = $client->chat()->create([
        'model' => 'claude-3-opus-20240229',
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Describe what you see in this image in detail.'],
                    [
                        'type' => 'image',
                        'image_url' => [
                            'url' => $imageUrl,
                            'detail' => 'high'
                        ]
                    ]
                ]
            ]
        ],
        'max_tokens' => 300
    ]);
    
    expect($response)->toBeInstanceOf(CreateResponse::class);
    expect($response->choices[0]->message->content)->toBeString();
    expect($response->choices[0]->message->content)->not->toBeEmpty();
    
    // The response should contain descriptive elements
    $content = $response->choices[0]->message->content;
    expect($content)->toContain('cat')
        ->and($content)->toContain('color')
        ->and($content)->toContain('background');
}); 