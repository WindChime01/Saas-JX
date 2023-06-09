<?php

namespace app\store\controller\wxapp;

use app\store\controller\Controller;
use app\store\model\WxappHelp as WxappHelpModel;

/**
 * 小程序帮助中心
 * Class help
 * @package app\store\controller\wxapp
 */
class Help extends Controller
{
    /**
     * 帮助中心列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new WxappHelpModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加帮助
     * @return array|mixed
     */
    public function add()
    {
        $model = new WxappHelpModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('help'))) {
            admin_log('新增小程序帮助');
            return $this->renderSuccess('添加成功', url('wxapp.help/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 更新帮助
     * @param $help_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($help_id)
    {
        // 帮助详情
        $model = WxappHelpModel::detail($help_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('help'))) {
            admin_log('更新小程序帮助');
            return $this->renderSuccess('更新成功', url('wxapp.help/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除帮助
     * @param $help_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($help_id)
    {
        // 帮助详情
        $model = WxappHelpModel::detail($help_id);
        if (!$model->remove()) {
            admin_log('删除小程序帮助');
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}
