# Muzeek SDK for PHP

This repository contains the open source Muzeek SDK that allows you to access the Muzeek API from your PHP app.

## Installation

The Muzeek PHP SDK can be installed with [Composer](https://getcomposer.org/). Run this command:

```sh
composer require muzeek-sdk/php-sdk
```

## Usage

Simple Muzeek Query Example

```php
require_once __DIR__ . '/vendor/autoload.php'; // change path as needed

$api = new Muzeek(["app_token" => $app_token]);

$haserror = true;
// -- login if needed
if ($genres = $api->genres()) {

  // -- pick a random genre
  $genre = array_rand($genres);

  // -- create a search query
  $query = $api->makeQuery($genre, $subgenre = null, $title = null, $tags = null);

  // -- request a standard generated music
  if ($idcard = $api->generate($query)) {
    $haserror = false;

    // -- use the data
    printIDCard($idcard);
  }
}
```

## License

Please see the [license file](https://github.com/muzeek/php-sdk/blob/master/LICENSE) for more information.

## Security Vulnerabilities

If you have found a security issue, please contact the support team directly at [support@muzeek.co](mailto:support@muzeek.co).
