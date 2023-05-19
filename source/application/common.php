<?php

// 应用公共函数库文件

use think\Request;
use think\Log;
use think\Db;

/**
 * 打印调试函数
 * @param $content
 * @param $is_die
 */
function pre($content, $is_die = true)
{
    header('Content-type: text/html; charset=utf-8');
    echo '<pre>' . print_r($content, true);
    $is_die && die();
}

/**
 * 驼峰命名转下划线命名
 * @param $str
 * @return string
 */
function toUnderScore($str)
{
    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
        return '_' . strtolower($matchs[0]);
    }, $str);
    return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
}

/**
 * 生成密码hash值
 * @param $password
 * @return string
 */
function yoshop_hash($password)
{
    return md5(md5($password) . 'yoshop_salt_SmTRx');
}

/**
 * 获取当前域名及根路径
 * @return string
 */
function base_url()
{
    static $baseUrl = '';
    if (empty($baseUrl)) {
        $request = Request::instance();
        $subDir = str_replace('\\', '/', dirname($request->server('PHP_SELF')));
        $baseUrl = $request->scheme() . '://' . $request->host() . $subDir . ($subDir === '/' ? '' : '/');
        // $baseUrl = 'https://' . $request->host() . $subDir . ($subDir === '/' ? '' : '/');
    }
    return $baseUrl;
}

/**
 * 写入日志 (废弃)
 * @param string|array $values
 * @param string $dir
 * @return bool|int
 */
//function write_log($values, $dir)
//{
//    if (is_array($values))
//        $values = print_r($values, true);
//    // 日志内容
//    $content = '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $values . PHP_EOL . PHP_EOL;
//    try {
//        // 文件路径
//        $filePath = $dir . '/logs/';
//        // 路径不存在则创建
//        !is_dir($filePath) && mkdir($filePath, 0755, true);
//        // 写入文件
//        return file_put_contents($filePath . date('Ymd') . '.log', $content, FILE_APPEND);
//    } catch (\Exception $e) {
//        return false;
//    }
//}

/**
 * 写入日志 (使用tp自带驱动记录到runtime目录中)
 * @param $value
 * @param string $type
 */
function log_write($value, $type = 'yoshop-info')
{
    $msg = is_string($value) ? $value : var_export($value, true);
    Log::record($msg, $type);
}

/**
 * curl请求指定url (get)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curl($url, $data = [])
{
    // 处理get数据
    if (!empty($data)) {
        $url = $url . '?' . http_build_query($data);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

/**
 * curl请求指定url (post)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curlPost($url, $data = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

if (!function_exists('array_column')) {
    /**
     * array_column 兼容低版本php
     * (PHP < 5.5.0)
     * @param $array
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    function array_column($array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {
            if (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 多维数组合并
 * @param $array1
 * @param $array2
 * @return array
 */
function array_merge_multiple($array1, $array2)
{
    $merge = $array1 + $array2;
    $data = [];
    foreach ($merge as $key => $val) {
        if (
            isset($array1[$key])
            && is_array($array1[$key])
            && isset($array2[$key])
            && is_array($array2[$key])
        ) {
            $data[$key] = array_merge_multiple($array1[$key], $array2[$key]);
        } else {
            $data[$key] = isset($array2[$key]) ? $array2[$key] : $array1[$key];
        }
    }
    return $data;
}

/**
 * 二维数组排序
 * @param $arr
 * @param $keys
 * @param bool $desc
 * @return mixed
 */
function array_sort($arr, $keys, $desc = false)
{
    $key_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $key_value[$k] = $v[$keys];
    }
    if ($desc) {
        arsort($key_value);
    } else {
        asort($key_value);
    }
    reset($key_value);
    foreach ($key_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * 数据导出到excel(csv文件)
 * @param $fileName
 * @param array $tileArray
 * @param array $dataArray
 */
function export_excel($fileName, $tileArray = [], $dataArray = [])
{
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 0);
    // ob_end_clean();
    ob_start();
    header("Content-Type: text/csv");
    header("Content-Disposition:attachment;filename=" . $fileName);
    $fp = fopen('php://output', 'w');
    fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));// 转码 防止乱码(比如微信昵称)
    fputcsv($fp, $tileArray);
    $index = 0;
    foreach ($dataArray as $item) {
        if ($index == 1000) {
            $index = 0;
            ob_flush();
            flush();
        }
        $index++;
        fputcsv($fp, $item);
    }
    ob_flush();
    flush();
    ob_end_clean();
}

/**
 * 隐藏敏感字符
 * @param $value
 * @return string
 */
function substr_cut($value)
{
    $strlen = mb_strlen($value, 'utf-8');
    if ($strlen <= 1) return $value;
    $firstStr = mb_substr($value, 0, 1, 'utf-8');
    $lastStr = mb_substr($value, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', $strlen - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}

/**
 * 获取当前系统版本号
 * @return mixed|null
 * @throws Exception
 */
function get_version()
{
    static $version = null;
    if ($version) {
        return $version['version'];
    }
    $file = dirname(ROOT_PATH) . '/version.json';
    if (!file_exists($file)) {
        throw new Exception('version.json not found');
    }
    $version = json_decode(file_get_contents($file), true);
    if (!is_array($version)) {
        throw new Exception('version cannot be decoded');
    }
    return $version['version'];
}

/**
 * 获取全局唯一标识符
 * @param bool $trim
 * @return string
 */
function getGuidV4($trim = true)
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        $charid = com_create_guid();
        return $trim == true ? trim($charid, '{}') : $charid;
    }
    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    // Fallback (PHP 4.2+)
    mt_srand((double)microtime() * 10000);
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace .
        substr($charid, 0, 8) . $hyphen .
        substr($charid, 8, 4) . $hyphen .
        substr($charid, 12, 4) . $hyphen .
        substr($charid, 16, 4) . $hyphen .
        substr($charid, 20, 12) .
        $rbrace;
    return $guidv4;
}

/**
 * 时间戳转换日期
 * @param $timeStamp
 * @return false|string
 */
function format_time($timeStamp)
{
    return date('Y-m-d H:i:s', $timeStamp);
}

/**
 * 左侧填充0
 * @param $value
 * @param int $padLength
 * @return string
 */
function pad_left($value, $padLength = 2)
{
    return \str_pad($value, $padLength, "0", STR_PAD_LEFT);
}

/**
 * 过滤emoji表情
 * @param $text
 * @return null|string|string[]
 */
function filter_emoji($text)
{
    // 此处的preg_replace用于过滤emoji表情
    // 如需支持emoji表情, 需将mysql的编码改为utf8mb4
    return preg_replace('/[\xf0-\xf7].{3}/', '', $text);
}

/**
 * 根据指定长度截取字符串
 * @param $str
 * @param int $length
 * @return bool|string
 */
function str_substr($str, $length = 30)
{
    if (strlen($str) > $length) {
        $str = mb_substr($str, 0, $length, 'utf-8');
    }
    return $str;
}

/**
 * 查询所有上级
 */
function  up_downline($user_id){
    $id = db::name('dealer_user')->where('user_id',$user_id)->value('referee_id');        //第一个上级
    // dump($id);
    $arr = $id;
    $up_id = [];
    while($arr){
        $superior = db::name('dealer_user')->where(['user_id'=>$arr])->value('referee_id');      //上级ID
     //   dump($superior);
        $up_id[] = $arr;
        $arr = $superior;
    }
    return $up_id;
 }
 /**
 * 查询所有下级
 */
function downline($user_id){
    $user = db::name('dealer_user')->field('user_id,referee_id')->select();
    $arr = [$user_id];
    foreach ($user as $val){
        if(in_array($val['referee_id'],$arr)){
            $arr[] = $val['user_id'];
        }
    }
    unset($user);
    return $arr;
}
 
/**
 * 分佣奖励
 */
function reward($user_id,$goods,$order_id,$order_type,$wxapp_id){               //分佣金(1-用户id 2-商品总金额(不含运费) 3-订单id 4-订单类型 5-wxapp_id)
$up_downline = up_downline($user_id);           //获取上级ID
// dump($a);
// dump($up_downline);
$levels = 0;
$rewards =0;
foreach ($up_downline as $val){
    $level = db::name('user')->where(['user_id'=>$val])->value('grade_id');         //上级等级
    $amount = db::name('user_grade')->where(['grade_id'=>$level])->value('commission');         //上级佣金比率
    $amount = $amount/100;
    // dump($level);die;
    if($level>$levels){
        $reward =  $goods*$amount-$rewards;             //分佣奖励
        // dump($amount);die;
        $rewards = $reward;
        // dump($rewards);
        $levels = $level;
    if($reward>0){
        $data = [
                'first_user_id'=>$val,                //上级id
                'user_id'=>$user_id,             //买家id
                'order_id'=>$order_id,          //订单id
                'order_type'=>$order_type,               //订单类型
                'order_price'=>$goods,          //订单总金额
                'first_money' => $reward,
                'wxapp_id'=>$wxapp_id,
                'create_time' => time(),
                'update_time' =>time(),
                'date_create_time'=>date('Y-m-d H:i:s',time()),
            ];
            db::name('dealer_order')->add($data);
            // return array("msg"=>"奖励发放成功","success"=>1);
        // echo 'id'.$val.'获得佣金'.$reward.'<br>';
    }
    }

}
}
/**
 * 发放分佣奖励
 */
function reward_settlement($order_id){
    $order = db::name('dealer_order')->where(['order_id'=>$order_id,'is_invalid'=>0,'is_settled'=>0])->select();
    $user_order = db::name('order')->where('order_id',$order_id)->field('user_id,order_price')->find();
    $expend_money = db::name('user')->where('user_id',$user_order['user_id'])->value('expend_money');
    $now_expend_money = db::name('user')->where('user_id',$user_order['user_id'])->save(['expend_money'=>$expend_money+$user_order['order_price']]);

    // dump($order);die;
    foreach ($order as $val){
        $amount = db::name('dealer_order')->where(['first_user_id'=>$val['first_user_id'],'order_id'=>$order_id])->value('first_money');        //佣金金额
        $money = db::name('dealer_user')->where('user_id',$val['first_user_id'])->field('money,total_amount')->find();       //查目前可提现佣金、累计获得佣金
        // dump($money);die;
        $data = [
                    'is_settled'=>1,
                    'settle_time'=>time(),
                    'update_time'=>time(),
                    'date_settle_time'=>date('Y-m-d H:i:s',time()),
            ];
            // dump($val['first_user_id']);die;
        $now_money = db::name('dealer_user')->where('user_id',$val['first_user_id'])->save(['money'=>$amount+$money['money'],
                    'total_amount'=>$amount+$money['total_amount']]);         //更新可提现佣金、累计获得佣金
        $now_order = db::name('dealer_order')->where(['first_user_id'=>$val['first_user_id'],'order_id'=>$order_id])->save($data);      //更新分销订单状态
        $now_orders = db::name('order')->where(['order_id'=>$order_id])->save(['is_settled'=>1]);        //更新普通订单状态
        // dump($now_order);die;
    }
}
/**
 * 写入管理员日志
 */
function admin_log($log_info){
    $data = [
                'admin_id' =>$_SESSION['yoshop_store']['user']['store_user_id'],
                'log_info' =>$log_info,
                'admin_name'=>$_SESSION['yoshop_store']['user']['user_name'],
                'wxapp_id' =>$_SESSION['yoshop_store']['wxapp']['wxapp_id'],
                'log_ip' => request()->ip(),
                'log_url' =>$_SERVER['REQUEST_URI'],
                'log_time'=>time(),
                'date_log_time'=>date('Y-m-d H:i:s',time()),
        ];
    // return $data;
    $add = db::name('store_log')->add($data);
    }
    /**
 * 导出excel
 * @param $strTable	表格内容
 * @param $filename 文件名
 */
function downloadExcel($strTable,$filename)
{
	header('Cache-Control: max-age=0');
	header("Content-type: application/vnd.ms-excel");
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=".$filename."_".date('YmdHis').".xlsx");
	header('Expires:0');
	header('Pragma:public');
	echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
}