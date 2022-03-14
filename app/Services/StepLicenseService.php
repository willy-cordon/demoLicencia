<?php

namespace App\Services;
use App\Services\ActionsService;
use App\Traits\ProcessJsonDbTrait;
use App\Services\Status\StatusValues;
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
    private $statusValues;
    public function __construct(ActionsService $actionService, StatusValues $statusValues)
    {
        $this->statusValues = $statusValues;
        $this->actionService = $actionService;
    }

    public function getNumberStep($request)
    {

    }

    /**
     * createStep
     *
     * @param  mixed $pasos
     * @param  mixed $grupos
     * @return void
     * * Aca se identifican cuales seran los pasos que seran necesarios para autorizar la licencia
     */
    public function createStep($pasos,$grupos,$id_licencia){
        $i = 1;
        $datosPaso = [];
        $datoTotal = [];
        foreach ($pasos[0] as $paso){
            $etiqueta_licencia = $paso['aprobadores_etiqueta'];
            $convertCollect = collect($grupos);
            $arrColectGrup = $convertCollect->where('id_etiqueta','=',$etiqueta_licencia)->first();
            if(!empty($arrColectGrup)){
                $datosPaso[$i] = $this->separarPasos($paso,$arrColectGrup);
                $i++;
            }else{
                if($paso['fijo_etiqueta']){
                    $grupoFijo = $this->relacionGrupoEtiqueta($etiqueta_licencia);
                    $datosPaso[$i] = $this->separarPasos($paso,$grupoFijo);
                    $i++;
                }
            }
        }
        $datoTotal['paso'] = $datosPaso;
        $datoTotal['paso_actual'] = $this->processStep($id_licencia,$datosPaso);
        return $datoTotal;
    }

    /**
     * separarPasos
     *
     * @param  mixed $arrayPaso
     * @param  mixed $arrColectGrup
     * @return void
     * * Esta funcion separa los datos que son necesarios para identificar cada dato del paso
     */
    public function separarPasos($arrayPaso,$arrColectGrup){
        $arrayGrupo = [];
        $arrayGrupo['grupo'] = $arrColectGrup['name'];
        $arrayGrupo['grupo_id'] = $arrColectGrup['id'];
        $arrayGrupo['aprobadores'] = $this->relacionGrupoAprobador($arrColectGrup['id']);
        $arrayGrupo['aprueba'] = $arrayPaso["aprueba"];
        if($arrayPaso["aprueba"]){
            $getStatus = $this->statusValues::Status['pendiente'];
            $arrayGrupo['estados'] = (new $getStatus)->getState();
            $arrayGrupo['todos'] = $arrayPaso["todos"];
        }else{
            $getStatus = $this->statusValues::Status['notificado'];
            $arrayGrupo['estados'] = (new $getStatus)->getState();
        }
        return $arrayGrupo;
    }

    /**
     * relacionGrupoEtiqueta
     *
     * @param  mixed $id_etiqueta
     * @return void
     * * Esta es la relacionGrupoEtiqueta que con el id de la etiqueta podes saber que grupo aprobador es el fijo
     */
    public function relacionGrupoEtiqueta($id_etiqueta){
        try {
            $arrRelGruEti = [];
            $file = $this->processJson('grupo_aprobadores');
            $convertCollect = collect($file);
            $arrRelGruEti =  $convertCollect->where('id_etiqueta',$id_etiqueta)->first();
            return $arrRelGruEti;

        } catch (\Throwable $th) {
            //throw $th;
            return $th;
        }

    }

    /**
     * relacionGrupoAprobador
     *
     * @param  mixed $id
     * @return void
     * * Esta es la relacionGrupoAprobador que con el id del grupo podemos saber que usuarios son los que van a aprobar la licencia
     */
    public function relacionGrupoAprobador($id){
        try {
            $arrRelGruApro = [];
            $arrGruApro = [];
            $fileRelGruApro = $this->processJson('relacion_grupo_aprobador');
            $fileApro = $this->processJson('aprobadores');
            $convertRelGruApro = collect($fileRelGruApro);
            $convertApro = collect($fileApro);
            $arrRelGruApro =  $convertRelGruApro->where('id_grAp',$id)->collect();
            $arrRelGruApro->each(function($index) use(&$arrGruApro,&$convertApro){

                $arrGruApro[] = $convertApro->where('legajo',$index['legajo_aprobador'])->first();
            });
            return $arrGruApro;

        } catch (\Throwable $th) {
            //throw $th;
            return $th;
        }

    }

    /**
     * processStep
     *
     * @param  mixed $data
     * @param  mixed $pasos
     * @return void
     * genera la informacion que se guarda en la base de datos StepLicenseService
     */
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

            $arr['id_workflow']        = $data;

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
