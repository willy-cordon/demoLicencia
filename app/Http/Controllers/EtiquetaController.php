<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\ProcessJsonDbTrait;
class EtiquetaController extends Controller
{
    use ProcessJsonDbTrait;
    public function etiqueta(){

        try {
            $file = $this->processJson('etiquetas');

                return response($file);
        } catch (\Throwable $th) {
            //throw $th;
            return $th;
        }

    }

    public function aprobadores(){
        try {
            $file = $this->processJson('grupo_aprobadores');

            return response($file);
            //code...
        } catch (\Throwable $th) {
            //throw $th;
            return $th;
        }

    }
}
