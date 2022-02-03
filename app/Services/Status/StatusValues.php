<?php

namespace App\Services\Status;
use App\Services\Status\pendiente;
use App\Services\Status\aprobado;
use App\Services\Status\rechazado;
use App\Services\Status\notificado;
use App\Services\Status\finalizado;
/**
 * Class NotificacionStatusValues
 * @package App\Services
 */
final class StatusValues
{
    const Status = [
        'pendiente'  => pendiente::class,
        'aprobado'   => aprobado::class,
        'rechazado'  => rechazado::class,
        'notificado' =>notificado::class,
        'finalizado' =>finalizado::class
    ];
}
