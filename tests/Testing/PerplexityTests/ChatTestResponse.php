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