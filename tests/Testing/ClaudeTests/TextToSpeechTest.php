<?php

use OpenAI\Resources\Audio\SpeechResponse;
use OpenAI\Factory;
use OpenAI\Client;

test('converts text to speech', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('anthropic')
        ->withProvider('claude')
        ->withProject('anthropic')
        ->make();
    
    $response = $client->audio()->speech([
        'model' => 'claude-3-opus-20240229',
        'input' => 'Hello, this is a test of the text to speech system.',
        'voice' => 'alloy',
        'response_format' => 'mp3',
        'speed' => 1.0
    ]);
    
    expect($response)->toBeInstanceOf(SpeechResponse::class);
    expect($response->audioData)->toBeString();
    
    // Save and verify the audio file
    $tempFile = tempnam(sys_get_temp_dir(), 'tts_test');
    file_put_contents($tempFile, $response->audioData);
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    expect(finfo_file($finfo, $tempFile))->toBe('audio/mpeg');
    
    // Check file size (should be reasonable for a short audio clip)
    expect(filesize($tempFile))->toBeGreaterThan(1000);
    
    // Cleanup
    unlink($tempFile);
}); 