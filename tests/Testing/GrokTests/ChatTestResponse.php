<?php

use OpenAI\Resources\Chat\CreateResponse;
use OpenAI\Factory;
use OpenAI\Client;

it('returns a response', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
        
    $result = $client->chat()->create([
        'model' => 'grok-2-latest',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello! How are you?'],
        ],
    ]);
    
    # expect that the result is an instance of CreateResponse
    #expect($result)->toBeInstanceOf(CreateResponse::class);
    
    #expect the response to be not empty
    expect($result['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('accepts a system role message and returns a response', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
        
    $result = $client->chat()->create([
        'model' => 'grok-2-latest',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => 'Hello! How are you?']
        ],
    ]);
    
    # expect that the result is an instance of CreateResponse
    #expect($result)->toBeInstanceOf(CreateResponse::class);
    
    #expect the response to be not empty
    expect($result['choices'][0]['message']['content'])->not->toBeEmpty();
});