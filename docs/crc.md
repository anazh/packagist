## Calculates CRC16 CheckSums

### Usage

- #### composer

    - **require**

      ```composer require dodosss/crc dev-master```

    - **composer.json**

      ```
      {
        "autoload": {
      	    "files": ["vendor/dodosss/crc/src/CRC/helps.php"]
      	},
      	"require": {
            "dodosss/crc": "dev-master"
        }    
      }
      ```

- #### php

```
<?php
<?php
use Dodosss\Crc\CRC16;

require_once './vendor/autoload.php';

$crc = new CRC16();
$crcResult = $crc->calc("303832");
echo $crcResult."<br/>"; // DAE3
```
结果如下：

![CRC16计算](crc16.png)