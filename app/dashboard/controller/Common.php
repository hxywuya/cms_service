<?php

declare(strict_types=1);

namespace app\dashboard\controller;

use app\dashboard\BaseController;
use think\facade\Db;

class Common extends BaseController
{

  /**
   * 获取全部菜单数据
   */
  public function menuTree()
  {
    $this->getAdminId();
    $menuList = Db::name('admin_menu')->order('order')->select()->toArray();

    $this->success('', arrayToTree($menuList));
  }

  /**
   * 获取全部角色数据
   */
  public function allRoles()
  {
    $this->getAdminId();
    $roleList = Db::name('role')->where('delete_time', 'null')->where('id', '<>', 1)->select();

    $this->success('', $roleList);
  }
}
