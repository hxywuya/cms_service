<?php
// 这是系统自动生成的公共文件

use think\facade\Db;

/**
 * 密码加密
 * @param int $pw       要加密的原始密码
 * @param string $authCode 加密字符串
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
