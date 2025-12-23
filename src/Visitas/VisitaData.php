<?php
// src/Visitas/VisitaData.php
// Encapsula la obtención de datos para el formulario de visitas

require_once __DIR__ . '/../conexion.php';

class VisitaData {
    public static function obtenerClientes($conn) {
        $sql = "SELECT cod_cli, nom_cli FROM clientes";
        $resultado = $conn->query($sql);
        $clientes = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $clientes[$fila['cod_cli']] = $fila['nom_cli'];
            }
        }
        return $clientes;
    }

    public static function obtenerEmpleados($conn) {
        $sql = "SELECT cod_emp, nom_emp FROM empleados";
        $resultado = $conn->query($sql);
        $empleados = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $empleados[$fila['cod_emp']] = $fila['nom_emp'];
            }
        }
        return $empleados;
    }

    public static function obtenerInmuebles($conn) {
        $sql = "SELECT cod_inm, dir_inm FROM inmuebles";
        $resultado = $conn->query($sql);
        $inmuebles = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $inmuebles[$fila['cod_inm']] = $fila['dir_inm'];
            }
        }
        return $inmuebles;
    }
}
