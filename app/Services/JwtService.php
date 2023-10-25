<?php

namespace Services;

use App\Entities\User;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;
use Config\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;

class JwtService
{
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function generateJwtForUser(User $user)
    {
        $key = getenv('JWT_KEY');
        $token = JWT::encode([
            'sub' => $user->id,
            'data' => [
                'username' => $user->username,
            ],
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
        
        $db = Database::connect();
        $db->table('refresh_tokens')->insert([
            'token' => $refresh,
            'expires' => Time::now()->addDays(5)->format('Y-m-d H:i:s')
        ]);
        
        return [
            'token' => $token,
            'refresh' => $refresh
        ];
    }
    
    public function decodeJwt(string $token)
    {
        $key = getenv('JWT_KEY');
        try {
            $data = JWT::decode($token, new Key($key, 'HS256'));
        } catch(\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
        return $data;
    }
    
    public function refresh(string $refreshToken)
    {
        $data = $this->decodeJwt($refreshToken);
        $db = Database::connect();
        $exists = $db->table('refresh_tokens')
                ->where('token', $refreshToken)
                ->countAllResults() > 0;
        if (!$exists) {
            throw new InvalidArgumentException('Unknown token');
        }
        $user = $this->userModel->find($data->sub);
        $db->table('refresh_tokens')
                ->where('token', $refreshToken)
                ->delete();
        
        return $this->generateJwtForUser($user);
    }
}
