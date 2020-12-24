<?php

declare(strict_types=1);

namespace app\dashboard\controller;

use app\dashboard\BaseController;

class Index extends BaseController
{
  public function index()
  {
    $this->success('success');
  }
}
