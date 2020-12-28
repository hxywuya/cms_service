<?php

declare(strict_types=1);

namespace app\dashboard\controller;

use app\dashboard\BaseController;
use think\exception\ValidateException;
use think\facade\Db;

class Auth extends BaseController
{

  /**
   * 账户列表
   */
  public function account()
  {
    $param = $this->request->param();
    $page = 1;
    $num = 10;
    if (isset($param['page'])) {
      $page = $param['page'];
    }

    if (isset($param['num'])) {
      $num = $param['num'];
    }

    $list = Db::name('admin')->order('id')->paginate([
      'list_rows' => $num,
      'page'      => $page,
    ]);

    $this->success('', $list);
  }

  /**
   * 角色列表
   */
  public function role()
  {
    $param = $this->request->param();
    $page = 1;
    $num = 10;
    if (isset($param['page'])) {
      $page = $param['page'];
    }

    if (isset($param['num'])) {
      $num = $param['num'];
    }

    $list = Db::name('role')->order('id')->where('delete_time', 'null')->paginate([
      'list_rows' => $num,
      'page'      => $page,
    ]);

    $this->success('', $list);
  }

  /**
   * 获取角色数据
   */
  public function roleInfo()
  {
    $param = $this->request->param();

    $rule = [
      'id'  => 'require|integer',
    ];

    try {
      $this->validate($param, $rule);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }

    

    $info = Db::name('role')->where('id', $param['id'])->field('id, name, remark')->find();

    if ($info) {
      $info['menu_ids'] = Db::name('role_menu')
        ->where('role_id', $param['id'])
        ->column('menu_id');
    }


    $this->success('', $info);
  }

  /**
   * 新增/编辑角色
   */
  public function roleEdit()
  {
    $param = $this->request->param();

    $rule = [
      'id'      => 'integer',
      'name'    => 'require|length:4,32',
      'remark'  => 'max:255',
      'menuIds' => 'array',
    ];

    $message = [
      'name.require'  => '请填写角色名称',
      'name'          => '角色名称的长度只能在4~32个字符之间',
      'remark'        => '角色描述最多不能超过255个字符',
      'menuIds'       => '请传入正确的角色权限',
    ];

    try {
      $this->validate($param, $rule, $message);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }

    Db::startTrans();
    try {
      $id = null;
      $data = [
        'name'    => $param['name'],
      ];

      if (isset($param['remark'])) {
        $data['remark'] = $param['remark'];
      }

      Db::name('role_menu')
        ->where('role_id', $param['id'])
        ->delete();
      if (isset($param['id'])) {
        // 编辑
        $id = $param['id'];
        Db::name('role')
          ->where('id', $id)
          ->update($data);

        if (count($param['menuIds']) > 0) {
          $menus = [];
          foreach ($param['menuIds'] as $menuId) {
            array_push($menus, [
              'role_id' => $id,
              'menu_id' => $menuId,
            ]);
          }
          Db::name('role_menu')
            ->insertAll($menus);
        }
      } else {
        // 新增
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = $data['create_time'];
        $id = Db::name('role')->insertGetId($data);
        if (!$id) {
          throw new \Exception("角色新增失败");
        }

        if (count($param['menuIds']) > 0) {
          $menus = [];
          foreach ($param['menuIds'] as $menuId) {
            array_push($menus, [
              'role_id' => $id,
              'menu_id' => $menuId,
            ]);
          }
          Db::name('role_menu')
            ->insertAll($menus);
        }
      }
      // 提交事务
      Db::commit();
    } catch (\Exception $e) {
      $this->error($e->getMessage());
      // 回滚事务
      Db::rollback();
    }
    
    $this->success();
  }

  /**
   * 删除角色数据
   */
  public function roleDel()
  {
    $param = $this->request->param();

    $rule = [
      'id'  => 'require|integer',
    ];

    try {
      $this->validate($param, $rule);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }

    Db::name('role')->where('id', $param['id'])->useSoftDelete('delete_time', date('Y-m-d H:i:s'))->delete();

    $this->success();
  }
}
