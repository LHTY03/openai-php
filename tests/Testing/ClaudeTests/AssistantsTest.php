<?php

use OpenAI\Resources\Assistants\Assistant;
use OpenAI\Resources\Threads\Thread;
use OpenAI\Factory;
use OpenAI\Client;

test('creates and uses an assistant with file analysis', function () {
    $client = OpenAI::factory()
        ->withApiKey('')
        ->withOrganization('anthropic')
        ->withProvider('claude')
        ->withProject('anthropic')
        ->make();
    
    // Create a test file
    $fileContent = "# Sales Report 2024\n\n" .
                  "Q1 Sales: $1,000,000\n" .
                  "Q2 Sales: $1,200,000\n" .
                  "Q3 Sales: $800,000\n" .
                  "Q4 Sales: $1,500,000";
    
    $tempFile = tempnam(sys_get_temp_dir(), 'sales_report');
    file_put_contents($tempFile, $fileContent);
    
    // Upload file
    $file = $client->files()->create([
        'file' => fopen($tempFile, 'r'),
        'purpose' => 'assistants'
    ]);
    
    // Create assistant
    $assistant = $client->assistants()->create([
        'name' => 'Sales Analyst',
        'instructions' => 'You are a helpful sales analyst. Analyze sales reports and provide insights.',
        'model' => 'claude-3-opus-20240229',
        'tools' => [
            ['type' => 'retrieval'],
            ['type' => 'code_interpreter']
        ],
        'file_ids' => [$file->id]
    ]);
    
    expect($assistant)->toBeInstanceOf(Assistant::class);
    expect($assistant->file_ids)->toContain($file->id);
    
    // Create thread
    $thread = $client->threads()->create();
    expect($thread)->toBeInstanceOf(Thread::class);
    
    // Add message to thread
    $message = $client->threads()->messages()->create($thread->id, [
        'role' => 'user',
        'content' => 'What was the total sales for the year?'
    ]);
    
    // Run the assistant
    $run = $client->threads()->runs()->create($thread->id, [
        'assistant_id' => $assistant->id
    ]);
    
    expect($run->status)->toBeIn(['queued', 'in_progress', 'completed']);
    
    // Clean up
    $client->files()->delete($file->id);
    $client->assistants()->delete($assistant->id);
    $client->threads()->delete($thread->id);
    unlink($tempFile);
}); 