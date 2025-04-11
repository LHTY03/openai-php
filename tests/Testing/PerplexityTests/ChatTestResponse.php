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
