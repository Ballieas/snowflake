#### A simple implementation of snowflake

##### Usage
```php
try {
    $snowFlake = new \Library\Snowflake(1, 1, 1, 5);
    for ($i = 0; $i < 50; $i++) {
        print_r($snowFlake->nextID());
        echo PHP_EOL;
    }
} catch (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
}
```
