<?php

namespace App\Traits;


trait ProcessJsonDbTrait {
   public function processJson($nameJson)
   {
       $file = storage_path('json/'.$nameJson.'.json');
       $archivo = file_get_contents($file);
       return json_decode($archivo,true);
   }
}
