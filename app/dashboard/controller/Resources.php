<?php

declare(strict_types=1);

namespace app\dashboard\controller;

use app\dashboard\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Filesystem;

class Resources extends BaseController
{

  public function upload()
  {
    // 获取表单上传文件
    $file = request()->file('image');

    // 1024 = 1kb
    $rule = [
      'image' => 'filesize:1048576|fileExt:jpg,png',
    ];

    $message = [
      'image.filesize'  => '图片大小不能超过1MB',
      'image.fileExt'   => '图片只能是jpg或png',
    ];

    try {
      $this->validate(['image'=> $file ], $rule, $message);
    } catch (ValidateException $e) {
      $this->error($e->getError());
    }

    // 上传到本地服务器
    $savename = \think\facade\Filesystem::disk('public')->putFile('image', $file, 'md5');
    $savename = $this->request->domain() . '/storage/' . str_replace('\\', '/', $savename);
    $this->success('', $savename);
  }
}
