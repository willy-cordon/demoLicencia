<?php

namespace App\Services\Status;

class pendiente implements StatusInterface
{
    public function getState()
    {
        return['pendiente','aprobado','rechazado'];
    }
}
