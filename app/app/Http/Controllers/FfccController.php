<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\ProcessJsonDbTrait;
class FfccController extends Controller
{
    use ProcessJsonDbTrait;
    public function formControladoLicencia(Request $request){


        $idTipoLicencia = $request["idTipoLicencia"];
        $arr = [];

        try {
            $file = $this->processJson('relacionLicenciaFormulario');
                $results = array_filter($file, function($tipoLicencia) use ($idTipoLicencia, &$arr) {
                    if($tipoLicencia['id_tipo_licencia'] == $idTipoLicencia)
                    {
                        $arr["nombreArchivo"] = $tipoLicencia['nombreArchivo'];
                        return $tipoLicencia['nombreArchivo'];
                    }

                });

            $formControlado = $this->processJson($arr["nombreArchivo"]);
            return $formControlado["components"];



            } catch (\Throwable $th) {
                //throw $th;
                return $th;
            }

    }

    public function getTipoLicencias()
    {
        try {

        }catch(\Throwable $e ){

        }
    }
}


