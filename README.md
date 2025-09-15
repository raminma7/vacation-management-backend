Back end section of a mini vacation request system.

Technologies used for this section:

    - Docker - Laravel/sail
    - Laravel/Sanctum for API
    - Laravel/Breeze

Steps to start the project:

1 - composer require laravel/sail --dev

2 - php artisan sail:install

3 - ./vendor/bin/sail up -d  (**make sure ports are free**)

4 - ./vendor/bin/sail artisan migrate

5 - ./vendor/bin/sail npm install

6 - ./vendor/bin/sail npm run dev
