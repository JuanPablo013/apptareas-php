<?php include_once __DIR__  . '/header-dashboard.php'; ?>

<div class="contenedor-sm">
    <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

    <a href="/perfil" class="enlace">Volver a Perfil</a>

    <form class="formulario" method="POST" action="/cambiar-contraseña">
        <div class="campo">
            <label for="nombre">Contraseña actual</label>
            <input
                type="password"
                name="contraseña_actual"
                placeholder="Tu contraseña actual"
            />
        </div>
        <div class="campo">
            <label for="nombre">Contraseña nueva</label>
            <input
                type="password"
                name="contraseña_nueva"
                placeholder="Tu contraseña nueva"
            />
        </div>

        <input type="submit" value="Guardar Cambios">
    </form>
</div>

<?php include_once __DIR__  . '/footer-dashboard.php'; ?>