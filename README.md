# BittrDbug an Error/Exception handler for PHP(7+)

Basic usage:
```php
use Debug\BittrDbug;

/**
 * @param error handle type
 * @param2 theme name (bittr|default|yola)
 * @param3 lines of code to cover before and after the error.
 */
new BittrDbug(BittrDbug::PRETTIFY, 'yola', 10);
#This should be implemented before any other script execution except your autoloader(if using one).
```
Output protoype:
![Screenshot](demo.png)

Using callback function:
```php
use Debug\BittrDbug;

new BittrDbug(function (\Throwable $e) {
    var_dump($e->getMessage());
});
#This should be implemented before any other script execution except your autoloader(if using one).
```

You can also log errors instead of ouputing them in browser:
```php
use Debug\BittrDbug;

/**
 * @param error handle type
 * @param path to save log files.
 */
new BittrDbug(BittrDbug::FILE_LOG, 'path/to/my/log/');
#This should be implemented before any other script execution except your autoloader(if using one).
```
