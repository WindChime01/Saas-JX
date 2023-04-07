<?php

namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\Setting;
use app\api\model\dealer\User as DealerUserModel;
use app\api\model\dealer\Order as OrderModel;
use app\common\service\Order as OrderService;
use think\Db;
/**
 * 分销商订单
 * Class Order
 * @package app\api\controller\user\dealer
 */
class Orders extends Controller
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
     * 分销商订单列表
     * @param int $settled
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($settled = -1)
    {
        // dump(1);die;
        // $model = new OrderModel;
       
        $data = $_GET;
        // dump($data);
        $dealer_order =
        db::name('dealer_order')
        ->alias('d')
        ->join('yoshop_order o','d.order_id = o.order_id')
        ->join('yoshop_user u','u.user_id = d.first_user_id')
        ->join('yoshop_user_grade g','g.grade_id = u.grade_id')
        ->where(['d.user_id'=>$this->user['user_id']])
        ->order(['d.create_time' => 'desc'])
        ->field('d.*,o.order_no,o.order_status,g.name,u.avatarUrl')
        // ->select();
        ->paginate(15, false, ['query' => \request()->request()]);
        // foreach ($dealer_order as $key=>$val){
        //     $dealer_order[$key]['create_time'] =  date("Y-m-d H:i",$val['create_time']);
        //     dump($dealer_order[$key]['create_time']);
        // }
        // die;
         if($settled > -1){
         $dealer_order = 
         db::name('dealer_order')
        ->alias('d')
        ->join('yoshop_order o','d.order_id = o.order_id')
        ->join('yoshop_user u','u.user_id = d.first_user_id')
        ->join('yoshop_user_grade g','g.grade_id = u.grade_id')
        ->where(['d.user_id'=>$this->user['user_id'],'d.is_settled'=>$data['settled']])
        ->order(['d.create_time' => 'desc'])
        ->field('d.*,o.order_no,o.order_status,g.name,u.avatarUrl')
        // ->select();
        ->paginate(15, false, ['query' => \request()->request()]);
         }
            // dump($dealer_order);die;
        return $this->renderSuccess([
            // 提现明细列表
            'list' => $dealer_order,
            
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

}