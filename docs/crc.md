## Calculates CRC16 checksums

### Usage

- #### 安装扩展

```
composer require dodosss/crc dev-master
composer update
composer dump-autoload
composer remove dodosss/crc
```

- #### example/composer.json
  - ```repositories```，添加本地git库
  - ```autoload```，添加自动加载函
  - ```require```，```composer require dodosss/crc dev-master``` 自动添加

```
{
  // 本地开发git地址（非开发删除repositories内容）
  "repositories":[ 
       { 
           "type":"git", 
           "url":"D:/phpStudy/WWW/packagist" 
       }
   ],
    "autoload": {
	    "files": ["vendor/dodosss/crc/src/CRC/helps.php"]
	},
	"require": {
      "dodosss/crc": "dev-master"
  }    
}
```

- #### 测试代码

```
<?php
namespace Dodo;

// php -S localhost:8000
set_time_limit(0);
use Dodosss\Crc\CRC16;

// 加载自动加载文件
require_once './vendor/autoload.php'; 

// crc16
$hexStr = "0102";
$crc = new CRC16();
$crcResult = $crc->calculationResult($hexStr);
echo $crcResult."<br/>"; // E181
```