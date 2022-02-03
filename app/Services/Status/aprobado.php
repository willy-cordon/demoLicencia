<?php

namespace App\Services\Status;

class aprobado implements StatusInterface
{
    public function getState()
    {
        return['aprobado','notificado','rechazado'];
    }
}
