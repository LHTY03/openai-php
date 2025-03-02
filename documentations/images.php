<?php

require 'vendor/autoload.php';

use GeminiAPI\Client as GeminiClient;
use GeminiAPI\Resources\ModelName as GeminiModelName;
use GeminiAPI\Resources\Parts\TextPart as GeminiTextPart;
use AIpi\Thread as AIpiThread;
use AIpi\Message as AIpiMessage;
use AIpi\MessageType as AIpiMessageType;

// Configuration
$geminiApiKey = 'YOUR_GEMINI_API_KEY';
$grokApiKey = 'YOUR_GROK_API_KEY';

// Function to generate an image using Gemini
function generateImageWithGemini($prompt, $apiKey)
{
    try {
        $client = new GeminiClient($apiKey);
        $response = $client->generativeModel(GeminiModelName::GEMINI_PRO)->generateContent(
            new GeminiTextPart($prompt)
        );
        return $response->text();
    } catch (Exception $e) {
        return 'Error generating image with Gemini: ' . $e->getMessage();
    }
}

// Function to generate an image using Grok
function generateImageWithGrok($prompt, $apiKey)
{
    try {
        $thread = new AIpiThread('xai-grok-vision-beta', $apiKey);
        $thread->AddMessage(new AIpiMessage($prompt, AIpiMessageType::TEXT));
        $message = $thread->Run();
        if ($message) {
            return $message->content;
        } else {
            return 'Error: ' . $thread->GetLastError();
        }
    } catch (Exception $e) {
        return 'Error generating image with Grok: ' . $e->getMessage();
    }
}

// Main execution
$prompt = 'A serene landscape with mountains during sunrise';

// Generate image with Gemini
$geminiResult = generateImageWithGemini($prompt, $geminiApiKey);
echo "Gemini Result:\n$geminiResult\n\n";

// Generate image with Grok
$grokResult = generateImageWithGrok($prompt, $grokApiKey);
echo "Grok Result:\n$grokResult\n\n";

?>

