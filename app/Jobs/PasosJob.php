<?php

namespace App\Jobs;

use App\Services\AprobadorService;

class PasosJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $idTipoLicencia;
    private $aprobadorService;
    public function __construct($idTipoLicencia, AprobadorService $aprobadorService)
    {
        $this->idTipoLicencia = $idTipoLicencia;
        $this->aprobadorService = $aprobadorService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
