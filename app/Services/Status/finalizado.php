<?php

namespace App\Services\Status;

use App\Services\ActionsService;

class finalizado implements StatusInterface
{
    public function getPossibleState()
    {
        return['finalizado','notificado'];
    }

    public function licenseStateAction($idLicense)
    {
        // TODO: Implement stateAction() method.
    }
}
