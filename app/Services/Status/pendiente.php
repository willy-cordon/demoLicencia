<?php

namespace App\Services\Status;

use App\Services\ActionsService;

class pendiente implements StatusInterface
{
    public function getPossibleState()
    {
        return['pendiente','aprobado','rechazado'];
    }

    public function licenseStateAction($idLicense)
    {
        // TODO: Implement stateAction() method.
    }
}
