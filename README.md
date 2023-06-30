# nx-log

log for nx


> composer require urn2/nx-log

```php
use \nx\parts\log\file;
use \nx\parts\log\dump;
class app extends \nx\app{
    use file,dump  
}
```

```php
$this->log($any);
$this->log->warning($any)
```
