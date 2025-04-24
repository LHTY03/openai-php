<?php

use OpenAI\Resources\Embeddings\CreateResponse;
use OpenAI\Factory;
use OpenAI\Client;

test('generates embeddings for vector search', function () {
    $client = makeClient();
    
    $response = $client->embeddings()->create([
        'model' => 'text-embedding-3-small',
        'input' => 'The quick brown fox jumps over the lazy dog',
        'encoding_format' => 'float'
    ]);
    
    expect($response)->toBeInstanceOf(CreateResponse::class);
    expect($response->data)->toBeArray();
    expect($response->data[0]->embedding)->toBeArray();
    expect(count($response->data[0]->embedding))->toBe(1536); // Dimension size for text-embedding-3-small
});

test('handles batch embedding generation', function () {
    $client = makeClient();
    
    $texts = [
        'The quick brown fox jumps over the lazy dog',
        'Pack my box with five dozen liquor jugs',
        'How vexingly quick daft zebras jump'
    ];
    
    $response = $client->embeddings()->create([
        'model' => 'text-embedding-3-small',
        'input' => $texts,
        'encoding_format' => 'float'
    ]);
    
    expect($response->data)->toHaveCount(3);
    foreach ($response->data as $embedding) {
        expect($embedding->embedding)->toBeArray();
        expect(count($embedding->embedding))->toBe(1536);
    }
});

test('performs similarity search with embeddings', function () {
    $client = makeClient();
    
    // Generate embeddings for a collection of texts
    $documents = [
        'The weather is sunny today',
        'The forecast shows rain tomorrow',
        'The temperature is very hot',
        'There might be snow next week'
    ];
    
    // Get embeddings for all documents
    $embeddings = $client->embeddings()->create([
        'model' => 'text-embedding-3-small',
        'input' => $documents,
        'encoding_format' => 'float'
    ]);
    
    // Get embedding for query
    $query = 'What\'s the weather like?';
    $queryEmbedding = $client->embeddings()->create([
        'model' => 'text-embedding-3-small',
        'input' => $query,
        'encoding_format' => 'float'
    ]);
    
    // Simulate vector similarity search (cosine similarity)
    $similarities = [];
    foreach ($embeddings->data as $index => $docEmbedding) {
        $similarity = cosineSimilarity(
            $queryEmbedding->data[0]->embedding,
            $docEmbedding->embedding
        );
        $similarities[$index] = $similarity;
    }
    
    // Sort by similarity
    arsort($similarities);
    $topResults = array_slice($similarities, 0, 2, true);
    
    expect(count($topResults))->toBe(2);
    // The weather-related documents should have higher similarity
    expect(array_keys($topResults))->toContain(0); // "The weather is sunny today"
});

// Helper function to calculate cosine similarity
function cosineSimilarity(array $a, array $b): float {
    $dotProduct = 0;
    $normA = 0;
    $normB = 0;
    
    for ($i = 0; $i < count($a); $i++) {
        $dotProduct += $a[$i] * $b[$i];
        $normA += $a[$i] * $a[$i];
        $normB += $b[$i] * $b[$i];
    }
    
    $normA = sqrt($normA);
    $normB = sqrt($normB);
    
    return $dotProduct / ($normA * $normB);
}

test('handles errors in embedding generation', function () {
    $client = makeClient();
    
    expect(function () use ($client) {
        $client->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => '' // Empty input should cause an error
        ]);
    })->toThrow(\Exception::class);
}); 