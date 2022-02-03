<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->post('/api/buscarPasos','workflowController@buscarPasosLegajo');

$router->get('/api/relacionGrupoAprobador','workflowController@relacionGrupoAprobador');
// $router->post('/api/aprobadores','EtiquetaController@aprobadores');
// $router->post('/api/buscarLicencia','workflowController@buscarTiposLicencia');
// $router->post('/api/buscarLicenciaPorPasos','workflowController@buscarLicenciaPorPasos');


