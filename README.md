# MatchTune SDK for PHP

This repository contains the open source MatchTune SDK that allows you to access the MatchTune API from your PHP app.

## Installation

The MatchTune PHP SDK can be installed with [Composer](https://getcomposer.org/). Run this command:

```sh
composer require matchtune-sdk/php-sdk
```

## Usage

Simple MatchTune Query Example

```php
require_once __DIR__ . '/vendor/autoload.php'; // change path as needed

use MatchTune\MatchTune;

$api = new MatchTune(["app_token" => $app_token]);

$haserror = true;
// -- Load genre & subgenre list
if ($genres = $api->genres()) {

  // -- pick a random genre
  $genre = $genres[array_rand($genres)];

  // -- create a search query
  $query = $api->makeQuery($genre, $title = null, $tags = null);

  // -- request a standard generated music
  if ($idcard = $api->generate($query)) {
    $haserror = false;

    // -- use the data
    printIDCard($idcard);
  }
}
```

## Api documentation

All mechanisms developed here are documented on our [REST API documentation](https://api-doc.matchtune.com/).

## License

Please see the [license file](https://github.com/matchtune-sdk/php-sdk/blob/master/LICENSE) for more information.

## Security Vulnerabilities

If you have found a security issue, please contact the support team directly at [support@matchtune.com](mailto:support@matchtune.com).
