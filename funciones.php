<?php
// =====================
// FUNCIONES DE CÁLCULO
// =====================

// Calcula el promedio de un array de notas
function calcularPromedio($notas){
    if(!is_array($notas) || count($notas)===0) return 0;
    return array_sum($notas)/count($notas);
}

// Devuelve el estudiante con mejor promedio
function mejorEstudiante(array $estudiantes){
    $mejor=["id"=>null,"nombre"=>null,"promedio"=>-1];
    foreach($estudiantes as $id=>$e){
        $p=calcularPromedio($e["notas"]);
        if($p>$mejor["promedio"]){
            $mejor=[
                "id"=>$id,
                "nombre"=>$e["nombre"],
                "promedio"=>$p
            ];
        }
    }
    return $mejor;
}

// Devuelve la clase CSS para colorear una celda de promedio
function clasePromedio($promedio,$aprobado=6){
    return $promedio>=$aprobado ? 'promedio-aprobado' : 'promedio-desaprobado';
}

// Devuelve la clase CSS para colorear toda la fila (por ejemplo en reportes)
function claseFila($promedio,$umbral=11){
    return $promedio>=$umbral ? 'aprobado' : 'desaprobado';
}

// FUNCIONES ABM

function altaEstudiante(&$estudiantes,$nombre,$edad){
    $estudiantes[]=[
        "nombre"=>$nombre,
        "edad"=>$edad,
        "notas"=>[]
    ];
}

function bajaEstudiante(&$estudiantes,$id){
    if(isset($estudiantes[$id])){
        unset($estudiantes[$id]);
    }
}

function modificarEstudiante(&$estudiantes,$id,$nuevoNombre,$nuevaEdad){
    if(isset($estudiantes[$id])){
        if(!empty($nuevoNombre)) $estudiantes[$id]["nombre"]=$nuevoNombre;
        if(!empty($nuevaEdad)) $estudiantes[$id]["edad"]=$nuevaEdad;
    }
}
