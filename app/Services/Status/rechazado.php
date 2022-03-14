<?php

namespace App\Services\Status;

use App\Enums\Responses;
use App\Services\ActionsService;

class rechazado implements StatusInterface
{
    public function getPossibleState()
    {
        return['finalizado'];
    }

    /**
     * @param $idLicense
     * @return bool|string
     */
    public function licenseStateAction($idLicense)
    {
        $actionService = new ActionsService();

        $workFlowFile = $this->processJson('workflowLicencia');
        $licenseFile = $this->processJson('licencia');
        $collectWorkflowFile = collect($workFlowFile)->first();
        if($collectWorkflowFile['count'] != 0)
            return Responses::FailedResponse;
        $licenseFileCollet = collect($licenseFile);

//        $result = $licenseFileCollet->where('id','=',$collectWorkflowFile['id_licencia'])->collect();
//        $actionService->sendEmails($licenseFileCollet['user_email']);
        return true;
    }
}
