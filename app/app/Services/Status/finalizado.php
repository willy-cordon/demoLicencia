<?php

namespace App\Services\Status;

class finalizado implements StatusInterface
{
    public function getState()
    {
        return['finalizado','notificado'];
    }
}
