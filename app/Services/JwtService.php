<?php

namespace Services;

use App\Entities\User;
use CodeIgniter\I18n\Time;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    public function generateJwtForUser(User $user)
    {
        $key = getenv('JWT_KEY');
        $token = JWT::encode([
            'sub' => $user->id,
            'username' => $user->username,
            'exp' => Time::now()->addMinutes(5)->getTimestamp()
        ], $key, 'HS256');
        $refresh = JWT::encode([
            'sub' => $user->id,
            'refresh_data' => [
                'username' => $user->username
            ],
            'exp' => Time::now()->addDays(5)->getTimestamp(),
            'nbf' => Time::now()->addMinutes(5)->getTimestamp()
        ], $key, 'HS256');
        return [
            'token' => $token,
            'refresh' => $refresh
        ];
    }
    
    public function decodeJwt(string $token)
    {
        $key = getenv('JWT_KEY');
        $data = JWT::decode($token, new Key($key, 'HS256'));
        return $data;
    }
}
