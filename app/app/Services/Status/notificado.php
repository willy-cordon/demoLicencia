<?php

namespace App\Services\Status;

class notificado implements StatusInterface
{
    public function getState()
    {
        return['notificado','finalizado'];
    }
}
