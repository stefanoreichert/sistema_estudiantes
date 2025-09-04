<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reportes - Sistema Estudiantes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background-color: #f8f9fa; }
    .navbar { border-bottom: 1px solid #dee2e6; }
    .card { border-radius: 12px; transition: transform 0.2s; }
    .card:hover { transform: translateY(-5px); }
    .card-title { font-weight: 600; }
    .card-subtitle { font-weight: 500; }
    .notas { font-size: 0.95rem; color: #495057; }
    .promedio { font-size: 1.1rem; font-weight: 600; color: #0d6efd; }
</style>
</head>
<body>

<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema Estudiantes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" >
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="estudiantes.php">Estudiantes</a></li>
                
                <li class="nav-item"><a class="nav-link active" href="notas.php">Notas</a></li>
                <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>
            </ul>
        </div>
    </nav>
</div>

<div class="container py-5">
    <h1 class="mb-5 text-center fw-bold text-primary">Reportes por Carrera</h1>
    <div class="row g-4">
    <?php
    $rs_carreras = $mysqli->query("SELECT id,nombre FROM carrera");
    while($carrera=$rs_carreras->fetch_assoc()){
        $id_carrera=$carrera['id'];
        $nombre_carrera=$carrera['nombre'];
        $rs_mejor=$mysqli->query("
            SELECT a.id AS id_alumno,a.nombre,AVG(n.nota) AS promedio
            FROM alumno a
            JOIN notas n ON a.id=n.id_alumno
            WHERE a.id_carrera=$id_carrera
            GROUP BY a.id
            ORDER BY promedio DESC
            LIMIT 1
        ");
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100 p-3">
                <div class="card-body d-flex flex-column">
                    <h3 class="card-title text-center"><?=htmlspecialchars($nombre_carrera)?></h3>
                    <?php
                    if($rs_mejor && $rs_mejor->num_rows>0){
                        $mejor=$rs_mejor->fetch_assoc();
                        $id_mejor=$mejor['id_alumno'];
                        $nombre_alumno=$mejor['nombre'];
                        $rs_notas=$mysqli->query("SELECT nota FROM notas WHERE id_alumno=$id_mejor");
                        $notas=[];
                        while($n=$rs_notas->fetch_assoc()) $notas[]=$n['nota'];
                        $promedio=count($notas)>0 ? array_sum($notas)/count($notas) : 0;
                        ?>
                        <h5 class="card-subtitle mb-2 text-center">Mejor Alumno: <?=htmlspecialchars($nombre_alumno)?></h5>
                        <p class="notas text-center"><strong>Notas:</strong> <?=implode(', ',$notas)?></p>
                        <p class="promedio text-center">Promedio: <?=number_format($promedio,2)?></p>
                        <?php
                    } else {
                        ?>
                        <p class="text-center text-muted mt-3">No hay alumnos con notas para esta carrera.</p>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    </div>
</div>

<footer class="bg-white text-center py-4 mt-5 border-top shadow-sm">
    Sistema de Estudiantes © <?=date('Y')?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
