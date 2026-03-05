<?php

spl_autoload_register(function ($class) {
    // 1. Definimos el prefijo del proyecto (Namespace)
    $prefix = 'App\\';
    
    // 2. ¿La clase usa nuestro prefijo?
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    // 3. Quitamos "App\" y cambiamos las "\" por "/" o "\" según el sistema
    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/../' . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    // 4. Si el archivo existe, lo cargamos
    if (file_exists($file)) {
        require_once $file;
    }
});