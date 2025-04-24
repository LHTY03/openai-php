<?php

use OpenAI\Resources\Audio\TranscriptionResponse;
use OpenAI\Resources\Audio\TranslationResponse;
use OpenAI\Factory;
use OpenAI\Client;

test('transcribes audio file to text', function () {
    $client = makeClient();
    
    // Assuming we have a test audio file
    $audioFile = __DIR__ . '/fixtures/test-audio.mp3';
    
    $response = $client->audio()->transcribe([
        'model' => 'whisper-1',
        'file' => fopen($audioFile, 'r'),
        'response_format' => 'json'
    ]);
    
    expect($response)->toBeInstanceOf(TranscriptionResponse::class);
    expect($response->text)->toBeString();
    expect($response->text)->not->toBeEmpty();
});

test('translates audio to English', function () {
    $client = makeClient();
    
    // Assuming we have a test audio file in a non-English language
    $audioFile = __DIR__ . '/fixtures/test-audio-french.mp3';
    
    $response = $client->audio()->translate([
        'model' => 'whisper-1',
        'file' => fopen($audioFile, 'r'),
        'response_format' => 'json'
    ]);
    
    expect($response)->toBeInstanceOf(TranslationResponse::class);
    expect($response->text)->toBeString();
    expect($response->text)->not->toBeEmpty();
    // The response should be in English regardless of input language
    expect($response->text)->toMatch('/^[A-Za-z0-9\s\.,!?-]+$/');
});

test('handles different audio formats', function () {
    $client = makeClient();
    $formats = ['mp3', 'mp4', 'mpeg', 'mpga', 'm4a', 'wav', 'webm'];
    
    foreach ($formats as $format) {
        $audioFile = __DIR__ . "/fixtures/test-audio.{$format}";
        if (!file_exists($audioFile)) continue;
        
        $response = $client->audio()->transcribe([
            'model' => 'whisper-1',
            'file' => fopen($audioFile, 'r'),
            'response_format' => 'json'
        ]);
        
        expect($response)->toBeInstanceOf(TranscriptionResponse::class);
        expect($response->text)->toBeString();
    }
});

test('supports timestamps in transcription', function () {
    $client = makeClient();
    
    $audioFile = __DIR__ . '/fixtures/test-audio.mp3';
    
    $response = $client->audio()->transcribe([
        'model' => 'whisper-1',
        'file' => fopen($audioFile, 'r'),
        'response_format' => 'verbose_json',
        'timestamp_granularities' => ['segment', 'word']
    ]);
    
    expect($response->segments)->toBeArray();
    foreach ($response->segments as $segment) {
        expect($segment)->toHaveKeys(['start', 'end', 'text']);
        expect($segment->start)->toBeFloat();
        expect($segment->end)->toBeFloat();
        expect($segment->text)->toBeString();
    }
}); 