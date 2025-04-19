<?php
function listarArchivos($directorio, $nivel = 0) {
    $archivos = scandir($directorio);
    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..') {
            continue;
        }
        $rutaCompleta = $directorio . DIRECTORY_SEPARATOR . $archivo;
        echo str_repeat('  ', $nivel) . $archivo . PHP_EOL;
        if (is_dir($rutaCompleta)) {
            listarArchivos($rutaCompleta, $nivel + 1);
        }
    }
}

// Cambia esta ruta al directorio raíz de tu proyecto
$directorioRaiz = 'c:/xampp/htdocs/ds6';
listarArchivos($directorioRaiz);