<?php

use Illuminate\Support\Facades\Log;

function statics ($file)
{
    return config('console.static_url').$file.'?v='.config('console.static_v');
}

/**
 * 截取REQUEST_URI
 * @param string $URL	REQUEST_URI
 */
function REQUEST_URI($URL,$ARR){
    $U = explode("?", $URL);
    $B = false;
    foreach ($ARR as $key => $value) {
        if($value === $U[0]){
            $B = true;
        }
    }
    return $B;
}

/**
 * 统一格式输出日志
 *
 * @param string    $typeName   类别名
 * @param array     $log        日志详情
 * @param integer   $startTime  开始时间
 */
function setLog($typeName, $startTime = 0, $log = [])
{
    $diffTime = $startTime ? getDiffMicrotime($startTime) : '0.00';
    
    Log::info($typeName . ' 参数:' . json_encode($log) . '执行用时:' . $diffTime);
}

/**
 * 计算时间差
 * @param integer $sTime
 */
function getDiffMicrotime($sTime) {
    $sTime = explode(' ', $sTime);
    $mTime = explode(' ',microtime());
    return roundDown((($mTime[1]+$mTime[0]) - ($sTime[1]+$sTime[0])),3);
}

/**
 * 向下啥去为最为接近的小数
 * @param number $x		操作对象
 * @param number $prec	小数点后几位
 */
function roundDown($x, $prec=2)
{
    return substr(sprintf("%.8f", $x), 0, -(8-$prec));
}