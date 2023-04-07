<?php

namespace app\store\controller\apps\dealer;

use app\store\controller\Controller;
use app\store\model\dealer\Order as OrderModel;
use think\Db;
/**
 * 分销订单
 * Class Order
 * @package app\store\controller\apps\dealer
 */
class Order extends Controller
{
    /**
     * 分销订单列表
     * @param null $user_id
     * @param int $is_settled
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($user_id = null, $is_settled = -1)
    {
        // dump($_GET['end_time']);die;
        $model = new OrderModel;
        //选择结算或未结算订单
        if($is_settled>-1){
            //选定时间计算
            if(!empty($_GET['start_time'])>0 and !empty($_GET['end_time'])>0){
                $start_time = strtotime(date($_GET['start_time']));
                $end_time = strtotime(date($_GET['end_time']));
                //根据分销商ID计算
                if(!empty($_GET['search'])){
                   $money = db::name('dealer_order')->where(['is_settled'=>$_GET['is_settled'],'is_invalid'=>0,'first_user_id'=>$_GET['search'],'create_time'=>['between',[$start_time,$end_time]]])->sum('first_money');
                //根据用户ID计算
                }else if($user_id>0){
                   $money = db::name('dealer_order')->where(['is_settled'=>$_GET['is_settled'],'is_invalid'=>0,'user_id'=>$user_id,'create_time'=>['between',[$start_time,$end_time]]])->sum('first_money');
                //根据未结算计算
                }else{
                   $money = db::name('dealer_order')->where(['is_settled'=>$_GET['is_settled'],'is_invalid'=>0,'create_time'=>['between',[$start_time,$end_time]]])->sum('first_money');
                }
            }else{
                //未选定时间计算
                if(!empty($_GET['search'])){
                   $money = db::name('dealer_order')->where(['is_settled'=>$_GET['is_settled'],'is_invalid'=>0,'first_user_id'=>$_GET['search']])->sum('first_money');
                }else if($user_id>0){
                   $money = db::name('dealer_order')->where(['is_settled'=>$_GET['is_settled'],'is_invalid'=>0,'user_id'=>$user_id])->sum('first_money');
                }else{
                   $money = db::name('dealer_order')->where(['is_settled'=>$_GET['is_settled'],'is_invalid'=>0])->sum('first_money');
                }
            }
        //全部订单
        }else{
            //选定时间计算
            if(!empty($_GET['start_time'])>0 and !empty($_GET['end_time'])>0){
                $start_time = strtotime(date($_GET['start_time']));
                $end_time = strtotime(date($_GET['end_time']));
                if(!empty($_GET['search'])){
                   $money = db::name('dealer_order')->where(['is_invalid'=>0,'first_user_id'=>$_GET['search'],'create_time'=>['between',[$start_time,$end_time]]])->sum('first_money');
                }else if($user_id>0){
                   $money = db::name('dealer_order')->where(['is_invalid'=>0,'user_id'=>$user_id,'create_time'=>['between',[$start_time,$end_time]]])->sum('first_money');
                }else{
                $money = db::name('dealer_order')->where(['is_invalid'=>0,'create_time'=>['between',[$start_time,$end_time]]])->sum('first_money');
                }
            }else{
                //未选定时间计算
                if(!empty($_GET['search'])){
                   $money = db::name('dealer_order')->where(['is_invalid'=>0,'first_user_id'=>$_GET['search']])->sum('first_money');
                }else if($user_id>0){
                   $money = db::name('dealer_order')->where(['is_invalid'=>0,'user_id'=>$user_id])->sum('first_money');
                }else{
                $money = db::name('dealer_order')->where(['is_invalid'=>0])->sum('first_money');
                }
            }
        }
        $list = $model->getList($user_id, $is_settled);
        return $this->fetch('index', compact('list','money'));
    }

}