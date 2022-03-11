<?php

namespace App\Services;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Traits\ProcessJsonDbTrait;
use App\Traits\SaveJsonDbTrait;
use App\Services\StepLicenseService;
use App\Services\ActionsService;
/**
 * Class NotificacionService
 * @package App\Services
 */
class NotificacionService
{
    use ProcessJsonDbTrait,SaveJsonDbTrait;

    public function sendPost($path,$data,$contentType){
        try {
            $client = new Client();
            $response = $client->post(
                env("NOTIFICATION_SERVICES_PATH").'/'.$path,
                $data,
                ['Content-Type' => $contentType]
            );
            if($response->getStatusCode() == 200){
                $responseJSON = json_decode($response->getBody(), true);
            }
            return $responseJSON;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function sendNotification($data){
        $path='bulkEmailProcessor';
        $contentType='application/json';
        try {
            
            $response=$this->sendPost($path,$data,$contentType);
            return $response;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function actionNotifica($workFlow,$workFlowStep,$processCurrent,$StepLicenseService,$ActionsService){
        try {
            /** **/
            /** Esto debería ir desde paso services **/
            /** **/
            foreach($workFlow as $lic){
                if($lic['paso_actual']==$processCurrent['id']){
                    $countUpdate = $lic['count'];
                    if($lic['count']>0){
                        $countUpdate = $countUpdate - 1;
                    }
                    if ($countUpdate == 0){
                        $lic['estado'] = 'aprobado';
                    }
                    $lic['count'] = $countUpdate;
                    $arrLic[] = $lic;
                }
            }
            $this->saveFileJson('workflowLicencia',json_encode($arrLic));
    
            foreach ($workFlowStep as $workflowS){
                if($workflowS['id'] == $processCurrent['id'])
                {
                    $workflowS['estados'] = 'aprobado';
                }
                $arrPaso[] = $workflowS;
            }
            $this->saveFileJson('workFlowStep',json_encode($arrPaso));
            /** **/
            /**agregando */
            /** Esto debería ir desde paso services **/
            /** **/

            $processCurrentAction = $StepLicenseService->getDataAprobadores($processCurrent['id_grupo_aprobador']);
            $notification =$ActionsService->sendNotification($processCurrentAction);
            $response = response()->json('ok', 200);
            return $response;
        } catch (\Throwable $th) {
            return $th;
        }
    }
}
