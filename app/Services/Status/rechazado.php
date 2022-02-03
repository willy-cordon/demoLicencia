<?php

namespace App\Services\Status;

class rechazado implements StatusInterface
{
    public function getState()
    {
        return['finalizado'];
    }
}
