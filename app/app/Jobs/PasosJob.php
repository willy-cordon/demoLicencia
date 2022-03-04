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
    public function __construct($idTipoLicencia)
    {
        $this->idTipoLicencia = $idTipoLicencia;
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
