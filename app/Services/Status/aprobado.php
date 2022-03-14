<?php

namespace App\Services\Status;

use App\Enums\Responses;
use App\Services\DedicatedServices\MailService;
use App\Traits\ProcessJsonDbTrait;
use App\Services\ActionsService;
use Illuminate\Support\Facades\Log;

class aprobado implements StatusInterface
{
    use ProcessJsonDbTrait;

    /**
     * @return string[]
     */
    public function getPossibleState()
    {
        return['aprobado','notificado','rechazado'];
    }

    /**
     * @param $idLicense
     * @return bool|string
     */
    public function licenseStateAction($idLicense)
    {
        $mailService = new MailService();

        $workFlowFile = $this->processJson('workflowLicencia');
        $licenseFile = $this->processJson('licencia');
        $collectWorkflowFile = collect($workFlowFile)->first();
        if($collectWorkflowFile['count'] != 0)
            return Responses::FailedResponse;
        $licenseFileCollet = collect($licenseFile);

//        $result = $licenseFileCollet->where('id','=',$collectWorkflowFile['id_licencia'])->collect();
        //Todo: Armado de template
        $mailService->sendNotification($licenseFileCollet['user_email']);
        return true;

    }
}
