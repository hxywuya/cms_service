<?php

declare(strict_types=1);

namespace app\dashboard\controller;

use app\dashboard\BaseController;
use think\exception\ValidateException;
use think\facade\Db;

class Article extends BaseController
{

  /**
   * 文章列表
   */
  public function list()
  {
    $this->getAdminId();
    $param = $this->request->param();
    $page = 1;
    $num = 10;
    if (isset($param['page'])) {
      $page = $param['page'];
    }

    if (isset($param['num'])) {
      $num = $param['num'];
    }
    
    $list = Db::name('article')->alias('article')
      ->join('admin admin','admin.id = article.admin_id')
      ->where('article.delete_time', 'null')
      ->field('article.*, admin.name as admin_name, admin.account as admin_account')
      ->order('id')->paginate([
        'list_rows' => $num,
        'page'      => $page,
      ]);

    $this->success('', $list);
  }

  /**
   * 获取登录管理员信息
   */
  public function info()
  {
    $adminId = $this->getAdminId();

    $res = getMenu($adminId);
    $res['info'] = $this->adminInfo;

    $this->success('', $res);
  }
}
