<?php

use OpenAI\Resources\Models;
use OpenAI\Factory;
use OpenAI\Client;

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
        ->withApiKey('xai-yoOhU8tYaS6wqm0b8lYhIe4MpVWhosbEviYiaWiD9aKq5eDsDfBaO43Y3zSGDwUtdu7KlET6XEel5bzY')
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

