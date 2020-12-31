<?php

declare(strict_types=1);

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token\Plain;

class Jwt
{

  /**
   * 生成Token
   * @access static
   * @param  array        $data     数据
   * @param  string       $key      嫡
   * @return string
   */
  static function generate(array $data = [], string $key = '')
  {
    $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($key));
    assert($config instanceof Configuration);

    $now = new DateTimeImmutable();
    $token = $config->builder()
      ->expiresAt($now->modify('+7 days')) // 过期时间
      ->withClaim('data', $data) // 数据
      // Builds a new token
      ->getToken($config->signer(), $config->signingKey());
    return $token->toString();
  }

  /**
   * 解析Token
   * @access static
   * @param  string       $token    TOKEN字符串
   * @param  string       $key      嫡
   * @return array|fasle
   */
  static function parse(string $token, string $key = '')
  {
    $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($key));
    assert($config instanceof Configuration);

    $token = $config->parser()->parse($token);

    assert($token instanceof Plain);

    return $token->claims()->all();
  }
}
