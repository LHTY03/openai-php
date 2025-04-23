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
         <?php

    t('returns a chat completion from perplexity', function () {
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

