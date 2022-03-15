<?php
use App\Jobs\TestJob;
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

$router->get('/ffcc', function () use ($router) {
    header('Access-Control-Allow-Origin: *');
    $data =  [
              [
                'name' => 'Name',
                'placeholder' => 'Please Write Your Name...',
                'class' => 'form-control',
                'column' => 'name',
                'type' => 'text',
                'value' => 'test',
                'required'=>true
              ],
              [
                'name' => 'Text Area',
                'placeholder' => 'Please Write Your Name...',
                'class' => 'date',
                'column' => 'datePicker',
                'type' => 'date',
                'value' => 'Text Area',
                'required'=>true
              ],
              [

                  'name' => 'Tags',
                  'class' => 'form-control',
                  'type' => 'tag',
                  'column' => 'tags',
                  'subColumn' => 'tag',
                  'autocomplete' => ['text' , 'and data' , 1,2, 'and three' , 'etcutra'],
                  'placeholder' => 'Write your tags',
                  'addOnlyFromAutocomplete' => false,

              ]
        ];


    return $data;
});
//$router->get('/job','workflowController@createJob');
$router->post('/api/buscarPasos','workflowController@buscarPasosLegajo');
$router->post('api/procesarLicencia', 'workflowController@procesarLicencia');
$router->post('api/aprobarPaso', 'workflowController@aprobarPaso');

$router->get('/job',function () use ($router){
    dispatch(new TestJob());
    return 'ok';
});

$router->get('/api/tipoLicencias','FfccController@getTipoLicencias');

$router->post('api/disponible', 'AprobadorController@buscarAprobador');
$router->get('/api/relacionGrupoAprobador','workflowController@relacionGrupoAprobador');


$router->get('/api/ffcc','FfccController@formControladoLicencia');
$router->group(['middleware' => 'cors'], function($app)
{
    $app->get('/api/ffcc','FfccController@formControladoLicencia');
});


// $router->post('/api/aprobadores','EtiquetaController@aprobadores');
// $router->post('/api/buscarLicencia','workflowController@buscarTiposLicencia');
// $router->post('/api/buscarLicenciaPorPasos','workflowController@buscarLicenciaPorPasos');


