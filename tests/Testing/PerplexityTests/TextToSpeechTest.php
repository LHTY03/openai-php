<?php

use OpenAI\Resources\Audio\SpeechResponse;
use OpenAI\Factory;
use OpenAI\Client;

test('converts text to speech', function () {
    $client = makeClient();
    
    $response = $client->audio()->speech([
        'model' => 'tts-1',
        'input' => 'Hello world! This is a test of the text to speech API.',
        'voice' => 'alloy',
        'response_format' => 'mp3'
    ]);
    
    expect($response)->toBeInstanceOf(SpeechResponse::class);
    expect($response->audioData)->toBeString();
    
    // Save the audio to a temporary file to verify it's valid MP3
    $tempFile = tempnam(sys_get_temp_dir(), 'tts_test');
    file_put_contents($tempFile, $response->audioData);
    
    // Verify file type using finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    expect(finfo_file($finfo, $tempFile))->toBe('audio/mpeg');
    
    unlink($tempFile);
});

test('supports different voices', function () {
    $client = makeClient();
    $voices = ['alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer'];
    
    foreach ($voices as $voice) {
        $response = $client->audio()->speech([
            'model' => 'tts-1',
            'input' => 'Testing different voices.',
            'voice' => $voice,
            'response_format' => 'mp3'
        ]);
        
        expect($response)->toBeInstanceOf(SpeechResponse::class);
        expect($response->audioData)->toBeString();
    }
});

test('handles different output formats', function () {
    $client = makeClient();
    $formats = ['mp3', 'opus', 'aac', 'flac'];
    
    foreach ($formats as $format) {
        $response = $client->audio()->speech([
            'model' => 'tts-1',
            'input' => 'Testing different output formats.',
            'voice' => 'alloy',
            'response_format' => $format
        ]);
        
        expect($response)->toBeInstanceOf(SpeechResponse::class);
        expect($response->audioData)->toBeString();
    }
});

test('handles long text input', function () {
    $client = makeClient();
    
    $longText = str_repeat('This is a test of long form text to speech conversion. ', 10);
    
    $response = $client->audio()->speech([
        'model' => 'tts-1',
        'input' => $longText,
        'voice' => 'alloy',
        'response_format' => 'mp3',
        'speed' => 1.0
    ]);
    
    expect($response)->toBeInstanceOf(SpeechResponse::class);
    expect($response->audioData)->toBeString();
}); 