<?php

namespace App\Services;

use App\Traits\ProcessJsonDbTrait;
use App\Traits\SaveJsonDbTrait;
use App\Services\Status\StatusValues;
use App\Services\StepLicenseService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Log;
use App\Jobs\PasosJob;

/**
 * Class WorkflowService
 * @package App\Services
 */
class WorkflowService
{
    use ProcessJsonDbTrait,SaveJsonDbTrait;
    private $notificacionService;
    private $statusValues;
    private $stepLicenseService;
    private $actionsService;

    public function __construct( StatusValues $statusValues, StepLicenseService $stepLicenseService, ActionsService $actionsService)
    {
        $this->statusValues = $statusValues;
        $this->stepLicenseService = $stepLicenseService;
        $this->actionsService = $actionsService;
    }

    /**
     * processWorkflow
     *
     * @param  mixed $request
     * @return void
     * * Esta función genera los pasos de la licencia y los guarda en json
     * ? informativo
     * ! error
     */
    public function processWorkflow($request)
    {
        try {

            $file = $this->processJson('usuarios');
            $legajo = $request['legajo'];
            $idLicencia = $request['id_licencia'];
            $datosSolicitud = []; // $datosSolicitud
            $convertCollect = collect($file);
            $datosSolicitud['usuario'] = $convertCollect->where('nro_legajo','=',$legajo)->first();
            $grupos= $this->buscargrupoPorLegajo($legajo);

            $tiposDeLicencia = $this->buscarTiposLicencia($idLicencia);

            $pasos = $tiposDeLicencia['pasos'];

            $i = 1;
            $datosSolicitud['Licencia']=$tiposDeLicencia['name'];
            $datosSolicitud['Tipo_licencia']=$tiposDeLicencia['tipo'];
            $datosSolicitud['Certificado']=$tiposDeLicencia['certificado'];
            foreach ($pasos[0] as $paso){
                $etiqueta_licencia = $paso['aprobadores_etiqueta'];
                $convertCollect = collect($grupos);
                $arrColectGrup = $convertCollect->where('id_etiqueta','=',$etiqueta_licencia)->first();
                if(!empty($arrColectGrup)){
                    $datosSolicitud['pasos'][$i] = $this->separarPasos($paso,$arrColectGrup);
                    $i++;
                }else{
                    if($paso['fijo_etiqueta']){
                        $grupoFijo = $this->relacionGrupoEtiqueta($etiqueta_licencia);
                        $datosSolicitud['pasos'][$i] = $this->separarPasos($paso,$grupoFijo);
                        $i++;
                    }
                }
            }

            //return $datosSolicitud;
            /**
             * * Save License
             */
            $g = $this->guardarDatos($datosSolicitud);
            /**
             * * Save Steps
             * ! Append or replace
             */
            $licenseArray['id'] = uniqid();
            $licenseArray['id_licencia'] = $g;
            $licenseArray['count'] = count($datosSolicitud['pasos']);
            //$licenseArray['pasos'] =$datosSolicitud['pasos'];
            $licenseArray['estado']='pendiente';



            $licenseArray['paso_actual'] = $this->stepLicenseService->processStep($licenseArray,$datosSolicitud['pasos']);
            $licArr[]=$licenseArray;
            $saveJson = $this->saveFileJson('workflowLicencia',json_encode($licArr));
//            $datosSolicitud['idLicencia'] = $g;
            array_unshift($datosSolicitud,['idLicencia'=>$g]);
            return  $datosSolicitud;


        } catch (\Throwable $th) {

            return $th;
        }
    }

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
     * guardarDatos
     *
     * @param  mixed $array
     * @return void
     */
    public function guardarDatos($array){

        $arr = [];

        $arr['id']          = uniqid();
        $arr['nombre']      = $array['Licencia'];
        $arr['tipo']        = $array['Tipo_licencia'];
        $arr['certificado'] = $array['Certificado'];
        $arr['user_name']   = $array['usuario']['nombre'];
        $arr['user_legajo'] = $array['usuario']['nro_legajo'];
        $arr['user_email'] = $array['usuario']['email'];
        $this->saveFileJson('licencia',json_encode($arr));
        return $arr['id'];
    }


    /**
     * buscarTiposLicencia
     *
     * @param  mixed $idLicencia
     * @return void
     */
    public function buscarTiposLicencia($idLicencia){

        $fileSEE = storage_path('json/tipo_licencias.json');
        $arr = [];
        if($fileSEE){

            $archivo = json_decode(file_get_contents($fileSEE),true);


            $results = array_filter($archivo, function($licencia) use ($idLicencia,&$arr) {

                if($licencia['id'] == $idLicencia){
                    $arr = $licencia;
                    return $arr;
                }
            });


            return $arr;

        }

    }


    public function buscargrupoPorLegajo($legajo){
        $fileRelacionAprobador = storage_path('json/relacion_aprobador.json');
        if($fileRelacionAprobador){
            $infoRelacionAprobador = json_decode(file_get_contents($fileRelacionAprobador),true);
            $convertCollect = collect($infoRelacionAprobador);
            $data =  $convertCollect->where('legajo_user',$legajo)->collect();
            $arrGrp = [];
            $data->each(function($index,$idGrp) use(&$arrGrp){
                $arrGrp[] = $this->relacionGrupoAprobadores($index['id_grAp']);
            });
            return $arrGrp;
        }
    }

    public function relacionGrupoAprobadores($id){
        try {
            $arr = [];
            $file = $this->processJson('grupo_aprobadores');
            $convertCollect = collect($file);
            $arr =  $convertCollect->where('id',$id)->first();
            return $arr;

        } catch (\Throwable $th) {
            //throw $th;
            return $th;
        }

    }

    public function relacionGrupoAprobador($id){
        try {
            $arr = [];
            $arr2 = [];
            $file = $this->processJson('relacion_grupo_aprobador');
            $results = array_filter($file, function($gpAprobadores) use ($id,&$arr2) {
                if($gpAprobadores['id_grAp'] == $id){
                    return $arr2[] = $gpAprobadores['legajo_aprobador'];
                }
            });


            foreach($arr2 as $k => $ar)
            {
                $file2 = $this->processJson('aprobadores');
                $results2 = array_filter($file2, function($aprobadores) use (&$arr,$ar,$k) {
                    if($ar == $aprobadores['legajo']){
                        $arr[$k]['legajo'] = $ar;
                        $arr[$k]['email'] = $aprobadores['email'];
                        $arr[$k]['nombre'] = $aprobadores['nombre'];
                    }
                });
            }

            return $arr;

        } catch (\Throwable $th) {
            //throw $th;
            return $th;
        }

    }

    public function relacionGrupoEtiqueta($id_etiqueta){
        try {
            $arr = [];
            $file = $this->processJson('grupo_aprobadores');
            $convertCollect = collect($file);
            $arr =  $convertCollect->where('id_etiqueta',$id_etiqueta)->first();
            return $arr;

        } catch (\Throwable $th) {
            //throw $th;
            return $th;
        }

    }
    /*
                     ###
                      ##
          ### ##      ##   ######
         ##  ##    #####    ##  ##
         ##  ##   ##  ##    ##  ##
          #####   ##  ##    #####
             ##    ######   ##
         #####             ####
     */

    /**
     * @return array|\Exception|mixed|\Throwable
     * ! Save id step
     * ? Array processWorkflow
     */
    public function processWorkflowLicencia($request)
    {
        try{
            $id_licencia = $request['id_licencia'];

            $workFlow = $this->processJson('workflowLicencia');
            $workFlowStep = $this->processJson('workFlowStep');

            switch ($workFlow[0]['estado']) {
                case 'aprobado':
                    return 'Licencia Aprobada';
                    break;
                case 'rechazado':
                    return 'Licencia Rechazada';
                    break;
                case 'pendiente':

                    $arr = "";
                    $arrAct = [];
                    $arrPaso = [];
                    $results = array_filter($workFlow, function($work) use ($id_licencia, &$arr) {
                        if($work['id_licencia'] == $id_licencia)
                        {
                            $arr= $work['paso_actual'] ;
                        }
                    });

                    /**
                     * ProcessCurrent
                     * ? $aprueba, dato para la funcion recursiva
                     * @return * informacion del paso actual
                     */
                    $processCurrent = $this->processWorkflowAccion($arr);
                    Log::debug($processCurrent);
                    $aprueba = $processCurrent['accion'];
                    $aprueba != 'notifica' ?  dispatch(new PasosJob($id_licencia)) : '';
                    $arrLic=[];
                    if($arr == $processCurrent['id']){
                        if($processCurrent['situacion']){

                            if($aprueba == 'notifica'){

                                foreach($workFlow as $lic){
                                    $countUpdate = $lic['count'] - 1;
                                    if ($countUpdate == 0){
                                        $lic['estado'] = 'aprobado';
                                    }
                                    $lic['count'] = $countUpdate;
                                    $arrLic[] = $lic;
                                }
                                $this->saveFileJson('workflowLicencia',json_encode($arrLic));
                                //TODO descontar paso de notificacion
                                foreach ($workFlowStep as $workflowS){
                                    if($workflowS['id'] == $processCurrent['id'])
                                    {
                                        $workflowS['estados'] = 'aprobado';
                                    }
                                    $arrPaso[] = $workflowS;
                                }
                                $this->saveFileJson('workFlowStep',json_encode($arrPaso));

                                $processCurrentAction = $this->stepLicenseService->getDataAprobadores($processCurrent['id_grupo_aprobador']);
                                $notification = $this->actionsService->sendNotification($processCurrentAction);
//                                $this->processWorkflowLicencia($request);
                            }else{
//                                dispatch(new PasosJob($id_licencia));
                            }

                        }

                    }else{
                        $results = array_filter($workFlow, function($work) use ($id_licencia, &$arrAct) {
                            if($work['id_licencia'] == $id_licencia)
                            {
                                $arrAct[]= $work ;
                            }
                        });
                        $arrAct[0]['paso_actual'] = $processCurrent['id'];
                        $saveJson = $this->saveFileJson('workflowLicencia',json_encode($arrAct));
                        $aprueba = $processCurrent['accion'];
                    }

                    /**
                     * * Si lo que devuelve el paso requiere aprobacion se corta la ejecucion
                     */

                    if ($aprueba!='aprueba'){
                        Log::debug('notifica');
                        return $this->processWorkflowLicencia($request);
                    }else{
                        $aprobadores = $this->stepLicenseService->getDataAprobadores($processCurrent['id_grupo_aprobador']);
                        $processCurrent['aprobadores'][] = $aprobadores;
                        return $processCurrent;
                    }
                    break;

                default:
                    return 'No existe el estado ';
                    break;
            }
        }catch (\Throwable $exception){
            return $exception;
        }
    }
    public function processWorkflowAccion($pasoActual)
    {
        Log::debug('process flow accion');
        try{
            $workFlowPasos = $this->processJson('workFlowStep');
            $workFlowLic = $this->processJson('workflowLicencia');
            $paso = (string)$pasoActual;
            $arr2 = [];
            foreach ($workFlowPasos as $key=>$datos ) {
                if($datos['id'] == $paso){
                    $arr2['id']= $datos['id'] ;
                    $arr2['estados']= $datos['estados'] ;
                    $arr2['accion']= $datos['accion'] ;
                    $arr2['id_grupo_aprobador']= $datos['id_grupo_aprobador'] ;
                    $arr2['paso'] = $datos['paso'];
                    $arr2['situacion'] = true;
                }
            }

            $arrLic = [];
            switch ($arr2['estados']) {
                case 'aprobado':

                    $sigPaso = $arr2['paso']+1;
                    foreach ($workFlowPasos as $key=>$datos ) {

                        if($datos['paso'] == $sigPaso){
                            $arr2['id']= $datos['id'] ;
                            $arr2['estados']= $datos['estados'] ;
                            $arr2['accion']= $datos['accion'] ;
                            $arr2['id_grupo_aprobador']= $datos['id_grupo_aprobador'] ;
                            $arr2['paso'] = $datos['paso'];
                            $arr2['situacion'] = true;
                        }
                    }
                    return $arr2;
                    break;
                case 'rechazado':
                    $arr2['situacion'] = false;
                    return $arr2;
                    break;
                default:
                    return $arr2;
                    break;
            }


        }catch (\Throwable $exception){
            return $exception;
        }
    }

    public function aprobarPasoInterface($data)
    {
        return $this->stepLicenseService->aprobarPaso($data);
    }



}
