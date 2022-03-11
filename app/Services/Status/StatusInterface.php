<?php

namespace App\Services\Status;

use App\Services\ActionsService;

interface StatusInterface
{
    public function getPossibleState();
    public function licenseStateAction($idLicense);
}
