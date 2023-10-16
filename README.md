# nx-log

log for nx


> composer require veasin/nx-log

```php
use \nx\parts\log\file;
use \nx\parts\log\dump;
class app extends \nx\app{
    use file,dump;
    public $log_dump_name='dump';
}
```

```php
$this->log($any);
$this->log->warning($any)
```
