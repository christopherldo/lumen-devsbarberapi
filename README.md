# How to RUN

* ```mv .env.example .env``` to copy the env example to a real .env;
* Go to .env file and configure your database (DB section);
* If you want to use coordinates, you might put your Google Geolocation API key on variable MAPS_KEY on .env file;
* ```composer install``` or ```php composer.phar install``` to install all needed libraries;
* ```php artisan jwt:secret``` to generate the JWT secret key;
* ```php artisan migrate``` to create database tables and colums;
* ```php -S 127.0.0.1:8000 -t public``` to start the server;
* Make a POST on 127.0.0.1:8000/user with query parameters: name, email, password and password_confirmation - to create your user;
* Get the token it'll send you back and use it on all other requests - On Headers (Authorization: Bearer <_TOKEN_>), or as a query parameter (?token=<_TOKEN_>);

# BONUS

If you want some "random Barbers" to visualize, you can go on ./routes/web.php and uncomment the line 24. Make a GET on 127.0.0.1:8000/random then comment the line again. This way it'll create random barber informations and you can use it to manipulate.
<br>
Thanks everybody. ❤️
