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


- You can also seed the database for sample users using **./vendor/bin/sail artisan db:seed --class=UserSeeder** command.
- For accessing the admin panel, you need to change the role of one of the sample users into **admin** through database. (**Access database through phpmyadmin at localhost:8080**)
