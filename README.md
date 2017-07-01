# Dbug a *very lightweight Error/Exception handler for PHP(7+)
[![Latest Stable Version](https://img.shields.io/badge/release-v1.0.0-brightgreen.svg)](https://github.com/Ghostff/Text_Tables_Generator/releases) ![License](https://img.shields.io/pypi/l/Django.svg) [![Latest Stable Version](https://img.shields.io/badge/packagist-v5.5.4-blue.svg)](https://packagist.org/packages/ghostff/text-tables-generator) [![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.0-8892BF.svg)](http://php.net/releases/7_0_0.php)
----------

#Installation   
You can download the  Latest [release version ](https://github.com/Ghostff/Dbug/releases/) as a standalone, alternatively you can use [Composer](https://getcomposer.org/) 
```json
$ composer require ghostff/dbug
```
```json
"require": {
    "ghostff/dbug": "^1.0"
}
```    

Basic usage:
```php
use Dbug\BittrDbug;

/**
 * @param error handle type
 * @param theme name (bittr|default|yola). Themes can be configures in theme.json
 * @param lines of code to cover before and after the error.
 */
new BittrDbug(BittrDbug::PRETTIFY, 'yola', 20);
#This should be implemented before any other script execution except your autoloader(if using one).
```
Output protoype:
![Screenshot](demo.png)

Using callback function:
```php
use Dbug\BittrDbug;

new BittrDbug(function (\Throwable $e) {
    var_dump($e->getMessage());
});
#This should be implemented before any other script execution except your autoloader(if using one).
```

You can also log errors instead of outputting them in browser:
```php
use Dbug\BittrDbug;

/**
 * @param error handle type
 * @param path to save log files.
 */
new BittrDbug(BittrDbug::FILE_LOG, 'path/to/my/log/');
#This should be implemented before any other script execution except your autoloader(if using one).
```
For file logging, you can set your ```path``` to a directory outside your webroot or maybe add add a ```.htaccess``` to prevent direct access to your log directory.


