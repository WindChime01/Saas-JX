<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use think\Db;


class Log extends Controller{
    
    public function index(){
		$list = cache('store_log');
		if(!$list){
        $list = db::name('store_log')
        ->where(['wxapp_id'=>$_SESSION['yoshop_store']['wxapp']['wxapp_id']])
        ->order('log_id desc')
        ->paginate(10, false, [
                'query' => \request()->request()
            ]);
		cache('store_log',$list);
			}
        return $this->fetch('index', compact('list'));
    }
}