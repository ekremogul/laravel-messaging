# Messaging package for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ekremogul/laravel-messaging.svg?style=flat-square)](https://packagist.org/packages/ekremogul/laravel-messaging)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/ekremogul/laravel-messaging/run-tests?label=tests)](https://github.com/ekremogul/laravel-messaging/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/ekremogul/laravel-messaging/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/ekremogul/laravel-messaging/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ekremogul/laravel-messaging.svg?style=flat-square)](https://packagist.org/packages/ekremogul/laravel-messaging)

[![Messaging package for Laravel](https://banners.beyondco.de/Laravel%20Messasing.png?theme=light&packageManager=composer+require&packageName=ekremogul+%2F+laravel-messaging&pattern=architect&style=style_1&description=Messaging+package+for+Laravel&md=1&showWatermark=1&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)](https://packagist.org/packages/ekremogul/laravel-messaging)

## Installation

You can install the package via composer:

```bash
composer require ekremogul/laravel-messaging
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="messaging-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="messaging-config"
```

This is the contents of the published config file:

```php
return [
    "user_model" => "App\Models\User"
];
```

## Usage

#### Definition
```php
$laravelMessaging = Ekremogul\LaravelMessaging\LaravelMessaging::create();
```

#### Get Inbox
```
$inbox = $laravelMessaging($order = "desc", $offset = 0, $take = 20); // Retrieve the last 20 messages

foreach($inbox as $item) {
    /*
    * App\Models\User 
    * return the messaging user
    */
    $item->withUser;
    
    /*
    * Ekremogul\LaravelMessaging\Model\Message
    * return last message with user
    */ 
    $item->message;
    
    /*
    * Return 0 or 1
    * Returns 1 if there is a message you haven't read
    */
    $item->unreaded_message;
    
    /*
    * Return integer
    * Returns the total number of unread messages
    */
    $item->total_unread;
}
```
#### Recieve message with user
```
$laravelMessaging->getMessagesWithUser($user_id, $offset = 0, $take = 20);
```
#### Send message
```
$laravelMessaging->sendMessage($user_id, $message);
```
#### Make seen specific message
```
$laravelMessaging->makeSeen($message_id);
```
#### Make specific message seen
```
$laravelMessaging->makeSeen($message_id);
```
#### Make all message make with the user seen
```
$laravelMessaging->makeSeenAll($user_id);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ekrem OĞUL](https://github.com/ekremogul)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
