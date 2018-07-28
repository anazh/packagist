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
                "hex_str_screen" => $utils->hexScreen($package->toString()),
                "hex_str_screen2" => $utils->hexScreen($package->toString(), "2"),
            );
            json_success($data);
        } catch (\Exception $e) {
            json_error(array("msg" => $e->getMessage()));
        }
    }

    // 重建帧数据
    if(isset($_POST['data']) && isset($_POST['hex_str'])){
        $dataStr    = $_POST['data'];
        $hexStr         = $_POST['hex_str'];
        $dataStr    = str_replace(" ", "", $dataStr);
        $hexStr         = str_replace(array(" ", "&nbsp;"),  array("", ""), $hexStr);
        // check seqStr
        if (strlen($dataStr)%2!=0) {
            json_error(array("msg" => "data非偶数"));
        }
        try {
            $utils = new Utils();
            $package = $utils->reBuildData($dataStr, $hexStr); // 格式校验，错误抛出异常
            $data = array(
                "data_length" => $package->getDataLength(),
                "data_length_dec" => $package->getDataLengthDec(),
                "hex_str_screen" => $utils->hexScreen($package->toString()),
                "hex_str_screen2" => $utils->hexScreen($package->toString(), "2"),
            );
            json_success($data);
        } catch (\Exception $e) {
            json_error(array("msg" => $e->getMessage()));
        }
    }

    // 重建帧数据
    if(isset($_POST['seq']) && isset($_POST['hex_str'])){
        $seqStr    = $_POST['seq'];
        $hexStr         = $_POST['hex_str'];
        $seqStr    = str_replace(" ", "", $seqStr);
        $hexStr         = str_replace(array(" ", "&nbsp;"),  array("", ""), $hexStr);
        // check seqStr
        if ($seqStr == "" || $seqStr== "\n" || strlen($seqStr) !=8) {
            json_error(array("msg" => "seq长度非16"));
        }
        try {
            $utils = new Utils();
            $package = $utils->reBuildSeq($seqStr, $hexStr); // 格式校验，错误抛出异常
            $data = array(
                "hex_str_screen" => $utils->hexScreen($package->toString()),
                "hex_str_screen2" => $utils->hexScreen($package->toString(), "2"),
            );
            json_success($data);
        } catch (\Exception $e) {
            json_error(array("msg" => $e->getMessage()));
        }
    }

    // $_GET
    $hex_str = isset($_GET['hex_str']) && $_GET['hex_str'] ? $_GET['hex_str'] : "303230313031303041414141303030310000a0095b46ce330000000100000000e104DA0a";
    $hex_str2 = isset($_GET['hex_str2']) ? $_GET['hex_str2'] : "";
    $result = "";
    if( isset($_GET['hex_str']) ){
        $hexStr = $_GET['hex_str'];
        $result = parse($hexStr);
    }else if( isset($_GET['hex_str2']) ){
        $hexStr = $_GET['hex_str2'];
        $result = calc($hexStr);
        if( isset($_GET['format']) && $_GET['format']=="json" ){
                $arr = $result ? explode('<br/>', $result) : array();
                if(count($arr)>2){
                    $crc = str_replace(array('&nbsp;', ' '), array('', ''), $arr[1]);
                    $data = array(
                        "status" => 1,
                        "data"   => array("crc" => $crc),
                    );
                    echo json_encode($data);exit;
                }else{
                    $data = array(
                        "status" => 0,
                        "data"   => array("msg" => "error"),
                    );
                    echo json_encode($data);exit;
                }
        }
    }

    if( isset($_GET['act']) && trim($_GET['act'])=="calc" ){
        $class_calc = "show";
        $class_decode = "hide";
    }else{
        $class_calc = "hide";
        $class_decode = "show";
    }

    // 简单模板
    $html = file_get_contents("crc.html");
    $html = str_replace(array('{{hex_str}}' , '{{hex_str2}}', '{{result}}', '{{class_calc}}', '{{class_decode}}'), array($hex_str, $hex_str2, $result, $class_calc, $class_decode), $html);
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
        $html .= '<div><span class="parse_label">dataLength</span><span id="dataLength" class="parse_text">'.$package->getDataLength().'</span><span class="arrow">--></span>';
        $html .= '<span id="dataLengthDec" class="parse_text">'.$package->getDataLengthDec()."</span></div>";
        $html .= '<div><span class="parse_label">device_sn</span><input type="text" class="form-control input-sm parse_input" id="device_sn" name="device_sn" value="'.hexToStr($package->getDeviceSN()).'" maxlength="16" /> <span id="device_sn_msg"></span></div>';      
        
        $html .= '<div><span class="parse_label">data</span><input type="text" class="form-control input-sm parse_input" id="data" name="data" value="'.$package->getData().'" /> <span id="data_msg"></span></div>';
        $html .= '<div><span class="parse_label">seq</span><input type="text" class="form-control input-sm parse_input" id="seq" name="seq" value="'.$package->getSeq().'" maxlength="8" /> <span id="seq_msg"></span></div>';
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
    $crcResult = $crc->calc($hexStr);
    $crcResultCheck = $crcResult[2].$crcResult[3].$crcResult[0].$crcResult[1];
    $signature = $crcResultCheck;
    $html .= $utils->hexScreen($signature)."<br/>";
    return $html;
}
?>