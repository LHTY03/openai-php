# Customizing the OpenAI PHP Repository to Incorporate Other APIs (e.g., Gemini)

This document explains how you can modify the OpenAI PHP repository to integrate other AI provider APIs such as Gemini. The primary focus is on adjusting the factory class and other resource classes to change the destination and configuration from OpenAI to Gemini.

---

## 1. Overview of the Repository Architecture

The repository is typically organized in a modular way with the following key components:

- **Factory Class:**  
  Responsible for creating instances of various API resource classes (e.g., Chat, Completions, Images). This class also configures the HTTP client (setting base URI, default headers, etc.).

- **Resource Classes:**  
  Each resource class (e.g., `Chat.php`) wraps a specific API endpoint. They use the client provided by the factory class to send HTTP requests.

- **Client Configuration:**  
  The HTTP client is set up with parameters such as the API key, base URL, headers, and other common settings.

---

## 2. Goals for Customization

To switch from using the OpenAI API to another API provider (e.g., Gemini), you will need to:

1. **Update the Base URI and Endpoints:**  
   Change the destination of API calls from OpenAI’s endpoints (e.g., `https://api.openai.com/v1`) to Gemini’s endpoints (e.g., `https://api.gemini.com/v1` or as specified in the Gemini API documentation).

2. **Modify HTTP Headers and Authentication:**  
   If Gemini requires different headers (or different header values) for authentication or content type, update these in the client configuration.

3. **Adjust Resource Class Methods:**  
   Modify methods (in files such as `chat.php`) to reflect any differences in parameters, endpoints, or request/response structure required by Gemini.

4. **Revise the Factory Class:**  
   Ensure that the factory class creates resource instances that use the correct configuration (e.g., base URI, API key, headers) for Gemini.

---

## 3. Step-by-Step Customization Guide

### Step 1: Locate and Modify the Factory Class

- **Identify the Factory Class:**  
  Look for a class (commonly named something like `ApiFactory.php` or `OpenAiFactory.php`) that is responsible for instantiating resource classes and configuring the HTTP client.

- **Update the Base URI:**  
  Replace the OpenAI base URI with Gemini’s base URI.  
  **Example Change:**
  ```php
  // Before (OpenAI)
  $this->baseUri = 'https://api.openai.com/v1';
  
  // After (Gemini)
  $this->baseUri = 'https://api.gemini.com/v1';

Adjust Authentication and Headers:
Update the default headers if Gemini uses a different authentication method or additional headers.

// Before (OpenAI)
$this->headers = [
    'Authorization' => 'Bearer ' . $apiKey,
    'Content-Type'  => 'application/json'
];

// After (Gemini)
$this->headers = [
    'Authorization' => 'Bearer ' . $apiKey,  // Use Gemini API key if different
    'Content-Type'  => 'application/json'
    // Add or modify any Gemini-specific headers here
];

### Step 2: Update Resource Classes
Identify Resource Classes:
Files like chat.php, completions.php, etc., contain methods that build requests for specific endpoints.

Change Endpoint Paths:
For example, if the OpenAI Chat resource uses /chat/completions, you may need to update it to Gemini’s equivalent endpoint.

Modify Request Parameters if Needed:
Compare the parameter requirements between OpenAI and Gemini. For instance, parameter names or payload structure might differ. Update your method signatures and payload building logic to reflect Gemini’s requirements.

### Step 3: Refactor Common Functionality
Abstract Common Methods:
If multiple resource classes share similar logic for handling responses or errors, consider moving that logic into a base resource class.
This will make future updates easier if Gemini introduces changes across multiple endpoints.

Configuration Options:
Allow configuration via environment variables or a configuration file so you can easily switch between providers (e.g., use API_PROVIDER set to openai or gemini).

### Step 4: Testing and Validation
Unit Testing:
Update or create unit tests for the modified classes to ensure that the HTTP client is calling the correct endpoints with the right parameters and headers.

Integration Testing:
Test the entire flow (e.g., sending a chat message) against Gemini’s sandbox or production environment to validate that responses are handled correctly.

Logging and Error Handling:
Verify that error responses from Gemini are logged properly, and that your code can handle any differences in response structure.