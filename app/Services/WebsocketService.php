<?php

namespace Services;

use phpcent\Client;

class WebsocketService
{
    public function updateStatus(int $userId, $status)
    {
        $this->send($userId, 'updateStatus', $status);
    }
    
    public function updateUserInfo(int $userId, $userInfo)
    {
        $this->send($userId, 'updateUserInfo', $userInfo);
    }
    
    public function send(int $userId, string $action, $data) {
        $client = new Client(getenv('WS_API_URL'));
        $client->setApiKey(getenv('WS_API_KEY'));
        $client->publish("user#{$userId}", [
            'action' => $action,
            'data' => $data
        ]);
    }
}
