<?php

namespace App\Services;

use App\Traits\ProcessJsonDbTrait;
use App\Services\Status\StatusValues;
use Illuminate\Http\Client\Request;

/**
 * Class WorkflowService
 * @package App\Services
 */
class WorkflowService
{
    use ProcessJsonDbTrait;
    private $notificacionService;
    private $statusValues;

    public function __construct(NotificacionService $notificacionService, StatusValues $statusValues)
    {
        $this->notificacionService = $notificacionService;
        $this->statusValues = $statusValues;
    }

    public function processWorkflow($request)
    {
        try {

            $file = $this->processJson('usuarios');
            $legajo = $request['legajo'];
            $idLicencia = $request['id_licencia'];
               $arr = [];
               $results = array_filter($file, function($nrolegajo) use ($legajo, &$arr) {
                   if($nrolegajo['nro_legajo'] == $legajo)
                   {
                       $arr['usuario'] = $nrolegajo;
                   }
                return $nrolegajo['nro_legajo'] == $legajo;
                });
                $grupos= $this->buscargrupoPorLegajo($legajo);

                $tiposDeLicencia = $this->buscarTiposLicencia($idLicencia);

                $pasos = $tiposDeLicencia['pasos'];

                 $i = 1;
                 $arr['Licencia']=$tiposDeLicencia['name'];
                 $arr['Tipo_licencia']=$tiposDeLicencia['tipo'];
                 $arr['Certificado']=$tiposDeLicencia['certificado'];
                 foreach ($pasos[0] as $paso){
                     $etiqueta_licencia = $paso['aprobadores_etiqueta'];
                     $j = 0;

                     foreach ($grupos as $grupo){
                         $etiqueta_grupo= $grupo[0]['id_etiqueta'];
                         if($etiqueta_licencia == $etiqueta_grupo){
                             $arr['pasos'][$i][$j]['grupo'] = $grupo[0]["name"];
                             $arr['pasos'][$i][$j]['aprobadores'] = $this->relacionGrupoAprobador($grupo[0]['id']);
                             $arr['pasos'][$i][$j]['aprueba'] = $paso["aprueba"];
                             if($paso["aprueba"]){
                                 $getStatus = $this->statusValues::Status['pendiente'];
                                 $arr['pasos'][$i][$j]['estados'] = (new $getStatus)->getState();
                                 $arr['pasos'][$i][$j]['todos'] = $paso["todos"];
                             }else{
                                $getStatus = $this->statusValues::Status['notificado'];
                                $arr['pasos'][$i][$j]['estados'] = (new $getStatus)->getState();
                             }
                             $j++;
                         }
                     }

                     if($j==0){
                         if($paso['fijo_etiqueta']){
                             $grupoFijo = $this->relacionGrupoEtiqueta($etiqueta_licencia);
                             $arr['pasos'][$i]['grupo'] = $grupoFijo[0]['name'];
                             $arr['pasos'][$i]['aprobadores'] = $this->relacionGrupoAprobador($grupoFijo[0]['id']);
                             $arr['pasos'][$i]['aprueba'] = $paso["aprueba"];
                             if($paso["aprueba"]){
                                $getStatus = $this->statusValues::Status['pendiente'];
                                $arr['pasos'][$i]['estados'] = (new $getStatus)->getState();
                                 $arr['pasos'][$i]['todos'] = $paso["todos"];
                             }else{
                                $getStatus = $this->statusValues::Status['notificado'];
                                $arr['pasos'][$i]['estados'] = (new $getStatus)->getState();
                             }
                             $i++;
                         }
                     }else{
                         $i++;
                     }

                 }
                return  $arr;


        } catch (\Throwable $th) {

            return $th;
        }
    }


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
            $arr = [];
            $results = array_filter($infoRelacionAprobador, function($relacionAprobador) use ($legajo,&$arr) {
                if($relacionAprobador['legajo_user'] == $legajo){
                    $arr[] = $relacionAprobador['id_grAp'];
                    return $arr;
                }

            });
            $arrGrp = [];
            $arrIdGrp = collect($arr);
            $arrIdGrp->each(function($idGrp) use(&$arrGrp){
                $arrGrp[] = $this->relacionGrupoAprobadores($idGrp);
                //$arrGrp[$idGrp] = $this->relacionGrupoAprobadores($idGrp);
            });

            return $arrGrp;

        }
    }

    public function relacionGrupoAprobadores($id){
        try {
            $arr = [];
            $file = $this->processJson('grupo_aprobadores');
            $results = array_filter($file, function($gpAprobadores) use ($id,&$arr) {
                if($gpAprobadores['id'] == $id){
                    return $arr[] = $gpAprobadores;
                }
            });
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
            $results = array_filter($file, function($gpAprobadores) use ($id_etiqueta,&$arr) {
                if($gpAprobadores['id_etiqueta'] == $id_etiqueta){
                    return $arr[] = $gpAprobadores;
                }
            });
            return $arr;

        } catch (\Throwable $th) {
            //throw $th;
            return $th;
        }

    }

}
