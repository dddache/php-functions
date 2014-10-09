## some class for yii
use like this at index.php

`if(!in_array('mysql', PDO::getAvailableDrivers())){`
`    $config = require($config);`
`    $config['components']['db']['pdoClass'] = 'PDO_Mysql';`
`}`
