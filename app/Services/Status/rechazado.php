<?php

namespace App\Services\Status;

use App\Services\ActionsService;

class rechazado implements StatusInterface
{
    public function getPossibleState()
    {
        return['finalizado'];
    }

    public function licenseStateAction($idLicense)
    {
        // TODO: Implement stateAction() method.
    }
}
