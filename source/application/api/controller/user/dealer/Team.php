<?php

namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\Setting;
use app\api\model\dealer\User as DealerUserModel;
use app\api\model\dealer\Referee as RefereeModel;
use think\Db;

/**
 * 我的团队
 * Class Order
 * @package app\api\controller\user\dealer
 */
class Team extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    private $dealer;
    private $setting;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 用户信息
        $this->user = $this->getUser();
        // 分销商用户信息
        $this->dealer = DealerUserModel::detail($this->user['user_id']);
        // 分销商设置
        $this->setting = Setting::getAll();
    }

    /**
     * 我的团队列表
     * @param int $level
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($level = -1)
    {
        $model = new RefereeModel;
        $downline = downline($this->user['user_id']);
        array_splice($downline,0,1);
        $team_money = db::name('user')->where(['user_id'=>['in',$downline]])->sum('expend_money');
        // dump($team_money);die;
        return $this->renderSuccess([
            // 分销商用户信息
            'dealer' => $this->dealer,
            // 我的团队列表
            'list' => $model->getList($this->user['user_id']),
            // 基础设置
            'setting' => $this->setting['basic']['values'],
            // 页面文字
            'words' => $this->setting['words']['values'],
            //团队业绩
            'team_money' =>$team_money,
        ]);
    }

}