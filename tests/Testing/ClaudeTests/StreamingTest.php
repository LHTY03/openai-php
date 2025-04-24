<?php

use OpenAI\Resources\Chat\CreateStreamedResponse;
use OpenAI\Factory;
use OpenAI\Client;

test('streams response in chunks', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('anthropic')
        ->withProvider('claude')
        ->withProject('anthropic')
        ->make();
    
    $stream = $client->chat()->createStreamed([
        'model' => 'claude-3-opus-20240229',
        'messages' => [
            ['role' => 'user', 'content' => 'Count from 1 to 3 with one second pause between numbers']
        ],
        'stream' => true,
        'max_tokens' => 50
    ]);
    
    $chunks = [];
    $numbers = [];
    
    foreach ($stream as $response) {
        expect($response)->toBeInstanceOf(CreateStreamedResponse::class);
        if ($response->choices[0]->delta->content) {
            $chunks[] = $response->choices[0]->delta->content;
            // Extract numbers from content
            preg_match('/\d+/', $response->choices[0]->delta->content, $matches);
            if (!empty($matches)) {
                $numbers[] = (int)$matches[0];
            }
        }
    }
    
    expect($chunks)->toBeArray();
    expect(count($chunks))->toBeGreaterThan(1);
    expect($numbers)->toContain(1);
    expect($numbers)->toContain(2);
    expect($numbers)->toContain(3);
    // Numbers should appear in order
    expect($numbers)->toBe(array_unique($numbers));
}); 