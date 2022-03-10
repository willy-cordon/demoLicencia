<?php

namespace App\Http\Controllers;


use App\Services\WorkflowService;
use Illuminate\Http\Request;
use App\Traits\ProcessJsonDbTrait;
use Illuminate\Support\Facades\Log;

class workflowController extends Controller
{

    protected $workflowService;
    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }


    use ProcessJsonDbTrait;
    public function buscarPasosLegajo(Request $request)
    {

        try {
            return $this->workflowService->processWorkflow($request);
        } catch (\Throwable $th) {
            return $th;
        }
    }

        public function getNumberStep(Request $request)
        {
            try{
                return $this->workflowService->getProcessNumberStep($request);
            }catch(\Throwable $e){
                return $e;
            }
        }

        public function procesarLicencia(Request $request)
        {

            try {
                return $this->workflowService->processWorkflowLicencia($request);
            } catch (\Throwable $th) {
                return $th;
            }
        }

        /**
         * aprobarPaso
         *
         * @param  mixed $request
         * @return void
         */
        public function aprobarPaso(Request $request)
        {
            try {
                return $this->workflowService->aprobarPasoInterface($request);
            }catch (\Throwable $exception){
                return $exception;
            }
        }










    }
