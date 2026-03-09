<?php $this->layout('layout', ['title' => 'Admin Dashboard']) ?>

<h5>Bienvenido, <?= $this->e($user['email']) ?></h5>
<p>Este es tu panel administrativo.</p>

<!-- Aquí puedes agregar enlaces a la administración de provincias, ciudades, usuarios, etc. -->
<ul>
    <li><a href="/provincias">Administrar Provincias</a></li>
    <li><a href="/ciudades">Administrar Ciudades</a></li>
    <li><a href="/usuarios">Administrar Usuarios</a></li>
    <li><a href="/datospersonales/browse">Padrón</a></li>
    <li><a href="/matriculas/informealtas">Informe de Altas</a></li>
    <li><a href="/matriculas/informebajas">Informe de Bajas</a></li>
    

</ul>


<p><a href="/logout">Cerrar sesión</a></p>
