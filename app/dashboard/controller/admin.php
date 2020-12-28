<?php

declare(strict_types=1);

namespace app\dashboard\controller;

use app\dashboard\BaseController;
use Jwt;
use think\exception\ValidateException;
use think\facade\Db;

class Admin extends BaseController
{

  /**
   * 登录
   */
  public function login()
  {
    $param = $this->request->param();

    $rule = [
      'account'   => 'require|alphaDash|length:4,12',
      // 只能包含字母数字以及!@#$%^&*
      'password'  => 'require|regex:/^[0-9a-zA-Z!@#$%^&*]{6,20}$/',
    ];

    $message = [
      'account.require'   => '请填写用户名',
      'account'           => '用户名只能以字母和数字，下划线_及破折号-组成，且长度在4~12之间',
      'password.require'  => '请填写密码',
      'password'          => '密码只能由字母数字以及!@#$%^&*组成，且长度在6~20之间',
    ];

    try {
      $this->validate($param, $rule, $message);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }
    
    $admin = Db::name('admin')->where('account', $param['account'])->field('id, password, name, mobile')->find();

    if (!$admin) {
      $this->error('用户不存在');
    }

    if (!compare_password($param['password'], $admin['password'])) {
      $this->error('密码不正确');
    }

    $token = Jwt::generate(['id' => $admin['id']]);
    unset($admin['id']);

    $menuList = Db::name('admin_menu')->order('order')->select()->toArray();
    
    $this->success('', [
      'token' => $token,
      'info'  => $admin,
      'menu'  => arrayToTree($menuList),
    ]);
  }

  /**
   * 获取登录管理员信息
   */
  public function info()
  {
    $adminId = $this->getAdminId();

    $admin = Db::name('admin')->where('id', $adminId)->field('password, name, mobile')->find();

    

    if (!$admin) {
      $this->error('未知错误');
    }

    $menuList = Db::name('admin_menu')->order('order')->select()->toArray();

    $this->success('', [
      'info'  => $admin,
      'menu'  => arrayToTree($menuList),
    ]);
  }
}
