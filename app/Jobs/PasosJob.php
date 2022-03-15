<?php

namespace App\Jobs;

use App\Services\AprobadorService;
use Illuminate\Support\Facades\Log;

class PasosJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $idLicencia;
    private $aprobadorService;
    public function __construct($idLicencia)
    {
        $this->idLicencia = $idLicencia;
        $this->aprobadorService =new AprobadorService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->aprobadorService->buscarGrupoAprobador($this->idLicencia);
        /**
         * ? Si tiene aprobador se registra un nuevo trabajo para verificar sus aprobadores
         * @return boolean
         */
        $data ? dispatch(new PasosJob($this->idLicencia)) : $this->delete();

    }
}
