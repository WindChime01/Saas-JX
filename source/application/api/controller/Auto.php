<?php

// 定时任务
namespace app\api\controller;

use think\Db;
class Auto {
        /**
         * 等级升级
         */
        public function level(){
            $user = db::name('user')->field('user_id,expend_money,grade_id')->select();         //获取所有用户相关信息
            $level = db::name('user_grade')->where(['status'=>1,'backstage'=>0])->order('weight asc')->field('grade_id,upgrade,name')->select();            //获取所有等级相关信息
            foreach ($user as $val){
                foreach ($level as $val2){
                        if($val['expend_money']>=$val2['upgrade']){
                            if($val['grade_id']<$val2['grade_id']){
                                // dump();die;
                                $old_level = db::name('user')->where('user_id',$val['user_id'])->value('grade_id');
                                $data = [
                                            'user_id'=>$val['user_id'],
                                            'old_grade_id'=>$old_level,
                                            'new_grade_id'=>$val2['grade_id'],
                                            'change_type'=>20,
                                            'remark'=>'升级到'.$val2['name'],
                                            'wxapp_id'=>10006,
                                            'create_time'=>time(),
                                    ];
                                    // dump($val['user_id']);die;
                                $up = db::name('user')->where('user_id',$val['user_id'])->save(['grade_id'=>$val2['grade_id']]);
                                $up_log = db::name('user_grade_log')->add($data);
                                // dump($val2['grade_id']);
                            }
                        }
                }
                     $downline = downline($val['user_id']);
                     array_splice($downline,0,1);          //获取下级人数
                    //  dump($downline);
                     $now_downline = count($downline);
                     $nows_downline = db::name('dealer_user')->where(['user_id'=>$val['user_id']])->save(['first_num'=>$now_downline]);
            }
            echo '执行成功';
        }
}