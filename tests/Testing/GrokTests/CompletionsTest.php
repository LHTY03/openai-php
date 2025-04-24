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

it('returns a streamed completions response', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
        
    $stream = $client->completions()->createStreamed([
            'model' => 'grok-2-1212',
            'prompt' => 'Hi, how are you?',
            'max_tokens' => 10,
        ]);
        
    foreach($stream as $response){
        expect($response->choices[0]->text)->toBeString();
    }
    
});

it('throws an exception when model is invalid', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
    
    expect(function () use ($client) {
        $client->completions()->create([
            'model' => 'non-existent-model',
            'prompt' => 'Test prompt',
            'max_tokens' => 100
        ]);
    })->toThrow(\Exception::class);
});

it('produces different outputs with varying temperatures', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
    
    $prompt = "Write a one-sentence story about a cat.";
    
    // Get response with temperature 0 (deterministic)
    $response1 = $client->completions()->create([
        'model' => 'grok-2-1212',
        'prompt' => $prompt,
        'max_tokens' => 50,
        'temperature' => 0
    ]);
    
    // Get response with temperature 1 (more random)
    $response2 = $client->completions()->create([
        'model' => 'grok-2-1212',
        'prompt' => $prompt,
        'max_tokens' => 50,
        'temperature' => 1
    ]);
    
    // Responses should be different due to temperature difference
    expect($response1->choices[0]->text)->not->toBe($response2->choices[0]->text);
});



