<?php

use OpenAI\Responses\Completions\CreateResponse;
use OpenAI\Resources\Completions;
use OpenAI\Factory;
use OpenAI\Client;

it('returns a completions response with correct parameters', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
        
    $response = $client->completions()->create([
            'model' => 'grok-2-1212',
            'prompt' => 'Write 3 sentences about lions',
            'max_tokens' => 100,
            'temperature' => 0
    ]);
    
    # check that response has the correct class
    #expect($response)->toBeInstanceOf(\OpenAI\Responses\Completions\CreateResponse::class);
    expect($response->id)->not->toBeEmpty();
    expect($response->object)->toBe('text_completion');
    expect($response->created)->not->toBeEmpty();
    expect($response->model)->toBe('grok-2-1212');
    expect($response->usage->promptTokens)->not->toBeEmpty();
    expect($response->usage->completionTokens)->not->toBeEmpty();
    expect($response->usage->totalTokens)->toBe($response->usage->promptTokens + $response->usage->completionTokens);
    
    foreach ($response->choices as $choice) {
        expect($choice->text)->not->toBeEmpty ; // '\n\nThis is a test'
        #expect choice index to be an integer
        #TODO: TEST FINISH REASON AND LOG PROBS
        expect($choice->index)->toBeInt();
    }
    
});