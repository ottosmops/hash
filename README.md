# Create and verify a hash manifest

[![Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)
[![Latest Stable Version](https://img.shields.io/badge/Version-stable-blue.svg?format=flat-square)](https://packagist.org/packages/ottosmops/hash)
[![Build Status](https://travis-ci.com/ottosmops/hash.svg?branch=master)](https://travis-ci.com/ottosmops/hash)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/248db8b3-4969-48c5-9a61-9c7346832ff0/mini.png)](https://insight.sensiolabs.com/projects/248db8b3-4969-48c5-9a61-9c7346832ff0)
[![Packagist Downloads](https://img.shields.io/packagist/dt/ottosmops/hash.svg?style=flat-square)](https://packagist.org/packages/ottosmops/hash)

## Installation

```bash
composer require ottosmops/hash
```

## Usage
```php
use Ottosmops\Hash\Hash;

$hash = New Hash(); // you can pass an algorithm into the constructor
$hash->createManifest($dir);
if (!$hash->verifyManifest($dir . 'manifest')) {
    print_r($this->messages);
} else {
    echo  sprintf('All files in %s have correct checksums ', $hash->manifest); 
}
```

You can pass a filename to the ```createManifest``` method. The filename must be a path relative to the dir. With the third parameter you can switch off the recursive directory iterator. No subdirectories will be scanned:

```php
$md5 = New Hash();
$md5->createManifest($dir, "myfilename", false);
```


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
