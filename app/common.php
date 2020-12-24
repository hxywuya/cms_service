<?php

declare(strict_types=1);
// 应用公共文件

/**
 * 密码加密
 * @param string $pw       要加密的原始密码
 * @param string $authCode 加密字符串
 * @return string
 */
function password(string $pw, string $authCode = '')
{
  if (empty($authCode)) {
    $authCode = config('database.authcode');
  }
  return md5(md5($authCode . $pw));
}

/**
 * 密码比较方法,所有涉及密码比较的地方都用这个方法
 * @param string $password     要比较的密码
 * @param string $passwordInDb 数据库保存的已经加密过的密码
 * @return boolean 密码相同，返回true
 */
function compare_password(string $password, string $passwordInDb)
{
  return password($password) == $passwordInDb;
}

/**
 * 数组转为树
 * @param array   $data     需要转换的数组
 * @param int     $parentId 父级ID
 * @param string  $children 孩子节点字段名 默认children
 * @return array
 */
function arrayToTree(array $data, int $parentId = 0, string $children = 'children') {
  $trees = [];
	foreach ($data as $key => $item) {
		if( $item['parent_id'] == $parentId )
		{
			$item[$children] = arrayToTree($data, $item['id']);
			$trees[] = $item;
		}
	}
	return $trees;
}
