# Composer Install Commands

I changed the composer package in order to make the deploy of the repo easier for the team. With the composer package
installed, we can now easily deploy our package and run our modified forked openai-php/client repo.

## Steps to follow

### 1. Install Composer

When we create a directory, we would want to make sure to install the composer package of our repo, so go in terminal
and type in the following code

```php
composer install lhty03/openai-php:dev-<branch name>
```

Replace <branch name> with the branch that you want to test out and composer should start installing all of the required
php packages that we would need to run the library.

### 2. After Installation
After we install the package, we would need to require **autoload.php** to quote the library in our testing code.
The following code is an example of what do a normal code calling the library looks like

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$client = OpenAI::factory()
    ->withApiKey($_ENV["GEMINI_API_KEY"])
    ->withOrganization('your-organization') // default: null
    ->withProject('Your Project') // default: null
    ->withProvider('Gemini')
    ->make();

$result = $client->chat()->create([
    'model' => 'gemini-2.0-flash',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!, who are you'],
    ],
]);  

echo $result->choices[0]->message->content;
```  

Notice how the format is the exact same to the format of calling the **openai-php/client** library, but what we
implemented is integrating the different models to a factory function that implemented a default url for each provider.

