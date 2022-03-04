<?php

namespace App\Http\Controllers;

use App\Services\AprobadorService;
use Illuminate\Http\Request;
use App\Traits\ProcessJsonDbTrait;

class AprobadorController extends Controller
{
    protected $aprobadorservice;
    public function __construct(AprobadorService $aprobadorService)
    {
        $this->aprobadorservice = $aprobadorService;
    }
    public function buscarAprobador(Request $request)
    {
        try {
            return $this->aprobadorservice->buscarDisponible($request);
        } catch (\Throwable $th) {
            return $th;
        }

    }
}
