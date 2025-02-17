Gemini‑API for Chat Completions Documentation
The Gemini‑API is designed to offer conversational AI capabilities similar to those provided by the OpenAI Chat Completions endpoint. While the core functionality remains analogous, the Gemini‑API introduces several differences in endpoints, authentication, and parameter nomenclature. This document explains how to integrate with Gemini‑API, how to modify your existing openai‑php codebase to support it, and best practices for usage.

1. Overview
Gemini‑API enables you to generate chat-based responses using advanced language models (e.g., gemini-1, gemini-2, etc.). The API accepts structured conversation inputs and returns completions in a JSON format. Its design philosophy is similar to OpenAI’s API, so developers familiar with the openai‑php library will find it straightforward to adapt their integrations.

Key differences compared to OpenAI’s API include:

Base Endpoint URL: The Gemini‑API uses a distinct base URL (e.g., https://api.gemini.com/v1) rather than the OpenAI endpoint.
Authentication Mechanism: While both use Bearer tokens, you must replace your OpenAI key with a Gemini‑API key.
Parameter Naming & Defaults: Some parameters may be renamed or have different defaults. For example, whereas OpenAI might use temperature, Gemini‑API may support additional fine-tuning parameters.
Response Schema: The structure of the completion response may include Gemini‑specific metadata, error codes, or performance indicators.


3. Configuration Changes
3.1. Updating the Base URL
In the original openai‑php library, the API base URL is defined (commonly in a configuration file or a constant within a core class). To target Gemini‑API, change the endpoint URL from:

php
Copy
Edit
const BASE_URL = 'https://api.openai.com/v1/';
to:

php
Copy
Edit
const BASE_URL = 'https://api.gemini.com/v1/';
This ensures that all subsequent API calls are directed to Gemini’s servers.

3.2. Authentication and API Keys
Replace any references to your OpenAI API key with the Gemini‑API key. For example, if your client instantiation looks like:

php
Copy
Edit
$openai = new OpenAI('YOUR_OPENAI_API_KEY');
modify it as follows:

php
Copy
Edit
$gemini = new GeminiAPI('YOUR_GEMINI_API_KEY');
Ensure that your HTTP headers are updated accordingly. A typical authentication header might be:

php
Copy
Edit
'Authorization: Bearer ' . $this->apiKey,
This remains largely the same, but the token value should now be the Gemini‑API key.

4. Request Structure for Chat Completions
4.1. Endpoint
For chat completions, the Gemini‑API typically exposes an endpoint similar to:

bash
Copy
Edit
POST https://api.gemini.com/v1/chat/completions
This is analogous to the OpenAI endpoint but uses Gemini‑specific routing.

4.2. Request Body Parameters
When adapting from OpenAI’s structure, consider the following key parameters:

model:

OpenAI: Typically "gpt-3.5-turbo" or "gpt-4".
Gemini: Use the Gemini‑model identifier such as "gemini-1" or "gemini-2".
messages:
An array of message objects. Each message is usually structured with keys such as:

json
Copy
Edit
{
  "role": "system", // can be "system", "user", or "assistant"
  "content": "Your message text."
}
Gemini‑API may accept the same structure or require slight modifications (e.g., an additional key like timestamp or sessionId for enhanced tracking).

temperature:
Controls randomness in the response. Gemini‑API uses the same concept but may have different default ranges or limits.

top_p, frequency_penalty, presence_penalty:
These parameters control response variety and repetition. Verify if Gemini‑API supports these parameters or if they have alternative names.

stream:
If Gemini‑API supports real-time streaming, you may be able to set "stream": true. Check the documentation for any nuances regarding the streaming response format.

custom Gemini parameters:
Gemini‑API might introduce extra optional parameters such as:

context_window: Specify the size of the context window.
retry_policy: Options to handle timeouts or errors more gracefully.
model_version: To choose between various Gemini releases.
4.3. Example Request Body (JSON)
Below is an example of a JSON payload for a chat completion request using Gemini‑API:

json
Copy
Edit
{
  "model": "gemini-1",
  "messages": [
    {
      "role": "system",
      "content": "You are a helpful assistant."
    },
    {
      "role": "user",
      "content": "Tell me about the Gemini API."
    }
  ],
  "temperature": 0.7,
  "top_p": 0.9,
  "stream": false
}
This request is nearly identical to what you’d send to OpenAI, aside from the model name and any additional Gemini‑specific parameters.

5. Response Structure
A successful Gemini‑API response will generally follow a structure similar to the OpenAI response. Expect a JSON object with fields such as:

id: A unique identifier for the request.
object: The type of object returned (e.g., "chat.completion").
created: A timestamp indicating when the response was generated.
model: The Gemini‑model used for the response.
choices: An array containing the generated completions. Each choice may include:
message: The message object with the response text.
finish_reason: Reason for completion termination (e.g., "stop", "length").
index: Position in the array.
usage: Information about token usage, which might differ slightly in Gemini‑API (for example, including additional metrics).
Developers should refer to the Gemini‑API documentation for any extra fields that may be provided or for differences in naming conventions.