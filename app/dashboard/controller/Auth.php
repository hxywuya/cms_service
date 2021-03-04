<?php

declare(strict_types=1);

namespace app\dashboard\controller;

use app\dashboard\BaseController;
use think\exception\ValidateException;
use think\facade\Db;

class Auth extends BaseController
{

  /**
   * 管理员账户列表
   */
  public function account()
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
    
    $list = Db::name('admin')->where('delete_time', 'null')
      ->where(function ($query) use($param) {
        if (isset($param['search'])) {
          $query->where('name', 'like', "%${param['search']}%")
            ->whereOr('account', 'like', "%${param['search']}%")
            ->whereOr('mobile', 'like', "%${param['search']}%");
        }
      })->order('id')->paginate([
        'list_rows' => $num,
        'page'      => $page,
      ])->each(function($item, $key){
        $item['roles'] = Db::name('role_admin')->alias('ra')
          ->join('role r','r.id = ra.role_id')
          ->where('admin_id', $item['id'])
          ->field('r.id, r.name')
          ->select();
        return $item;
      });

    $this->success('', $list);
  }

  /**
   * 获取管理员数据
   */
  public function accountInfo()
  {
    $this->getAdminId();
    $param = $this->request->param();

    $rule = [
      'id'  => 'require|integer',
    ];

    try {
      $this->validate($param, $rule);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }

    $info = Db::name('admin')->where('id', $param['id'])->field('id, name, mobile, account, status, remarks')->find();

    if ($info) {
      $info['roles'] = Db::name('role_admin')
        ->where('admin_id', $param['id'])
        ->column('role_id');
    }


    $this->success('', $info);
  }

  /**
   * 新增/编辑管理员
   */
  public function accountEdit()
  {
    $this->getAdminId();
    $param = $this->request->param();

    $rule = [
      'id'        => 'integer',
      'account'   => 'require|alphaDash|length:4,12',
      // 只能包含字母数字以及!@#$%^&*
      'password'  => 'require|regex:/^[0-9a-zA-Z!@#$%^&*]{6,20}$/',
      'name'      => 'max:32',
      'mobile'    => 'mobile',
      'roles'     => 'array',
      'status'    => 'require|in:0,1',
      'remarks'   => 'max:255',
    ];

    if (isset($param['id'])) {
      if ($param['id'] === 1) {
        $this->error('超级管理员不可被编辑');
      }
      unset($rule['password']);
    }

    $message = [
      'account.require'   => '请填写用户名',
      'account'           => '用户名只能以字母和数字，下划线_及破折号-组成，且长度在4~12之间',
      'password.require'  => '请填写密码',
      'password'          => '密码只能由字母数字以及!@#$%^&*组成，且长度在6~20之间',
      'name'              => '姓名最多不能超过32个字符',
      'mobile'            => '请填写正确的手机号',
      'remarks'           => '备注最多不能超过255个字符',
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
        'account' => $param['account'],
        'name'    => $param['name'],
        'mobile'  => $param['mobile'],
        'status'  => $param['status'],
        'remarks' => $param['remarks'],
      ];

      
      if (isset($param['id'])) {
        // 编辑
        $id = $param['id'];
        Db::name('admin')
          ->where('id', $id)
          ->update($data);

        Db::name('role_admin')
          ->where('admin_id', $param['id'])
          ->delete();
      } else {
        // 新增
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = $data['create_time'];
        $data['password'] = password($param['password']);
        $id = Db::name('admin')->insertGetId($data);
        if (!$id) {
          throw new \Exception("账号新增失败");
        }
      }

      // 生成角色关联
      if (count($param['roles']) > 0) {
        $menus = [];
        foreach ($param['roles'] as $roleId) {
          array_push($menus, [
            'admin_id'  => $id,
            'role_id'   => $roleId,
          ]);
        }
        Db::name('role_admin')
          ->insertAll($menus);
      }
      // 提交事务
      Db::commit();
    } catch (\Exception $e) {
      $this->error($e->getMessage());
      // 回滚事务
      Db::rollback();
    }

    // 写入日志
    if (isset($param['id'])) {
      $admin = Db::name('admin')->where('id', $param['id'])->find();
      writeOperationLog($this->adminId, " 修改了账号：{$admin['account']}", 3, json_encode($param));
    } else {
      writeOperationLog($this->adminId, " 新增了账号：${param['account']}", 3, json_encode($param));
    }
    
    $this->success();
  }

  /**
   * 设定管理员状态
   */
  public function accountStatus()
  {
    $this->getAdminId();
    $param = $this->request->param();

    $rule = [
      'id'      => 'require|integer',
      'status'  => 'require|in:0,1',
    ];

    try {
      $this->validate($param, $rule);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }

    if ($param['id'] === 1) {
      $this->error('超级管理员不可被编辑');
    }

    Db::name('admin')->where('id', $param['id'])->update(['status' => $param['status']]);

    // 写入日志
    $admin = Db::name('admin')->where('id', $param['id'])->find();
    $status = $param['status'] === 1 ? '启用' : '禁用';
    writeOperationLog($this->adminId, " ${status}了账号：{$admin['account']}", 3, json_encode($param));

    $this->success();
  }

  /**
   * 删除管理员数据
   */
  public function accountDel()
  {
    $this->getAdminId();
    $param = $this->request->param();

    $rule = [
      'id'  => 'require|integer',
    ];

    try {
      $this->validate($param, $rule);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }

    if ($param['id'] === 1) {
      $this->error('超级管理员不可被删除');
    }

    Db::name('admin')->where('id', $param['id'])->useSoftDelete('delete_time', date('Y-m-d H:i:s'))->delete();

    // 写入日志
    $admin = Db::name('admin')->where('id', $param['id'])->find();
    writeOperationLog($this->adminId, " 删除了账号：{$admin['account']}", 3, json_encode($param));

    $this->success();
  }

  /**
   * 重置管理员密码
   */
  public function resetAccountPwd()
  {
    $this->getAdminId();
    $param = $this->request->param();

    $rule = [
      'id'        => 'require|integer',
      // 只能包含字母数字以及!@#$%^&*
      'password'  => 'require|regex:/^[0-9a-zA-Z!@#$%^&*]{6,20}$/',
    ];

    try {
      $this->validate($param, $rule);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }

    Db::name('admin')->where('id', $param['id'])->update(['password' => password($param['password'])]);

    // 写入日志
    $admin = Db::name('admin')->where('id', $param['id'])->find();
    unset($param['password']);
    writeOperationLog($this->adminId, " 重置了账号：{$admin['account']} 的密码", 3, json_encode($param));

    $this->success();
  }

  /**
   * 角色列表
   */
  public function role()
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
    $this->getAdminId();
    $param = $this->request->param();

    $rule = [
      'id'  => 'require|integer',
    ];

    try {
      $this->validate($param, $rule);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }

    $info = Db::name('role')->where('id', $param['id'])->field('id, name, remarks')->find();

    if ($info) {
      $adminMenu = Db::name('role_menu')->alias('rm') // 菜单（规则）角色关联表
        ->join('admin_menu am','am.id = rm.menu_id') // 菜单（规则）表
        ->where('rm.role_id', $param['id'])
        ->order('am.order')->field('am.*')
        ->distinct(true)->select()->toArray();

      $menuTree = arrayToTree($adminMenu);
      $leafs = getAllLeaf($menuTree);

      $info['menu_ids'] = [];
      foreach ($leafs as $leaf) {
        array_push($info['menu_ids'], $leaf['id']);
      }
    }


    $this->success('', $info);
  }

  /**
   * 新增/编辑角色
   */
  public function roleEdit()
  {
    $this->getAdminId();
    $param = $this->request->param();

    $rule = [
      'id'      => 'integer',
      'name'    => 'require|length:4,32',
      'remarks' => 'max:255',
      'menuIds' => 'array',
    ];

    if (isset($param['id'])) {
      if ($param['id'] === 1) {
        $this->error('超级管理员不可被编辑');
      }
      unset($rule['password']);
    }

    $message = [
      'name.require'  => '请填写角色名称',
      'name'          => '角色名称的长度只能在4~32个字符之间',
      'remarks'       => '角色描述最多不能超过255个字符',
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
        'remarks' => $param['remarks'],
      ];

      
      if (isset($param['id'])) {
        // 编辑
        $id = $param['id'];
        Db::name('role')
          ->where('id', $id)
          ->update($data);

        Db::name('role_menu')
          ->where('role_id', $param['id'])
          ->delete();
      } else {
        // 新增
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = $data['create_time'];
        $id = Db::name('role')->insertGetId($data);
        if (!$id) {
          throw new \Exception("角色新增失败");
        }
      }

      // 生成菜单关联
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

      // 提交事务
      Db::commit();
    } catch (\Exception $e) {
      $this->error($e->getMessage());
      // 回滚事务
      Db::rollback();
    }

    // 写入日志
    if (isset($param['id'])) {
      $role = Db::name('role')->where('id', $param['id'])->find();
      writeOperationLog($this->adminId, " 修改了角色：{$role['name']}", 3, json_encode($param));
    } else {
      writeOperationLog($this->adminId, " 新增了角色：${param['name']}", 3, json_encode($param));
    }
    
    $this->success();
  }

  /**
   * 删除角色数据
   */
  public function roleDel()
  {
    $this->getAdminId();
    $param = $this->request->param();

    $rule = [
      'id'  => 'require|integer',
    ];

    try {
      $this->validate($param, $rule);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }

    if ($param['id'] === 1) {
      $this->error('超级管理员不可被删除');
    }

    Db::name('role')->where('id', $param['id'])->useSoftDelete('delete_time', date('Y-m-d H:i:s'))->delete();

    // 写入日志
    $role = Db::name('role')->where('id', $param['id'])->find();
    writeOperationLog($this->adminId, "删除了角色：{$role['name']}", 3, json_encode($param));

    $this->success();
  }

  /**
   * 操作记录列表
   */
  public function operationLog()
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

    $where = [];

    if (isset($param['search'])) {
      $where[] = ['log.content', 'like', "%${param['search']}%"];
    }

    if (isset($param['dateRange'])) {
      $where[] = ['log.create_time', 'between time', [$param['dateRange'][0] . '00:00:00', $param['dateRange'][1] . '23:59:59']];
    }

    if (isset($param['moduleId'])) {
      $where[] = ['log.module_id', '=', $param['moduleId']];
    }
    
    $list = Db::name('operation_log')->alias('log')
      ->join('admin admin','admin.id = log.user_id')
      ->join('operation_module module','module.id = log.module_id')
      ->field('log.id, log.content, log.ip, log.create_time, admin.account, admin.name, module.name as module_name')
      ->where($where)
      ->order('id desc')->paginate([
        'list_rows' => $num,
        'page'      => $page,
      ]);

    $this->success('', $list);
  }
}
