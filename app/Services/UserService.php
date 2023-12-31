<?php

namespace Services;

use App\Entities\User;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;
use Config\Database;

class UserService
{
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function isOnline(User $user): bool
    {
        return $user->is_online 
                && Time::now()->subMinutes(5)
                ->isBefore($user->last_online);
    }
    
    public function getOnlineUsersCount(): int
    {
        return $this->userModel->builder()
                ->where('is_online', true)
                ->where('last_online >', Time::now()->subMinutes(5))
                ->countAllResults();
    }
    
    public function getOnlineUsers()
    {
        return $this->userModel->builder()
                ->where('is_online', true)
                ->where('last_online >', Time::now()->subMinutes(5))
                ->get();
    }
    
    public function getInfo(int $userId)
    {
        $user = $this->userModel->find($userId);
        
        return [
            'id' => $user->id,
            'username' => $user->username,
            'victories' => $user->victories,
            'defeats' => $user->defeats,
        ];
    }
    
    public function updateOnline(int $userId)
    {
        Database::connect()->table('users')->where('id', $userId)
                ->update([
                    'is_online' => true,
                    'last_online' => Time::now()
                ]);
    }
}
