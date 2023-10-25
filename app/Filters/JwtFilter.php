<?php

namespace App\Filters;

use CodeIgniter\Config\Services;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Services\JwtService;
use Throwable;

class JwtFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $token = $_SERVER['HTTP_TOKEN'] ?? '';
        $jwtService = new JwtService();
        try {
            $data = $jwtService->decodeJwt($token);
        } catch (Throwable $e) {
            $response = Services::response();
            $response->setStatusCode(401);
            $response->setJSON([
                'message' => $e->getMessage()
            ]);
            return $response;
        }
        
        if (!isset($data->data)) {
            $response = Services::response();
            $response->setStatusCode(401);
            $response->setJSON([
                'message' => 'Invalid token'
            ]);
        }
        
        $request->auth = (object)[
            'id' => $data->sub,
            'username' => $data->data->username
        ];
        return $request;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
