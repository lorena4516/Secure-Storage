<?php
spl_autoload_register(function ($class_name) {
    // Lista de directorios donde buscar las clases
    $directories = [
        __DIR__ . '/config/',
        __DIR__ . '/models/',
        __DIR__ . '/controllers/',
        __DIR__ . '/utils/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Si no se encuentra la clase, mostrar error detallado
    throw new Exception("No se pudo cargar la clase: $class_name");
});
?>