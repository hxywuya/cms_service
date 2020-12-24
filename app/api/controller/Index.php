<?php

namespace app\api\controller;

use app\BaseController;
use Jwt;

class Index extends BaseController
{
    public function index()
    {
        return Jwt::generate(['t' => 1]);
    }

    public function parse()
    {
        $text = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOiIxNjA4NzEwNTQ0LjUzNDIxOSIsImRhdGEiOnsidCI6MX19.Md3NtxxkAZQWQMZ9nyIkfdw5BESB2prkMs4zzVVp7nU';
        return json(Jwt::parse(($text)));
    }
}
