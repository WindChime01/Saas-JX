<?php

namespace app\admin\controller;
use think\Db;

class Test{

    public function test(){
        // dump(1);die;
        // $a = level();
        // dump($a);
        $success = 0;
        $order = db::name('store_log')->select();
        foreach ($order as $val){
            $date_log_time = date('Y-m-d H:i:s',$val['log_time']);
            $save = db::name('store_log')->where('log_id',$val['log_id'])->save(['date_log_time'=>$date_log_time]);
            $success++;
        }
        echo '已成功执行'.$success.'次';
    }
    
    public function excel(){
    $lujin = dirname(dirname(dirname(dirname(__FILE__)))).'/PHPExcel-1.8/PHPExcel/';
    // var_dump($lujin);die;
    include $lujin.'IOFactory.php';
    
    $inputFileName = '/www/wwwroot/saas_test.weiyintest.com/saas/source/PHPExcel-1.8/测试导入.xlsx';
    date_default_timezone_set('PRC');
    // 读取excel文件
    
    $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
    $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);
    
    
    // 确定要读取的sheet，什么是sheet，看excel的右下角，真的不懂去百度吧
    $sheet = $objPHPExcel->getSheet(0);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    // dump($highestRow);
    $all = array();
    $keys = [
      0 => "user_id",
      1 => "nickName",
    ];
    $count =0;
    // 获取一行的数据
    for ($row = 2; $row <= $highestRow; $row++){
        // Read a row of data into an array
        
        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
        // dump($rowData);die;
        if($row == 1) {
            // $keys = $rowData[0];
        } else {
            // dump($rowData);die;
            foreach ($rowData[0] as $key=>$val) {
                $rowData[0][$keys[$key]] = $val;
                unset($rowData[0][$key]);
            }
            $all[] = $rowData[0];
        }
        // if($row >= 10) break;
    }
    $allcount = count($all);
    // dump($allcount);die;
    foreach ($all as $val) {
        $data = [
            'nickName'=>'测试',
            'wxapp_id'=>10006,
            'create_time'=>time(),
            'update_time'=>time(),
            ];
            // dump($data);die;
        $add = db::name('user')->add($data);
        if($add){
            $count++;
        }
    }
    echo '共'.$allcount.'条数据'.'<br>'.'已成功导入'.$count.'条数据';
    exit;
}
}