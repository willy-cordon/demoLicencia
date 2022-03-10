<?php

namespace App\Services;
use App\Services\ActionsService;
use App\Traits\ProcessJsonDbTrait;
use App\Traits\SaveJsonDbTrait;
use Illuminate\Support\Facades\Log;

/**
 * Class StepLicenseService
 * @package App\Services
 */
class StepLicenseService
{
    use ProcessJsonDbTrait,SaveJsonDbTrait;

    private $actionService;
    public function __construct(ActionsService $actionService)
    {
        $this->actionService = $actionService;
    }

    public function getNumberStep($request)
    {

    }

    public function processStep($data,$pasos)
    {
        $arr = [];
        $count = 0;
        foreach ($pasos as $key => $paso)
        {

            $accion = 'notifica';
            if($paso['aprueba']){
                $accion = 'aprueba';
            }
            $arr['id']                 = uniqid();
            if($key==1){
               $id =  $arr['id'] ;
            }

            $arr['paso']               = $key;

            $arr['id_workflow']        = $data['id'];

            $arr['estados']            = 'pendiente';

            $arr['accion']             = $accion;

            $arr['id_grupo_aprobador'] = $paso['grupo_id'];

            $file[]=$arr;

            $this->saveFileJson('workFlowStep',json_encode($file));


        }
        return $id;

    }

    public function getDataAprobadores($idGrupoAprobador)
    {
        $dataRelation = $this->processJson('relacion_grupo_aprobador');
        $dataAprobadores = $this->processJson('aprobadores');
        $arr = [];
        foreach ($dataRelation as $d)
        {
            if ($d['id_grAp'] === $idGrupoAprobador)
            {
                foreach ($dataAprobadores as $apr)
                {
                    if($d['legajo_aprobador'] === $apr['legajo']){
                           $arr[] = $apr;
                    }
                }
            }
        }
        return $arr;
    }

    public function aprobarPaso($request){
        $step = $this->processJson('workFlowStep');
        $license = $this->processJson('workflowLicencia');
        $lic = $this->processJson('licencia');
        $id_paso = $request['id_paso'];
        $aprobacion = $request['aprobacion'];
        $reason = $request['reason'];
        $idLicencia = '';
        $arrLic = [];
        foreach ($step as $s){
            if ($s['id'] == $id_paso){
                foreach ($license as $l) {
                    if ($aprobacion === 'true'){
                        $countUpdate = $l['count'] - 1;
                    }else{
                        $countUpdate = $l['count'];
                    }
                    if ($countUpdate == 0){
                        $l['estado'] = 'aprobado';
                    } else {

                        // Rechaza si es FALSE
                        if ($aprobacion == 'false'){
                            // $data = $this->actionService->sendNotification([$lic['user_email']]);
                            // if ($data){
                                $l['estado'] = 'rechazado';
                                $l['reason'] = $reason;
                            // }
                        }
                    }
                    $l['count'] = $countUpdate;
                    $arrLic[] = $l;
                }
            }
        }
        $this->saveFileJson('workflowLicencia',json_encode($arrLic));

        $mensaje = '';
        foreach ($step as $workflowS){
            if($workflowS['id'] == $id_paso)
            {
                // Aprueba si es 1 es TRUE
                if ($aprobacion === 'true'){
                    $workflowS['estados'] = 'aprobado';
                    $mensaje = 'Licencia Aprobada por el aprobador';
                }

                // Rechaza si es o es FALSE
                if ($aprobacion === 'false'){
                    $workflowS['estados'] = 'rechazado';
                    $mensaje = 'Licencia Rechazada por el aprobador';
                }
            }
            $arrPaso[] = $workflowS;
        }
        $this->saveFileJson('workFlowStep',json_encode($arrPaso));

        return $mensaje;
    }



}
