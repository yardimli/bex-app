## About Bex App

Bex App is a platform designed to maximize the users productivity. It is built using Laravel, a popular PHP framework.

### Setup

- Clone the repository:
- Create a `.env` file from the `.env.example` file:
- Edit the `.env` file for the database connection:
- Create account on google for text to speech.
- Create account on openrouter for LLM.
- Create account on openai for LLM.
- Create account on fal.ai for Image generation.
- - Edit the `.env` file add all the API keys and JSON file for google.


- Run the following commands to set up the rest:
    - `composer install`
    - `npm install`
    - `npm run build`
    - `php artisan migrate`
    - `php artisan storage:link`

`php artisan serve --port 8011`

to start the server.
