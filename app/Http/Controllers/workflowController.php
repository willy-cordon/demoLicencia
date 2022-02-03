<?php

namespace App\Http\Controllers;

use App\Services\WorkflowService;
use Illuminate\Http\Request;
use App\Traits\ProcessJsonDbTrait;

class workflowController extends Controller
{

    protected $workflowService;
    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }


    use ProcessJsonDbTrait;
    public function buscarPasosLegajo(Request $request){

        try {
            return $this->workflowService->processWorkflow($request);
        } catch (\Throwable $th) {
            return $th;
        }

    }






}
