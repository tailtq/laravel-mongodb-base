# LARAVEL RESTFul APIs Boilerplate

## About

The boilerplate has been build with a purpose to help developers develop an API module quickly.

## Feature supports


## Framework & Package we used

**framework**

- Laravel 7.4

**packages**

- Passports

- laravel-json-api

- laravel-permission


## Setup & Run

**setup**

1. Install package passport 

```
php artisan passport:install

```

After running this command, add the Laravel\Passport\HasApiTokens trait to your App\User model.

Next, you should call the Passport::routes method within the boot method of your AuthServiceProvider

Finally, in your config/auth.php configuration file, you should set the driver option of the api authentication guard to passport.


## Contributing