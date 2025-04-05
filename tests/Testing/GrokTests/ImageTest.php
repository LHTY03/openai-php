<?php

use OpenAI\Responses\Images\CreateResponse;
use OpenAI\Resources\Images;
use OpenAI\Factory;
use OpenAI\Client;

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
