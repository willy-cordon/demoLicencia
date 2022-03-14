<?php

namespace App\Services;
use App\Traits\ProcessJsonDbTrait;
use App\Traits\SaveJsonDbTrait;
use App\Services\StepLicenseService;
use Illuminate\Support\Facades\Log;

/**
 * Class AprobadorService
 * @package App\Services
 */
class AprobadorService
{
    use ProcessJsonDbTrait,SaveJsonDbTrait;

    private $stepLicenseService;


    public function buscarGrupoAprobador($idLicencia)
    {
        $workflowLicencia = $this->processJson('workflowLicencia');
        $workflowPasos = $this->processJson('workFlowStep');
        $idGrupoAprobador= '';
        $idPaso= '';
        foreach ($workflowLicencia as $datos ) {
            if($datos['id_licencia'] == $idLicencia){
                $idPaso = $datos['paso_actual'];
            }
               foreach ($workflowPasos as $paso)
               {
                   if($paso['id'] == $idPaso ){
                       $idGrupoAprobador = $paso['id_grupo_aprobador'];
                   }
               }
        }

        $aprobadores = $this->getDataAprobadores($idGrupoAprobador);

        $aprobadoresActivos = [];
        foreach ($aprobadores as $aprobador)
        {
            if ($aprobador['activo'] || $aprobador['activo'] == 'true'){
                $aprobadoresActivos[] = $aprobador;
            }
        }

        if (count($aprobadoresActivos) != 0)
        {
            return true;
        }else{
            $this->cerrarPaso($idPaso);
            return false;
            //Notifica
        }


    }

    public function buscarDisponible($request)
    {
        $legajo = $request["legajo"];
        $arr = [];

        try {
            $file = $this->processJson('aprobadores');
                $results = array_filter($file, function($aprobadores) use ($legajo, &$arr) {
                    if($aprobadores['legajo'] == $legajo)
                    {
                        $arr = $aprobadores;
                        return $aprobadores;
                    }

                });

            $count = count($arr);

            if($count != 0){
                if($arr['activo']){
                    return 'Usuario Disponible';
                }else{
                    return 'Usuario no Disponible';
                }
            }else{
                try {
                    $file = $this->processJson('usuarios');
                        $results = array_filter($file, function($usuarios) use ($legajo, &$arr) {
                            if($usuarios['nro_legajo'] == $legajo)
                            {
                                $arr = $usuarios;
                                return $usuarios;
                            }

                        });

                    $count = count($arr);

                    if($count != 0){
                        if($arr['activo']){
                            return 'Usuario Disponible';
                        }else{
                            return 'Usuario no Disponible';
                        }
                    }else{
                        return 'No hay ningun usuario con ese legajo';
                    }

                    } catch (\Throwable $th) {
                        //throw $th;
                        return $th;
                    }
            }

            } catch (\Throwable $th) {
                //throw $th;
                return $th;
            }


    }
    public function cerrarPaso($idPaso)
    {
        try {
            $workFlowPasos = $this->processJson('workFlowStep');
            $workFlowLic = $this->processJson('workflowLicencia');
            $arr2 = [];
            $arr1 = [];
            $idWorkflow = '';
            $arrLic = [];
            foreach ($workFlowPasos as $datos ) {
                $idWorkflow = $datos['id_workflow'];
                if($datos['id'] == $idPaso){
                    $arr1['id']= $datos['id'] ;
                    $arr1['estados']= 'aprobado' ;
                    $arr1['accion']= $datos['accion'] ;
                    $arr1['id_grupo_aprobador']= $datos['id_grupo_aprobador'] ;
                    $arr1['paso'] = $datos['paso'];
                    $arr1['situacion'] = true;
                    $arr1['mensaje'] = 'paso cerrado, no se encontraron aprobadores';
                    $arr2[] = $arr1;
                }else{
                    $arr2[] = $datos;
                }
            }
            foreach($workFlowLic as $lic){
                $countUpdate = $lic['count'] - 1;
                if ($countUpdate == 0){
                    $lic['estado'] = 'aprobado';
                }
                $lic['count'] = $countUpdate;
                $arrLic[] = $lic;
            }
            $this->saveFileJson('workflowLicencia',json_encode($arrLic));

            $this->saveFileJson('workFlowStep',json_encode($arr2));
        }catch(\Throwable $exception){
            return $exception;
        }
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
}
