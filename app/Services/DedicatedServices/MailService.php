<?php

namespace App\Services\DedicatedServices;

use App\Traits\ProcessJsonDbTrait;
use App\Traits\SaveJsonDbTrait;
use GuzzleHttp\Client;
/**
 * Class MailService
 * @package App\Services
 */
class MailService
{
    use ProcessJsonDbTrait,SaveJsonDbTrait;

    public function sendPost($path,$data,$contentType){
        try {
            $client = new Client();
            $response = $client->post(
                env("NOTIFICATION_SERVICES_PATH").'/'.$path,
                $data,
                ['Content-Type' => $contentType]
            );
            if($response->getStatusCode() == 200){
                $responseJSON = json_decode($response->getBody(), true);
            }
            return $responseJSON;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function sendNotification($data){
        $path='bulkEmailProcessor';
        $contentType='application/json';
        try {
            
            $response=$this->sendPost($path,$data,$contentType);
            return $response;
        } catch (\Throwable $th) {
            return $th;
        }
    }
}
