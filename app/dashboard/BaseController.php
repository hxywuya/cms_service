<?php

declare(strict_types=1);

namespace app\dashboard;

use Jwt;
use think\App;
use think\exception\ValidateException;
use think\exception\HttpResponseException;
use think\facade\Db;
use think\Response;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
  /**
   * Request实例
   * @var \think\Request
   */
  protected $request;

  /**
   * 应用实例
   * @var \think\App
   */
  protected $app;

  /**
   * 是否批量验证
   * @var bool
   */
  protected $batchValidate = false;

  /**
   * 控制器中间件
   * @var array
   */
  protected $middleware = [];

  /**
   * token
   * @var array
   */
  protected $token = null;

  /**
   * 管理员ID
   * @var int
   */
  protected $adminId = null;

  /**
   * 管理员
   * @var int
   */
  protected $adminInfo = null;

  /**
   * 构造方法
   * @access public
   * @param  App  $app  应用对象
   */
  public function __construct(App $app)
  {
    $this->app     = $app;
    $this->request = $this->app->request;

    // 控制器初始化
    $this->initialize();
    $this->initUser();
  }

  // 初始化
  protected function initialize()
  {
  }

  // 初始化用户信息
  protected function initUser()
  {
    $header = $this->request->header();
    if (!isset($header['authorization']) || !$header['authorization']) {
      return;
    }
    $this->token = Jwt::parse($header['authorization']);

    $now = time();

    if (!isset($this->token['exp']) || !isset($this->token['data'])) {
      return;
    }

    if ($now > $this->token['exp']->getTimestamp()) {
      return;
    }

    $this->adminId = $this->token['data']['id'];

    $this->adminInfo = Db::name('admin')
      ->where('id', $this->adminId)
      ->where('delete_time', 'null')
      ->field('password, name, mobile, account, status')->find();
  }

  /**
   * 验证数据
   * @access protected
   * @param  array        $data     数据
   * @param  string|array $validate 验证器名或者验证规则数组
   * @param  array        $message  提示信息
   * @param  bool         $batch    是否批量验证
   * @return array|string|true
   * @throws ValidateException
   */
  protected function validate(array $data, $validate, array $message = [], bool $batch = false)
  {
    if (is_array($validate)) {
      $v = new Validate();
      $v->rule($validate);
    } else {
      if (strpos($validate, '.')) {
        // 支持场景
        [$validate, $scene] = explode('.', $validate);
      }
      $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
      $v     = new $class();
      if (!empty($scene)) {
        $v->scene($scene);
      }
    }

    $v->message($message);

    // 是否批量验证
    if ($batch || $this->batchValidate) {
      $v->batch(true);
    }

    return $v->failException(true)->check($data);
  }

  /**
   * 操作成功跳转的快捷方法
   * @access protected
   * @param  array        $msg    提示信息
   * @param  string|array $data   返回的数据
   * @param  array        $header 发送的Header信息
   * @return void
   */
  protected function success(string $msg = '', $data = null, array $header = [])
  {
    $code   = 1;
    $result = [
      'code' => $code,
      'msg'  => $msg,
      'data' => $data,
    ];

    $header['Access-Control-Allow-Origin']  = '*';
    $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId';
    $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
    $response                               = Response::create($result, 'json')->header($header);
    throw new HttpResponseException($response);
  }

  /**
   * 操作错误跳转的快捷方法
   * @access protected
   * @param  string|array $msg    提示信息
   * @param  string|array $data   返回的数据
   * @param  array        $header 发送的Header信息
   * @return void
   */
  protected function error($msg = '', $data = null, array $header = [])
  {
    $code = 0;
    if (is_array($msg)) {
      $code = $msg['code'];
      $msg  = $msg['msg'];
    }
    $result = [
      'code' => $code,
      'msg'  => $msg,
      'data' => $data,
    ];

    $header['Access-Control-Allow-Origin']  = '*';
    $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId';
    $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
    $response                               = Response::create($result, 'json')->header($header);
    throw new HttpResponseException($response);
  }

  /**
   * 获取管理员ID
   * @access protected
   * @return int
   */
  protected function getAdminId()
  {
    if ($this->token && $this->adminId) {
      
      if (!$this->adminInfo) {
        $this->error([
          'code'  => 10002,
          'msg'   => '该账户不存在或已被删除',
        ]);
      }

      if ($this->adminInfo['status'] !== 1) {
        $this->error([
          'code'  => 10002,
          'msg'   => '该账户已被禁用',
        ]);
      }

      return $this->adminId;
    }

    // 有token为超时
    if ($this->token) {
      $this->error([
        'code'  => 10001,
        'msg'   => '登录已经超时请重新登录',
      ]);
    }

    // 全没有为未登录
    $this->error([
      'code'  => 10001,
      'msg'   => '未登录',
    ]);
  }
}
