<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait SaveJsonDbTrait {
    public function saveFileJson($file,$data)
    {
        try {

            $file_path = storage_path('json/'.$file.'.json');
//            $json = json_decode(file_put_contents($file_path,$data.PHP_EOL , FILE_APPEND | LOCK_EX));
            $json = file_put_contents($file_path,$data);
            return true;
        }catch (\Throwable $e){
            return $e;
        }

    }
}
