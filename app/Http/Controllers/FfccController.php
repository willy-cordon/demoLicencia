<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Traits\ProcessJsonDbTrait;
use function Psy\debug;

class FfccController extends Controller
{
    use ProcessJsonDbTrait;
    public function formControladoLicencia(Request $request){

        $idTipoLicencia = (int) $request["idTipoLicencia"];
        $arr = [];

        try {
            $file = $this->processJson('relacionLicenciaFormulario');
                $results = array_filter($file, function($tipoLicencia) use ($idTipoLicencia, &$arr) {
                    if($tipoLicencia['id_tipo_licencia'] == $idTipoLicencia)
                    {
                        Log::debug('entro en el if');
                        $arr["nombreArchivo"] = $tipoLicencia['nombreArchivo'];
                        return $tipoLicencia['nombreArchivo'];
                    }

                });

            $formControlado = $this->processJson($arr["nombreArchivo"]);
            return $formControlado;



            } catch (\Throwable $th) {
                //throw $th;
                return $th;
            }

    }

    public function getTipoLicencias()
    {
        try {
            $arr =[];
            $files = $this->processJson('tipo_licencias');
            foreach ($files as $file){
                $arr[] = ['id'=>$file['id'],'value'=>$file['name']];

             }
            return $arr;
        }catch(\Throwable $e ){

        }
    }
}


