<?php

use OpenAI\Responses\Images\CreateResponse;
use OpenAI\Resources\Images;
use OpenAI\Factory;
use OpenAI\Client;
use OpenAI\Exceptions\InvalidArgumentException;

it("returns an image in b64 json format", function () {
    $client = OpenAI::factory()
        ->withApiKey("")
        ->withOrganization("brainiest-testing")
        ->withProvider("grok")
        ->withProject("brainiest-testing")
        ->make();

    $response = $client->images()->create([
        "model" => "grok-2-image",
        "prompt" => "A cute baby sea otter",
        "n" => 1,
        "response_format" => "b64_json",
    ]);

    expect($response)->toBeInstanceOf(CreateResponse::class);
    expect($response->id)->not->toBeEmpty();

    $response->created; // 1589478378

    foreach ($response->data as $data) {
        expect($data->b64_json)->not->toBeEmpty();
    }

    $response->toArray();
});

it("returns an image in url format", function () {
    $client = OpenAI::factory()
        ->withApiKey("")
        ->withOrganization("brainiest-testing")
        ->withProvider("grok")
        ->withProject("brainiest-testing")
        ->make();

    $response = $client->images()->create([
        "model" => "grok-2-image",
        "prompt" => "A cute baby sea otter",
        "n" => 2,
        "response_format" => "url",
    ]);

    #$response->created; // 1589478378
    expect($response)->toBeInstanceOf(CreateResponse::class);
    expect($response->id)->not->toBeEmpty();

    foreach ($response->data as $data) {
        expect($data->url)->toBeString(); // 'https://oaidalleapiprodscus.blob.core.windows.net/private/...'
        $data->b64_json; // null
    }

    #$response->toArray(); // ['created' => 1589478378, data => ['url' => 'https://oaidalleapiprodscus...', ...]]
});

it("handles multiple images correctly", function () {
    $client = OpenAI::factory()
        ->withApiKey("")
        ->withOrganization("brainiest-testing")
        ->withProvider("grok")
        ->withProject("brainiest-testing")
        ->make();

    $response = $client->images()->create([
        "model" => "grok-2-image",
        "prompt" => "A fantasy landscape with dragons",
        "n" => 3,
        "response_format" => "url",
    ]);
})
//edge case test

it("throws an exception for an unsupported response_format", function () {
    $client = OpenAI::factory()
        ->withApiKey("")
        ->withOrganization("brainiest-testing")
        ->withProvider("grok")
        ->withProject("brainiest-testing")
        ->make();

    expect(function () use ($client) {
        $client->images()->create([
            "model"           => "grok-2-image",
            "prompt"          => "Edge case test",
            "n"               => 1,
            "response_format" => "unsupported_format",
        ]);
    })->toThrow(InvalidArgumentException::class);
});


// edge case: no messages array provided
it("throws an exception when messages parameter is missing", function () {
    $client = OpenAI::factory()
        ->withApiKey("")
        ->withOrganization("brainiest-testing")
        ->withProvider("grok")
        ->withProject("brainiest-testing")
        ->make();

    expect(function () use ($client) {
        $client->chat()->create([
            "model" => "gpt-4o",
            // missing "messages" key entirely
        ]);
    })->toThrow(InvalidArgumentException::class);
});
