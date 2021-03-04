<?php
// 这是系统自动生成的公共文件

use think\facade\Db;

/**
 * 获取指定账号的菜单及权限
 * @param int $adminId  管理员ID
 * @return string
 */
function getMenu(int $adminId)
{
  if ($adminId === 1) {
    $adminMenu = Db::name('admin_menu')->order('order')
    ->field('id, name, rule, parent_id, icon, type')
    ->select()->toArray();
  } else {
    $adminMenu = Db::name('role_menu')->alias('rm') // 菜单（规则）角色关联表
      ->join('admin_menu am','am.id = rm.menu_id') // 菜单（规则）表
      ->join('role_admin ra','ra.role_id = rm.role_id') // 管理员角色关联表
      ->where('ra.admin_id', $adminId)
      ->order('am.order')->field('am.id, am.name, am.rule, am.parent_id, am.icon, am.type, am.order')
      ->distinct(true)->select()->toArray();
  }

  $menuList = [];
  $authList = [];

  foreach ($adminMenu as $auth) {
    array_push($authList, $auth['rule']);

    if ($auth['type'] === 1) {
      unset($auth['order']);
      array_push($menuList, $auth);
    }
  }

  return [
    'menu'  => arrayToTree($menuList),
    'auth'  => $authList,
  ];
}

/**
 * 写入日志
 * @param int $adminId    管理员ID
 * @param string $content 操作内容
 * @param int $module     操作模块 1为其他
 * @param string $rawData 操作原始数据
 * @return string
 */
function writeOperationLog(int $adminId, string $content, int $module = 1, string $rawData = '')
{
  $data = [
    'user_id'     => $adminId,
    'module_id'   => $module,
    'content'     => $content,
    'ip'          => getIP(),
    'create_time' => date('Y-m-d H:i:s'),
    'raw_data'    => $rawData
  ];

  Db::name('operation_log')->insert($data);
}

/**
 * 获取IP地址
 * @return string
 */
function getIP(){
  $forwarded = request()->header("x-forwarded-for");
  if($forwarded){
      $ip = explode(',',$forwarded)[0];
  }else{
      $ip = request()->ip();
  }
  return $ip;
}
