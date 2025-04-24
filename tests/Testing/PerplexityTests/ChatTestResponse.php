<?php
 
use OpenAI\Resources\Chat\CreateResponse;
use OpenAI\Factory;
use OpenAI\Client;
 
$client = OpenAI::factory()
    ->withApiKey(PERPLEXITY_API_KEY)
    ->withOrganization('brainiest-testing')
    ->withProvider('perplexity')
    ->withProject('brainiest-testing')
    ->make();

it('returns a chat completion from perplexity', function () {
    $client = OpenAI::factory()
        ->withApiKey('test-api-key')
        ->withOrganization('brainiest-testing')
        ->withProvider('perplexity') // Your custom fork
        ->withProject('brainiest-testing')
        ->make();

    $response = $client->chat()->create([
        'model' => 'perplexity/gpt-4',
        'messages' => [
            ['role' => 'user', 'content' => 'Who won the world cup in 2018?'],
        ],
    ]);

    expect($response)->toBeInstanceOf(CreateResponse::class);
    expect($response->id)->not->toBeEmpty();

    $reply = $response->choices[0]->message->content;

    expect($reply)->toBeString();
    expect($reply)->toContain('France')->or()->toContain('2018');
});

test('generates multiple completions when n > 1', function () {
    $client = makeClient();
    $response = $client->chat()->create([
        'model'    => 'perplexity/gpt-4',
        'messages' => [
            ['role' => 'user', 'content' => 'Say "hello".'],
        ],
        'n'       => 3,
    ]);
    
    // Expect exactly 3 choices
    expect(count($response->choices))->toBe(3);
    
    // Each choice should have a string content
    foreach ($response->choices as $choice) {
        expect($choice->message->content)->toBeString();
    }
});


test('response object property is chat.completion', function () {
$client = makeClient();
$response = $client->chat()->create([
    'model'    => 'perplexity/gpt-4',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!'],
    ],
]);

// Ensure the API labelled this correctly
expect($response->object)->toBe('chat.completion');
});


test('accepts sampling parameters temperature and top_p', function () {
    $client = makeClient();
    $response = $client->chat()->create([
        'model'       => 'perplexity/gpt-4',
        'messages'    => [
            ['role' => 'user', 'content' => 'What is the capital of France?'],
        ],
        'temperature' => 0.7,
        'top_p'       => 0.9,
    ]);

    expect($response)->toBeInstanceOf(CreateResponse::class);
    // The content should still be present
    expect(isset($response->choices[0]->message->content))->toBeTrue();
});

test('maintains conversation context across multiple messages', function () {
    $client = makeClient();
    
    $response = $client->chat()->create([
        'model' => 'perplexity/gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant who speaks French.'],
            ['role' => 'user', 'content' => 'Say "Hello, how are you?" in French'],
            ['role' => 'assistant', 'content' => 'Bonjour, comment allez-vous?'],
            ['role' => 'user', 'content' => 'Now ask me what my name is, still in French']
        ],
        'temperature' => 0.7
    ]);

    expect($response)->toBeInstanceOf(CreateResponse::class);
    expect($response->choices[0]->message->content)->toBeString();
    
    // The response should be in French and contain common French question words for asking someone's name
    expect($response->choices[0]->message->content)
        ->toContain('Comment')
        ->or()->toContain('Quel')
        ->and()->toContain('nom');
        
    // Verify message structure
    expect($response->choices[0]->message->role)->toBe('assistant');
    expect($response->choices[0]->finish_reason)->toBe('stop');
    
    // Verify usage information
    expect($response->usage->promptTokens)->toBeGreaterThan(0);
    expect($response->usage->completionTokens)->toBeGreaterThan(0);
    expect($response->usage->totalTokens)
        ->toBe($response->usage->promptTokens + $response->usage->completionTokens);
});

