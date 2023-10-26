<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;
use Services\JwtService;

class JwtController extends BaseController
{
    use ResponseTrait;
    
    private JwtService $jwtService;
    
    public function __construct() 
    {
        $this->jwtService = new JwtService;
    }
    
    public function refresh()
    {
        try {
            $response = $this->jwtService->refresh($this->request->getJSON()->refresh);
            return $this->respond($response);
        } catch (InvalidArgumentException $e) {
            return $this->respond([
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    public function getWsJwt(): ResponseInterface
    {
        return $this
                ->respond($this
                        ->jwtService
                        ->getWsJwt($this->request->auth->id));
    }
}
