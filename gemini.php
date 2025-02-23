<?php

// Replace with your actual API key
$api_key = 'YOUR_API_KEY';

// Base URL for the Gemini API
$base_url = 'https://generativelanguage.googleapis.com/v1beta/models/';

// Model names
$text_model = 'gemini-pro:generateContent';
$image_model = 'gemini-pro-vision:generateContent';

// Function to make API requests
function callGeminiAPI($url, $api_key, $payload) {
    $curl = curl_init($url . '?key=' . $api_key);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        return ['error' => 'cURL error: ' . curl_error($curl)];
    }

    curl_close($curl);

    return json_decode($response, true);
}

// Text prompts
$text_prompts = [
    'Write a short poem about a cat.',
    'Explain the theory of relativity in simple terms.',
    'Summarize the plot of Hamlet.',
];

// Image prompts (using dummy image data)
$image_prompts = [
    [
        'parts' => [
            ['text' => 'What is in this image?'],
            [
                'inlineData' => [
                    'mimeType' => 'image/jpeg',
                    'data' => base64_encode(file_get_contents('placeholder.jpg')), // Replace 'placeholder.jpg' with an actual image.
                ],
            ],
        ],
    ],
    [
      'parts' => [
          ['text' => 'Describe this scene'],
          [
              'inlineData' => [
                  'mimeType' => 'image/jpeg',
                  'data' => base64_encode(file_get_contents('placeholder2.jpg')), // Replace 'placeholder2.jpg' with an actual image.
              ],
          ],
      ],
    ],
];

// Text API calls
echo "<h2>Text Responses:</h2>";
foreach ($text_prompts as $prompt) {
    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt],
                ],
            ],
        ],
    ];

    $response = callGeminiAPI($base_url . $text_model, $api_key, $payload);

    if (isset($response['error'])) {
        echo "<p>Error: " . $response['error'] . "</p>";
    } else {
        echo "<p><strong>Prompt:</strong> " . htmlspecialchars($prompt) . "</p>";
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            echo "<p><strong>Response:</strong> " . nl2br(htmlspecialchars($response['candidates'][0]['content']['parts'][0]['text'])) . "</p>";
        } else {
            echo "<p>No response text found.</p>";
        }
    }
}

// Image API calls
echo "<h2>Image Responses:</h2>";
foreach ($image_prompts as $prompt) {

    $payload = [
        'contents' => [
            $prompt,
        ],
    ];

    $response = callGeminiAPI($base_url . $image_model, $api_key, $payload);

    if (isset($response['error'])) {
        echo "<p>Error: " . $response['error'] . "</p>";
    } else {
        echo "<p><strong>Image Prompt:</strong> " . htmlspecialchars($prompt['parts'][0]['text']) . "</p>";
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            echo "<p><strong>Response:</strong> " . nl2br(htmlspecialchars($response['candidates'][0]['content']['parts'][0]['text'])) . "</p>";
        } else {
            echo "<p>No response text found.</p>";
        }
    }
}
?>
