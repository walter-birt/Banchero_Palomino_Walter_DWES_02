<?php
include "usuarios_coches.php";

// Función para calcular la letra del DNI (módulo 23)
function letra_nif($dni) {
    return substr("TRWAGMYFPDXBNJZSQVHLCKE", strtr($dni, "XYZ", "012") % 23, 1);
}

// Asignar datos del formulario a variables
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$dni = $_POST['dni'];
$modelo = $_POST['modelo'];
$fecha_inicio = $_POST['fecha_inicio'];
$duracion = (int)$_POST['duracion'];

$nombreOk = false;
$apellidoOk = false;
$dniOk = false;
$fechaOk = false;
$duracionOk = false;
$vehiculoOk = false;

// Validar campos vacíos
if (empty($nombre)) {
    $nombreOk = false;
} else {
    // Comprobar la existencia del usuario y DNI utilizando letra_nif
    foreach (USUARIOS as $usuario) {
        if (strtoupper($usuario['nombre']) === strtoupper($nombre)) {
            $nombreOk = true;
        }
        if (strtoupper($usuario['apellido']) === strtoupper($apellido)) {
            $apellidoOk = true;
        }
        if ($nombreOk && $apellidoOk) {
            // Verificar formato dni
            if (preg_match('/^[0-9]{8}[A-Z]$/', $dni)) {
                // Se comprueba que la letra del DNI sea correcta
                if (substr($dni, -1) === letra_nif(substr($dni, 0, -1))) {
                    // Se comprueba que el DNI coincide con el de ese usuario
                    if ($usuario['dni'] === $dni) {
                        $dniOk = true; // El DNI es válido y coincide con el usuario
                    } else {
                        $dniOk = false; // El DNI no coincide con el del usuario
                    }
                } else {
                    $dniOk = false; // La letra del DNI es incorrecta
                }
            } else {
                $dniOk = false; // Formato del DNI incorrecto
            }
            break; // Usuario encontrado, sale del bucle
        }
    }
}

if (empty($apellido)) {
    $apellidoOk = false;
}

if (empty($dni)) {
    $dniOk = false;
}

if (empty($fecha_inicio)) {
    $fechaOk = false;
} else {
    // Comprobar fecha de inicio
    if (strtotime($fecha_inicio) > strtotime(date('Y-m-d'))) {
        $fechaOk = true;
    }
}

if (empty($duracion)) {
    $duracionOk = false;
} else {
    // Comprobar duración
    if ($duracion >= 1 && $duracion <= 30) {
        $duracionOk = true;
    }
}

if (empty($modelo)) {
    $vehiculoOk = false;
} else {
    // Comprobar disponibilidad del vehículo
    foreach ($coches as &$coche) {
        if ($coche['modelo'] === $modelo && 
            (is_null($coche['fecha_inicio']) || strtotime($fecha_inicio) > strtotime($coche['fecha_fin']))) {
            $vehiculoOk = true;
            $coche['fecha_inicio'] = $fecha_inicio;
            $coche['fecha_fin'] = date('Y-m-d', strtotime("$fecha_inicio +$duracion days"));
            break;
        }
    }
}

// Mostrar la confirmación de reserva o los errores
if ($nombreOk && $apellidoOk && $dniOk && $fechaOk && $duracionOk && $vehiculoOk) {
    echo "<h2>Reserva confirmada:</h2>";
    echo "<p>Nombre: ".ucfirst($nombre)."</p>";
    echo "<p>Apellido: ".ucfirst($apellido)."</p>";
    echo "<img src='../img/$modelo.jpg' alt='$modelo' style='width:200px;height:auto;'><br><br>";
} else {
    echo "<h2>Errores en la reserva:</h2>";

    if (empty($nombre)) {
        echo "<p style='color: red;'>Nombre campo vacío.</p>";
    } elseif ($nombreOk) {
        echo "<p style='color: green;'>Nombre: $nombre</p>";
    } else {
        echo "<p style='color: red;'>Nombre incorrecto.</p>";
    }

    if (empty($apellido)) {
        echo "<p style='color: red;'>Apellido campo vacío.</p>";
    } elseif ($apellidoOk) {
        echo "<p style='color: green;'>Apellido: $apellido</p>";
    } else {
        echo "<p style='color: red;'>Apellido incorrecto.</p>";
    }

    if (empty($dni)) {
        echo "<p style='color: red;'>DNI campo vacío.</p>";
    } elseif ($dniOk) {
        echo "<p style='color: green;'>DNI: $dni</p>";
    } else {
        echo "<p style='color: red;'>DNI incorrecto.</p>";
    }

    if (empty($fecha_inicio)) {
        echo "<p style='color: red;'>Fecha de inicio campo vacío.</p>";
    } elseif ($fechaOk) {
        echo "<p style='color: green;'>Fecha de Inicio: $fecha_inicio</p>";
    } else {
        echo "<p style='color: red;'>La fecha de inicio debe ser posterior a la fecha actual.</p>";
    }

    if (empty($duracion)) {
        echo "<p style='color: red;'>Duración campo vacío.</p>";
    } elseif ($duracionOk) {
        echo "<p style='color: green;'>Duración: $duracion días</p>";
    } else {
        echo "<p style='color: red;'>La duración debe ser entre 1 y 30 días.</p>";
    }

    if (empty($modelo)) {
        echo "<p style='color: red;'>Modelo campo vacío.</p>";
    } elseif ($vehiculoOk) {
        echo "<p style='color: green;'>Vehículo: $modelo</p>";
    } else {
        echo "<p style='color: red;'>El vehículo no está disponible en las fechas seleccionadas.</p>";
    }
}
?>






