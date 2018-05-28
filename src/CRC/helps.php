<?php

// 功能函数
function json_success($data)
{
    json(1, $data);
}

function json_error($data)
{
    json(0, $data);
}

function json($status, $data)
{
    $result = array(
        "status"    => $status,
        "data"      => $data
    );
    echo json_encode($result);
    exit;
}

function hexToStr($hex)
{
    $str = "";
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $str .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $str;
}

function strToHex($str)
{
    $hex='';
    for ($i=0; $i < strlen($str); $i++){
        $hex .= dechex(ord($str[$i]));
    }
    return $hex;
}

function isHexString($str)
{
    return ctype_xdigit($str);
}