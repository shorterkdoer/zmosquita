<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Pantalla Inicial - <?= $this->e($appName) ?></title>
    <style>
        body {
            background: #f8f8f8;
            margin: 0;
            padding: 20px; /* evita que quede pegado en landscape */
            font-family: Arial, sans-serif;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh; /* mejor que height */
        }

        .logo {
            margin-bottom: 40px;
        }

        .logo img {
            max-width: 90vw;      /* nunca más ancho que la pantalla */
            max-height: 40vh;     /* nunca más alto que el 40% del alto visible */
            height: auto;
            width: auto;
        }

        .app-name {
            font-size: 24px;
            margin-bottom: 60px;
        }
        .buttons {
            display: flex;
            gap: 20px;
        }
        .btn {
            padding: 15px 25px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            background: #ac3587;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn:hover {
            background: #ac35ff;
        }
        .btn-ingresar {
    background-color: #28a745; /* verde */
  }

  .btn-ingresar:hover {
    background-color: #218838;
  }

  .btn-registrarme {
    background-color: #ffc107; /* amarillo */
    color: #000; /* mejor contraste sobre amarillo */
  }

  .btn-registrarme:hover {
    background-color: #e0a800;
  }

  .btn-passrecovery {
    background-color: #ac3587; /* amarillo */
    color: #000; /* mejor contraste sobre amarillo */
  }

  .btn-passrecovery:hover {
    background-color: #ac35ff;
  }
@media (orientation: landscape) {
    .logo img {
        max-height: 30vh;
    }
}
  
    </style>
</head>
<body>
    <div class="logo">
        <img src="/public/logovert.png" alt="Logo">
    </div>
    <div class="app-name">
        <h4><?= $this->e($appName) ?></h4>
    </div>
    <div class="buttons">
        <a class="btn" href="/login">Ingresar</a>
        <a class="btn" href="/register">Registrarme</a>
    </div>
    <p></p>

    <div class="buttons">
        <a class="btn " href="http://www.coprobilp.org.ar/tutos/CoProBiLP-recuperar-clave.pdf" target="_blank"
        >Cómo recuperar la contraseña</a>
        
    </div>
    <p></p>

    <div class="buttons">
        <a class="btn" href="/requisitos" target="_blank">Requisitos para matricularse</a>
        <a class="btn" href="/institucional" target="_blank">Información institucional</a>
    </div>
    <p></p>



</body>
</html>
