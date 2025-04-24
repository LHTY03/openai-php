<?php

use OpenAI\Resources\Audio\TranscriptionResponse;
use OpenAI\Factory;
use OpenAI\Client;

test('transcribes audio file to text', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('anthropic')
        ->withProvider('claude')
        ->withProject('anthropic')
        ->make();
    
    // Using a test audio file containing "Hello, this is a test recording"
    $audioFile = __DIR__ . '/fixtures/test-audio.mp3';
    
    $response = $client->audio()->transcribe([
        'model' => 'claude-3-opus-20240229',
        'file' => fopen($audioFile, 'r'),
        'response_format' => 'verbose_json',
        'timestamp_granularities' => ['word'],
        'language' => 'en'
    ]);
    
    expect($response)->toBeInstanceOf(TranscriptionResponse::class);
    expect($response->text)->toBeString();
    expect($response->text)->toContain('Hello');
    expect($response->text)->toContain('test');
    
    // Check word-level timestamps if available
    if (isset($response->words)) {
        expect($response->words)->toBeArray();
        foreach ($response->words as $word) {
            expect($word)->toHaveKeys(['word', 'start', 'end']);
            expect($word->start)->toBeFloat();
            expect($word->end)->toBeFloat();
            expect($word->end)->toBeGreaterThan($word->start);
        }
    }
}); 