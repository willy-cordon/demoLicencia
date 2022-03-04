<?php

namespace App\Services;
use App\Traits\ProcessJsonDbTrait;
use App\Traits\SaveJsonDbTrait;
use App\Services\StepLicenseService;

/**
 * Class AprobadorService
 * @package App\Services
 */
class AprobadorService
{
    use ProcessJsonDbTrait,SaveJsonDbTrait;

    private $stepLicenseService;

    public function __construct(StepLicenseService $stepLicenseService)
    {
        $this->stepLicenseService = $stepLicenseService;
    }

    public function buscarGrupoAprobador($idLicencia)
    {
        $workflowLicencia = $this->processJson('workflowLicencia');
        $workflowPasos = $this->processJson('workFlowStep');
        $idGrupoAprobador= '';
        $idPaso= '';
        foreach ($workflowLicencia as $datos ) {
            if($datos['id_licencia'] == $idLicencia){
                $idPaso = $datos['paso_actual'];
               foreach ($workflowPasos as $paso)
               {
                   if($paso == $idPaso ){
                       $idGrupoAprobador = $paso['id_grupo_aprobador'];
                   }
               }
            }
        }

        $aprobadores = $this->stepLicenseService($idGrupoAprobador);

        $aprobadoresActivos = [];
        foreach ($aprobadores as $aprobador)
        {
            if ($aprobador['activo']){
                $aprobadoresActivos[] = $aprobador;
            }
        }

        if (count($aprobadoresActivos) != 0)
        {
            $this->cerrarPaso($idPaso);
        }else{
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
            $arr2 = [];
            foreach ($workFlowPasos as $key=>$datos ) {
                if($datos['id'] == $idPaso){
                    $arr2['id']= $datos['id'] ;
                    $arr2['estados']= $datos['estados'] ;
                    $arr2['accion']= $datos['accion'] ;
                    $arr2['id_grupo_aprobador']= $datos['id_grupo_aprobador'] ;
                    $arr2['paso'] = $datos['paso'];
                    $arr2['situacion'] = true;
                    $arr2['mensaje'] = 'paso cerrado x motivo';
                }
            }

            $this->saveFileJson('workflowLicencia',json_encode($arr2));
        }catch(\Throwable $exception){
            return $exception;
        }
    }
}
