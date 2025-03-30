<?php

use OpenAI\Resources\Chat\CreateResponse;
use OpenAI\Factory;
use OpenAI\Client;

$apikey=getenv('GROK_API_KEY');

it('returns a response', function () {
    $client = OpenAI::factory()
        ->withApiKey(GROK_API_KEY)
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
        ->withApiKey(GROK_API_KEY)
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
    expect($result['choices'][0]['message']['role'])->toBe('assistant');
    expect($result['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('returns a streamed chat response', function () {
    $client = OpenAI::factory()
        ->withApiKey(GROK_API_KEY)
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
        
    $stream = $client->chat()->createStreamed([
            'model' => 'grok-2-latest',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
    ]);
        
    foreach($stream as $response){
        $arr = $response->choices[0]->toArray();       
        expect($arr['index'])->toBeInt();
        expect($arr['delta']['content'])->toBeString();
        expect($arr['delta']['role'])->toBe('assistant');
    }
    
});