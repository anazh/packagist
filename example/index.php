<?php
use Dodosss\Crc\CRC16;

require_once './vendor/autoload.php';

$crc = new CRC16();
$crcResult = $crc->calc("303832");
echo $crcResult."<br/>"; // DAE3