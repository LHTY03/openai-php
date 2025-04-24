<?php

use OpenAI\Resources\Chat\CreateResponse;
use OpenAI\Factory;
use OpenAI\Client;

it('returns a response', function () {
    $GROK_API_KEY="";
    $client = OpenAI::factory()
        ->withApiKey($GROK_API_KEY)
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
        
    $result = $client->chat()->create([
        'model' => 'grok-2-latest',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello! How are you?'],
        ],
    ]);
    
    # expect that the result is an instance of CreateResponse
    #expect($result)->toBeInstanceOf(CreateResponse::class);
    
    #expect the response to be not empty
    expect($result['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('accepts a system role message and returns a response', function () {
    $GROK_API_KEY="";
    $client = OpenAI::factory()
        ->withApiKey($GROK_API_KEY)
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
        
    $result = $client->chat()->create([
        'model' => 'grok-2-latest',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => 'Hello! How are you?']
        ],
    ]);
    
    # expect that the result is an instance of CreateResponse
    #expect($result)->toBeInstanceOf(CreateResponse::class);
    
    #expect the response to be not empty
    expect($result['choices'][0]['message']['role'])->toBe('assistant');
    expect($result['choices'][0]['message']['content'])->not->toBeEmpty();
});

it('returns a streamed chat response', function () {
    $GROK_API_KEY="";
    $client = OpenAI::factory()
        ->withApiKey($GROK_API_KEY)
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();
        
    $stream = $client->chat()->createStreamed([
            'model' => 'grok-2-latest',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
    ]);
        
    foreach($stream as $response){
        $arr = $response->choices[0]->toArray();       
        expect($arr['index'])->toBeInt();
        expect($arr['delta']['content'])->toBeString();
        expect($arr['delta']['role'])->toBe('assistant');
    }
    
});

it('returns chat completion with function call', function () {
    $client = OpenAI::factory()
    ->withApiKey('')
    ->withOrganization('brainiest-testing')
    ->withProvider('grok')
    ->withProject('brainiest-testing')
    ->make();
    
    $response = $client->chat()->create([
        'model' => 'grok-2-1212',
        'messages' => [
            ['role' => 'user', 'content' => 'What\'s the weather like in Boston?'],
        ],  
        'functions' => [
            [
                'name' => 'get_current_weather',
                'description' => 'Get the current weather in a given location',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The city and state, e.g. San Francisco, CA',
                        ],
                        'unit' => [
                            'type' => 'string',
                            'enum' => ['celsius', 'fahrenheit']
                        ],
                    ],
                    'required' => ['location'],
                ],
            ]
        ]
    ]); 
        
    expect($response->id)->toBeString();
    #$response->object;
    #$response->created;
    expect($response->model)->toBeString();
        
    foreach ($response->choices as $choice) {
        $choice->index;
        expect($choice->message->role)->toBe('assistant'); // 'assistant'
        $choice->message->content; // null
        expect($choice->message->functionCall->name)->toBeString(); // 'get_current_weather'
        $choice->message->functionCall->arguments; // "{\n  \"location\": \"Boston, MA\"\n}"
        $choice->finishReason; // 'function_call'
    }   
        
    expect($response->usage->promptTokens)->toBeInt(); // 82,
    expect($response->usage->completionTokens) -> toBeInt(); // 18,
    expect($response->usage->totalTokens)->toBeInt(); // 100
    
});

it('returns chat completion with a tool call', function () {
    $client = OpenAI::factory()
    ->withApiKey('')
    ->withOrganization('brainiest-testing')
    ->withProvider('grok')
    ->withProject('brainiest-testing')
    ->make();
    
    $response = $client->chat()->create([
        'model' => 'grok-2-1212',
        'messages' => [
            ['role' => 'user', 'content' => 'What\'s the weather like in Boston?'],
        ],
        'tools' => [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_current_weather',
                    'description' => 'Get the current weather in a given location',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'location' => [
                                'type' => 'string',
                                'description' => 'The city and state, e.g. San Francisco, CA',
                            ],
                            'unit' => [
                                'type' => 'string',
                                'enum' => ['celsius', 'fahrenheit']
                            ],
                        ],
                        'required' => ['location'],
                    ],
                ],
            ]
        ]
    ]);
    
    expect($response->id)->toBeString(); // 'chatcmpl-6pMyfj1HF4QXnfvjtfzvufZSQq6Eq'
    $response->object; // 'chat.completion'
    $response->created; // 1677701073
    $response->model; // 'gpt-3.5-turbo-0613'
    
    foreach ($response->choices as $choice) {
        expect($choice->index)->toBeInt(); // 0
        expect($choice->message->role)->toBe('assistant'); // 'assistant'
        $choice->message->content; // null
        $choice->message->toolCalls[0]->id; // 'call_123'
        expect($choice->message->toolCalls[0]->type)->toBe('function'); // 'function'
        expect($choice->message->toolCalls[0]->function->name)->toBe('get_current_weather'); // 'get_current_weather'
        $choice->message->toolCalls[0]->function->arguments; // "{\n  \"location\": \"Boston, MA\"\n}"
        $choice->finishReason; // 'tool_calls'
    }
    
    expect($response->usage->promptTokens)->toBeInt(); // 82,
    expect($response->usage->completionTokens)->toBeInt(); // 18,
    expect($response->usage->totalTokens)->toBeInt(); // 100
    
});

it('returns a response using think_mode reasoning', function () {
    $client = OpenAI::factory()
        ->withApiKey('your-key')
        ->withOrganization('brainiest-testing')
        ->withProvider('grok')
        ->withProject('brainiest-testing')
        ->make();

    $response = $client->chat()->create([
        'model' => 'grok-2-latest',
        'messages' => [
            ['role' => 'user', 'content' => 'What are the long-term economic impacts of AI on developing countries?'],
        ],
        'extra_headers' => [
            'xai:reasoning_mode' => 'think_mode'
        ]
    ]);

    expect($response['choices'][0]['message']['role'])->toBe('assistant');
    expect($response['choices'][0]['message']['content'])->not->toBeEmpty();
});
