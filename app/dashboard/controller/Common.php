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
    $menuList = Db::name('admin_menu')->order('order')->select()->toArray();

    $this->success('', arrayToTree($menuList));
  }

  /**
   * 获取全部角色数据
   */
  public function allRoles()
  {
    $roleList = Db::name('role')->where('delete_time', 'null')->select();

    $this->success('', $roleList);
  }
}
