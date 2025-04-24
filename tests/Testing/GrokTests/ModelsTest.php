<?php

use OpenAI\Resources\Models;
use OpenAI\Factory;
use OpenAI\Client;
use OpenAI\Exceptions\InvalidArgumentException;


it('returns a list of models', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
        
    $response = $client->models()->list();
    
    # expect response object to be a list
    expect($response)->toBeInstanceOf(\OpenAI\Responses\Models\ListResponse::class);
    
    #expect the response to be not empty
    expect($response->object)->toBe('list');
    expect($response->data)->not->toBeEmpty();
    expect($response->data[0]->id)->not->toBeEmpty();
    expect($response->data[0]->object)->toBe('model');    
});

it('retreives a models attributes', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
        
    $response = $client->models()->list();
    
    # expect response object to be a list
    expect($response)->toBeInstanceOf(\OpenAI\Responses\Models\ListResponse::class);
    
    $model_name = $response->data[0]->id;
    $response = $client->models()->retrieve($model_name);
    
    #expect the response to be not empty
    expect($response->id)->toBe($model_name);
    expect($response->object)->toBe('model');
    expect($response->created)->not->toBeEmpty();
    expect($response->ownedBy)->toBe('xai');   
});

// Edge case: retrieving a model using a non‐string ID type
it("throws an exception when retrieving a model with a non‐string ID", function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();

    // passing an integer instead of a string
    expect(function () use ($client) {
        $client->models()->retrieve(12345);
    })->toThrow(InvalidArgumentException::class);
});


it('filters image generation models from the full model list', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();

    $models = $client->models()->list();

    $imageModels = array_filter($models->data, fn($model) =>
        str_contains($model->id, 'image') || $model->id === 'grok-dalle' // common pattern
    );

    expect($imageModels)->not->toBeEmpty();
    foreach ($imageModels as $model) {
        expect($model->object)->toBe('model');
    }
});


it('confirms a language model supports chat', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();

    $response = $client->models()->retrieve('grok-3');
    
    expect($response->id)->toBe('grok-3');
    expect($response->object)->toBe('model');
    expect($response->capabilities)->toContain('chat'); // assuming 'capabilities' field
});


it('throws an error when retrieving a non-existent model', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();

    expect(function () use ($client) {
        $client->models()->retrieve('grok-fake-model-xyz');
    })->toThrow(\Exception::class);
});


it('ensures all listed models include essential metadata', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();

    $response = $client->models()->list();

    foreach ($response->data as $model) {
        expect($model->id)->not->toBeEmpty();
        expect($model->object)->toBe('model');
        expect($model->ownedBy)->toBeString(); // often 'xai'
    }
});
