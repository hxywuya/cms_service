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
    
    $admin = Db::name('admin')
      ->where('account', $param['account'])
      ->where('delete_time', 'null')
      ->field('id, password, name, mobile, account, status')->find();

    if (!$admin) {
      $this->error('用户不存在');
    }

    if ($admin['status'] != 1) {
      $this->error('该账户已被禁用');
    }

    if (!compare_password($param['password'], $admin['password'])) {
      $this->error('密码不正确');
    }

    $token = Jwt::generate(['id' => $admin['id']]);
    $res = getMenu($admin['id']);
    unset($admin['id']);

    $res['token'] = $token;
    $res['info'] = $admin;
    
    $this->success('', $res);
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
