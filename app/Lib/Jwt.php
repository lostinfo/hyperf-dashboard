<?php

declare(strict_types=1);

namespace App\Lib;


use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class Jwt
{
    /**
     * @param string $guard_name
     * @param int $jwt_id
     * @param array $attributes
     * @return string
     */
    public function login(string $guard_name, int $jwt_id)
    {
        $signer = new Sha256();
        $token  = (new Builder())
            ->setId('4f1g23a12aa', true)
            ->setIssuedAt(time())
            ->setExpiration(time() + (86400 * 7))
            ->set('guard_name', $guard_name)
            ->set('uid', $jwt_id)
            ->sign($signer, config('jwt_key'))
            ->getToken();
        return (string)$token;
    }

    /**
     * @param $token
     * @param string $guard_name
     * @return bool|int
     */
    public function validateToken($token, string $guard_name)
    {
        try {
            $token = (new Parser())->parse($token);
            $signer = new Sha256();
            if (!$token->verify($signer, config('jwt_key'))) {
                return false;
            };
            if ($guard_name !== $token->getClaim('guard_name')) {
                return false;
            }
            return (int)$token->getClaim('uid');
        } catch (\Exception $exception) {
            Log::get()->info($exception->getMessage());
            return false;
        }
    }
}