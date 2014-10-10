## some class for yii
###PDO_Mysql
use like this in index.php
```php
if(!in_array('mysql', PDO::getAvailableDrivers())){
    $config = require($config);
    $config['components']['db']['pdoClass'] = 'PDO_Mysql';
}
```
