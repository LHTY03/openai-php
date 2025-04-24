# How to use the new Functions

## With Provider

With provider is a new service that we added in for users to play with. When we are creating a client, instead of
naming the baseURL, we could name it through the LLM that we are selecting. so with the new withProvider service,
users could select different LLM's base urls through simply typing in the names of these LLM providers.

For the example below, instead of providing the baseURL for each LLM, we could simply add in the withProvider tag 
to specify the baseURL for a specific LLM provider.
```php
$client = OpenAI::factory()
    ->withApiKey($_ENV["GROK_API_KEY"])
    ->withOrganization('your-organization') // default: null
    ->withProject('Your Project') // default: null
    ->withProvider('Grok') //->withBaseUri('https://api.x.ai/v1')
    ->make();
```

This function currently supports the following providers:
- Grok
- Gemini
- Perplexity
- OpenAI

We would work on expanding this functionality to more providers as we incorporate the different providers into the 
availability of the library.
