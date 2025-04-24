<?php

use OpenAI\Resources\Images\CreateResponse;
use OpenAI\Factory;
use OpenAI\Client;

test('generates an image with DALL-E', function () {
    $client = makeClient();
    
    $response = $client->images()->create([
        'model' => 'dall-e-3',
        'prompt' => 'A cute baby penguin wearing a red bowtie',
        'n' => 1,
        'size' => '1024x1024',
        'quality' => 'standard',
        'style' => 'natural'
    ]);
    
    expect($response)->toBeInstanceOf(CreateResponse::class);
    expect($response->data)->toBeArray();
    expect($response->data[0]->url)->toBeString();
    expect($response->data[0]->url)->toMatch('/^https:\/\//');
});

test('generates image variations', function () {
    $client = makeClient();
    
    // First create an initial image
    $response = $client->images()->create([
        'model' => 'dall-e-3',
        'prompt' => 'A simple red circle on white background',
        'n' => 1,
        'size' => '1024x1024'
    ]);
    
    // Get the image data
    $imageUrl = $response->data[0]->url;
    $imageData = file_get_contents($imageUrl);
    
    // Create variations
    $variations = $client->images()->variations([
        'image' => $imageData,
        'n' => 2,
        'size' => '1024x1024'
    ]);
    
    expect($variations->data)->toBeArray();
    expect(count($variations->data))->toBe(2);
    foreach ($variations->data as $variation) {
        expect($variation->url)->toBeString();
        expect($variation->url)->toMatch('/^https:\/\//');
    }
});

test('handles image generation errors gracefully', function () {
    $client = makeClient();
    
    expect(function () use ($client) {
        $client->images()->create([
            'model' => 'dall-e-3',
            'prompt' => '', // Empty prompt should cause an error
            'n' => 1,
            'size' => '1024x1024'
        ]);
    })->toThrow(\Exception::class);
}); 