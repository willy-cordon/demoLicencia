<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * Class ActionsService
 * @package App\Services
 */
class ActionsService
{


    public function __construct()
    {
    }

    /**
     * @param $data
     * !add route Process Notification
     * ?
     */
    public function sendNotification($data)
    {
        Log::alert('notnot');
        Log::alert($data);
        try {
            /**
             * * Notification Email
             */
            $client = new Client();
            $response = $client->post(
                '10.12.32.16:8109/bulkEmailProcessor',
                [
                    json_encode($data)
                ],
                ['Content-Type' => 'application/json']
            );
            $responseBoolean = false;

            if($response->getStatusCode() == 200){
                $responseBoolean = true;
                $responseJSON = json_decode($response->getBody(), true);
            }



            /**
             * TODO:Manejar la respuesta solicitando una accion de notificacion hacia la plataforma
             */

            return $responseBoolean;
        }catch (\Throwable $exception){
            return $exception;
        }

    }

    public function updateRHPRO()
    {

    }
}
