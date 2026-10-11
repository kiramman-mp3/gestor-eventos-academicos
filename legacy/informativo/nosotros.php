<?php
require_once '../session.php';
$nombre = getUserName();
$apellido = getUserLastname();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créditos</title>
  <link rel="stylesheet" href="../css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <header class="ctt-header">
    <div class="top-bar">
      <div class="logo">
        <img src="../uploads/logo.png" alt="Logo CTT">
      </div>
      <div class="top-links">
        <div class="link-box">
          <i class="fa-solid fa-arrow-left"></i>
          <div>
            <span class="title">Regresar</span><br>
            <a href="javascript:history.back()">Página anterior</a>
          </div>
        </div>
      </div>
    </div>
  </header>

<main class="container">
    <h1 class="display-5">Créditos</h1>
    <div class="credits-grid">
      <div class="card">
        <img src="../resource/pablo.png" class="card-img-top" alt="Pablo Vayas">
        <div class="card-body">
          <h5 class="card-title">Pablo Vayas</h5>
          <p class="card-text">Estudiante de Software, especializado en frontend y bases de datos</p>
          <div class="social-links">
            <a href="https://www.facebook.com/pablo.vayas.33" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/pablo.vayas/" title="Instagram"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
      </div>

      <div class="card">
        <img src="../resource/alexis.jpg" class="card-img-top" alt="Alexis López">
        <div class="card-body">
          <h5 class="card-title">Alexis López</h5>
          <p class="card-text">Apasionado por la programación web y el diseño visual interactivo</p>
          <div class="social-links">
            <a href="https://www.facebook.com/alexis.lopez.737521" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/alexislp.z/" title="Instagram"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
      </div>

      <div class="card">
        <img src="../resource/jose.jpg" class="card-img-top" alt="José Manzano">
        <div class="card-body">
          <h5 class="card-title">José Manzano</h5>
          <p class="card-text">Estudiante de Software con interés en computación gráfica y desarrollo de videojuegos</p>
          <div class="social-links">
            <a href="https://www.facebook.com/profile.php?id=100036996780282" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/manzano8555/" title="Instagram"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
      </div>

      <div class="card">
        <img src="../resource/johan.jpg" class="card-img-top" alt="Johan Rodriguez">
        <div class="card-body">
          <h5 class="card-title">Johan Rodriguez</h5>
          <p class="card-text">Estudiante de Software con experiencia en simulaciones gráficas y sistemas interactivos</p>
          <div class="social-links">
            <a href="https://www.facebook.com/johan.kiramman/" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/jhnrx907/" title="Instagram"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
      </div>

      <div class="card">
        <img src="../resource/alan.jpg" class="card-img-top" alt="Alan Puruncajas">
        <div class="card-body">
          <h5 class="card-title">Alan Puruncajas</h5>
          <p class="card-text">Estudiante apasionado por el diseño gráfico y la realidad aumentada</p>
          <div class="social-links">
            <a href="https://www.facebook.com/alan.puruncajas" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/alam_cuenquita/" title="Instagram"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>