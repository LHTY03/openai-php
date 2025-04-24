<?php

use OpenAI\Resources\Assistants\Assistant;
use OpenAI\Resources\Threads\Thread;
use OpenAI\Factory;
use OpenAI\Client;

test('creates and manages an assistant', function () {
    $client = makeClient();
    
    // Create an assistant
    $assistant = $client->assistants()->create([
        'name' => 'Math Tutor',
        'instructions' => 'You are a helpful math tutor that helps students solve problems step by step.',
        'model' => 'gpt-4-turbo-preview',
        'tools' => [
            ['type' => 'code_interpreter'],
            ['type' => 'retrieval']
        ]
    ]);
    
    expect($assistant)->toBeInstanceOf(Assistant::class);
    expect($assistant->id)->toBeString();
    expect($assistant->name)->toBe('Math Tutor');
    
    // Modify the assistant
    $updated = $client->assistants()->update($assistant->id, [
        'name' => 'Advanced Math Tutor'
    ]);
    
    expect($updated->name)->toBe('Advanced Math Tutor');
    
    // Delete the assistant
    $deleted = $client->assistants()->delete($assistant->id);
    expect($deleted->deleted)->toBeTrue();
});

test('creates and manages threads with messages', function () {
    $client = makeClient();
    
    // Create a thread
    $thread = $client->threads()->create();
    expect($thread)->toBeInstanceOf(Thread::class);
    
    // Add a message to the thread
    $message = $client->threads()->messages()->create($thread->id, [
        'role' => 'user',
        'content' => 'I need help with calculus.'
    ]);
    
    expect($message->role)->toBe('user');
    expect($message->content[0]->text->value)->toBe('I need help with calculus.');
    
    // List messages in the thread
    $messages = $client->threads()->messages()->list($thread->id);
    expect($messages->data)->toBeArray();
    expect(count($messages->data))->toBe(1);
    
    // Delete the thread
    $deleted = $client->threads()->delete($thread->id);
    expect($deleted->deleted)->toBeTrue();
});

test('runs an assistant with function calling', function () {
    $client = makeClient();
    
    $functions = [
        [
            'name' => 'calculate_area',
            'description' => 'Calculate the area of a geometric shape',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'shape' => [
                        'type' => 'string',
                        'enum' => ['circle', 'square', 'rectangle']
                    ],
                    'dimensions' => [
                        'type' => 'object',
                        'properties' => [
                            'radius' => ['type' => 'number'],
                            'width' => ['type' => 'number'],
                            'height' => ['type' => 'number']
                        ]
                    ]
                ],
                'required' => ['shape', 'dimensions']
            ]
        ]
    ];
    
    // Create assistant with function
    $assistant = $client->assistants()->create([
        'name' => 'Geometry Helper',
        'instructions' => 'You help calculate areas of geometric shapes.',
        'model' => 'gpt-4-turbo-preview',
        'tools' => [
            [
                'type' => 'function',
                'function' => $functions[0]
            ]
        ]
    ]);
    
    // Create thread and message
    $thread = $client->threads()->create();
    $client->threads()->messages()->create($thread->id, [
        'role' => 'user',
        'content' => 'Calculate the area of a circle with radius 5'
    ]);
    
    // Run the assistant
    $run = $client->threads()->runs()->create($thread->id, [
        'assistant_id' => $assistant->id
    ]);
    
    expect($run->status)->toBeIn(['queued', 'in_progress', 'completed']);
    
    // Clean up
    $client->assistants()->delete($assistant->id);
    $client->threads()->delete($thread->id);
});

test('handles file uploads and retrieval', function () {
    $client = makeClient();
    
    // Create a test file
    $fileContent = "This is a test document for the assistant to analyze.";
    $tempFile = tempnam(sys_get_temp_dir(), 'test_doc');
    file_put_contents($tempFile, $fileContent);
    
    // Upload file
    $file = $client->files()->create([
        'file' => fopen($tempFile, 'r'),
        'purpose' => 'assistants'
    ]);
    
    // Create assistant with file
    $assistant = $client->assistants()->create([
        'name' => 'Document Analyzer',
        'instructions' => 'Analyze the provided document and answer questions about it.',
        'model' => 'gpt-4-turbo-preview',
        'tools' => [['type' => 'retrieval']],
        'file_ids' => [$file->id]
    ]);
    
    expect($assistant->file_ids)->toContain($file->id);
    
    // Clean up
    $client->files()->delete($file->id);
    $client->assistants()->delete($assistant->id);
    unlink($tempFile);
}); 