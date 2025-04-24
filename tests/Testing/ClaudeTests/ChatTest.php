<?php

use OpenAI\Resources\Chat\CreateResponse;
use OpenAI\Factory;
use OpenAI\Client;

test('chat completion generates programming help', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('anthropic')
        ->withProvider('claude')
        ->withProject('anthropic')
        ->make();
    
    $response = $client->chat()->create([
        'model' => 'claude-3-opus-20240229',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful programming assistant who writes clear, commented code.'],
            ['role' => 'user', 'content' => 'Write a Python function to check if a number is prime']
        ],
        'temperature' => 0.7
    ]);
    
    expect($response)->toBeInstanceOf(CreateResponse::class);
    expect($response->choices[0]->message->role)->toBe('assistant');
    expect($response->choices[0]->message->content)->toContain('def is_prime');
    expect($response->choices[0]->message->content)->toContain('#');  // Should contain comments
    expect($response->model)->toBe('claude-3-opus-20240229');
}); 