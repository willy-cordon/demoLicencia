<?php

namespace App\Services\Status;

use App\Services\ActionsService;

class notificado implements StatusInterface
{
    public function getPossibleState()
    {
        return['notificado','finalizado'];
    }

    public function licenseStateAction($idLicense)
    {
        // TODO: Implement stateAction() method.
    }
}
