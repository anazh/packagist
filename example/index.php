<?php
namespace Dodo;

// 加载自动加载文件
require_once './vendor/autoload.php';

set_time_limit(0);
// php -S localhost:8000

use Dodosss\Crc\CRC16;
use Dodosss\Crc\PackageUtils;

// crc16
$hexStr = "0102";
$crc = new CRC16();
$crcResult = $crc->calculationResult($hexStr);
echo $crcResult."<br/>"; // E181


// Package
$deviceSNStr = "08010100AAAA0001";
$hexStr = "303830353236313046454243303030310000020c0102010202020202020202020000002733560a";
$packageUtils = new PackageUtils();
echo $packageUtils->hexScreen($hexStr)."<br/>";
$package = $packageUtils->reBuild($deviceSNStr, $hexStr); // 格式校验，错误抛出异常
echo $packageUtils->hexScreen($package->getDeviceSN())."<br/>";
echo hexToStr($package->getDeviceSN())."<br/>";