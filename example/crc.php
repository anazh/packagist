<?php
namespace Dodo;

use Dodosss\Crc\CRC16;
use Dodosss\Crc\Package;
use Dodosss\Crc\Utils;

// 加载自动加载文件
require_once './vendor/autoload.php';

set_time_limit(0);
// php -S localhost:8000

// 初始启动
init();

function init()
{
    // $_POST
    // 重建帧数据
    if(isset($_POST['device_sn']) && isset($_POST['hex_str'])){
        $deviceSNStr    = $_POST['device_sn'];
        $hexStr         = $_POST['hex_str'];
        $deviceSNStr    = str_replace(" ", "", $deviceSNStr);
        $hexStr         = str_replace(array(" ", "&nbsp;"),  array("", ""), $hexStr);
        // check deviceSNStr
        if ($deviceSNStr == "" || $deviceSNStr== "\n" || strlen($deviceSNStr) !=16) {
            json_error(array("msg" => "设备号长度非16"));
        }
        try {
            $utils = new Utils();
            $package = $utils->reBuild($deviceSNStr, $hexStr); // 格式校验，错误抛出异常
            $data = array(
                "device_sn_hex"  => $utils->hexScreen($package->getDeviceSN()),
                "hex_str_screen" => $utils->hexScreen($package->toString()),
                "hex_str_screen2" => $utils->hexScreen($package->toString(), "2"),
            );
            json_success($data);
        } catch (\Exception $e) {
            json_error(array("msg" => $e->getMessage()));
        }
    }

    // $_GET
    $hex_str = isset($_GET['hex_str']) && $_GET['hex_str'] ? $_GET['hex_str'] : "303830353236313046454243303030310000020c0102010202020202020202020000002733560a";
    $hex_str2 = isset($_GET['hex_str2']) ? $_GET['hex_str2'] : "";
    $result = "";
    if( isset($_GET['hex_str']) ){
        $hexStr = $_GET['hex_str'];
        $result = parse($hexStr);
    }else if( isset($_GET['hex_str2']) ){
        $hexStr = $_GET['hex_str2'];
        $result = calc($hexStr);
    }

    // 简单模板
    $html = file_get_contents("crc.html");
    $html = str_replace(array('{{hex_str}}' , '{{hex_str2}}', '{{result}}'), array($hex_str, $hex_str2, $result), $html);
    echo $html;
}

function parse($hexStr)
{
    $hexStr = str_replace(" ", "", $hexStr);
    if ($hexStr == "" || $hexStr== "\n") {
        $html = "请输入要计算的16进制字符串<br/>";
        return $html;
    }
    try {
        $utils = new Utils();
        $package = $utils->parse($hexStr);
    } catch (\Exception $e) {
        $html ="Caught exception: ".$e->getMessage();
        return $html;
    }
    if(is_object($package)){
        $html = '';
        $html .= '<div class="package">';
        $html .= '<div><span id="hex_str_screen">'.$utils->hexScreen($package->toString()).'</span></div>';
        $html .= '<div><span id="hex_str_screen2">'.$utils->hexScreen($package->toString(), "2").'</span></div>';
        $html .= '<div><span id="device_sn_txt">'.$utils->hexScreen($package->getDeviceSN()).'</span><span class="arrow">--></span>';
        $html .= '<input type="text" class="form-control input-sm" id="device_sn" name="device_sn" value="'.hexToStr($package->getDeviceSN()).'" maxlength="16" /> <span id="device_sn_msg"></span></div>';
        $html .= '<div>'.$package->getVersion()."</div>";
        $html .= '<div>'.$package->getConnectType()."</div>";
        $html .= '<div>'.$package->getCommand()."</div>";
        $html .= '<div>'.$package->getDataLength().'<span class="arrow">--></span>';
        $html .= '<span>'.$package->getDataLengthDec()."</span></div>";
        $html .= '<div>'.$package->getData().'</div>';
        $html .= '<div>'.$package->getSeq().'</div>';
        $html .= '<div>'.$package->getSignature().'</div>';
        $html .= '<div>'.$package->getEof().'</div>';
        $html .= '</div>';        
    }
    return $html;
}

function calc($hexStr)
{
    $hexStr = str_replace(" ", "", $hexStr);
    if ($hexStr == "" || $hexStr== "\n") {
        $html = "请输入要计算的16进制字符串<br/>";
        return $html;
    }
    if (strlen($hexStr)%2!=0) {
        $html = "16进制字符串长度不能为单数<br/>";
        return $html;
    }
    if (!isHexString($hexStr)) {
        $html = "非16进制字符串<br/>";
        return $html;
    }
    $html = "";
    $utils = new Utils();
    $html .= $utils->hexScreen($hexStr)."<br/>";
    $crc = new CRC16();
    $crcResult = $crc->calculationResult($hexStr);
    $crcResultCheck = $crcResult[2].$crcResult[3].$crcResult[0].$crcResult[1];
    $signature = $crcResultCheck;
    $html .= $utils->hexScreen($signature)."<br/>";
    return $html;
}
?>