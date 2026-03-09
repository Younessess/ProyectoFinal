<?php
// la función siguiente es propia a php para evitar usar muchos rquire_once o include
spl_autoload_register(function ($class) {
    // 1. Definimos el prefijo del proyecto (Namespace) para que php no intenta cargar clases externas solo va a cargar las clases que tienen como namespace App
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