<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'conexion.php';

// Manejar Acciones (Cargarlas primero para que el listado se actualice)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $nombre = $_POST['nombre'];
            $precio = $_POST['precio'];
            $imagen = $_POST['imagen'];
            $cat = $_POST['categoria'];
            $stmt = $conexion->prepare("INSERT INTO servicios (nombre, precio, imagen, categoria) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $nombre, $precio, $imagen, $cat);
            $stmt->execute();
        } elseif ($_POST['action'] === 'update') {
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $precio = $_POST['precio'];
            $imagen = $_POST['imagen'];
            $cat = $_POST['categoria'];
            $stmt = $conexion->prepare("UPDATE servicios SET nombre=?, precio=?, imagen=?, categoria=? WHERE id=?");
            $stmt->bind_param("sdssi", $nombre, $precio, $imagen, $cat, $id);
            $stmt->execute();
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            $stmt = $conexion->prepare("DELETE FROM servicios WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        } elseif ($_POST['action'] === 'toggle') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $stmt = $conexion->prepare("UPDATE servicios SET activo = ? WHERE id = ?");
            $stmt->bind_param("ii", $status, $id);
            $stmt->execute();
        }
    }
}

$servicios = $conexion->query("SELECT * FROM servicios ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | BEAUTY MAKEUP</title>
    <link rel="stylesheet" href="style.css?v=1.2">
    <style>
        .admin-main { padding: 8rem 5% 4rem; max-width: 1200px; margin: 0 auto; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; }
        .service-list { display: grid; gap: 1rem; }
        .service-item { background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(223, 207, 190, 0.1); }
        .add-form { background: rgba(223, 207, 190, 0.05); padding: 2rem; border-radius: 12px; margin-bottom: 3rem; border: 1px solid var(--primary-color); }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
        .admin-btn { padding: 8px 15px; border: 1px solid var(--primary-color); background: transparent; color: var(--primary-color); cursor: pointer; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; }
        .admin-btn.danger { border-color: #ff5555; color: #ff5555; }
        .admin-btn:hover { background: var(--primary-color); color: #000; }
        .admin-btn.danger:hover { background: #ff5555; color: #fff; }
    </style>
</head>
<body>
    <header id="navbar">
        <div class="logo-container">
            <a href="index.php" class="logo-link">
                <img src="logo.png" alt="Logo" class="logo-img-circular">
                <span class="logo-text">ADMIN PANEL</span>
            </a>
        </div>
        <nav>
            <ul class="nav-links">
                <li><a href="logout.php" style="color: #ff8888;">Cerrar Sesión (Admin)</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-main">
        <div class="admin-header">
            <h2 class="section-title">Gestión de Servicios</h2>
            <p>Hola, <strong><?php echo $_SESSION['admin_user']; ?></strong></p>
        </div>

        <section class="add-form">
            <h3 id="form-title" style="margin-bottom: 1.5rem; color: var(--primary-color);">Agregar Nuevo Servicio</h3>
            <form action="admin_dashboard.php" method="POST" id="mainForm">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="id" id="form-id" value="">
                <div class="form-row">
                    <input type="text" name="nombre" id="form-nombre" placeholder="Nombre del Servicio" required>
                    <input type="number" step="0.01" name="precio" id="form-precio" placeholder="Precio ($)" required>
                    <input type="text" name="imagen" id="form-imagen" placeholder="Nombre de imagen (ej: bridal.jpg)" required>
                    <select name="categoria" id="form-categoria" required>
                        <option value="maquillaje">Maquillaje</option>
                        <option value="especial">Especial</option>
                        <option value="ojos">Ojos</option>
                        <option value="cejas">Cejas</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="admin-btn" id="submitBtn">Guardar Servicio</button>
                    <button type="button" class="admin-btn" id="cancelBtn" style="display:none;" onclick="resetForm()">Cancelar</button>
                </div>
            </form>
        </section>

        <section class="service-list">
            <h3 style="margin-bottom: 1.5rem;">Listado de Servicios</h3>
            <?php while($s = $servicios->fetch_assoc()): ?>
                <div class="service-item">
                    <div>
                        <strong style="color: var(--primary-color);"><?php echo $s['nombre']; ?></strong> - $<?php echo $s['precio']; ?>
                        <br><span style="font-size: 0.8rem; opacity: 0.6;">Categoría: <?php echo $s['categoria']; ?> | Imagen: <?php echo $s['imagen']; ?></span>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="admin-btn" onclick="editService(<?php echo htmlspecialchars(json_encode($s)); ?>)">Editar</button>
                        <form action="admin_dashboard.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                            <input type="hidden" name="status" value="<?php echo $s['activo'] ? 0 : 1; ?>">
                            <button type="submit" class="admin-btn"><?php echo $s['activo'] ? 'Desactivar' : 'Activar'; ?></button>
                        </form>
                        <form action="admin_dashboard.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este servicio?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                            <button type="submit" class="admin-btn danger">Eliminar</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </section>
    </main>

    <script>
        const formTitle = document.getElementById('form-title');
        const formAction = document.getElementById('form-action');
        const formId = document.getElementById('form-id');
        const submitBtn = document.getElementById('submitBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        const fNombre = document.getElementById('form-nombre');
        const fPrecio = document.getElementById('form-precio');
        const fImagen = document.getElementById('form-imagen');
        const fCategoria = document.getElementById('form-categoria');

        function editService(s) {
            formTitle.textContent = 'Editar Servicio: ' + s.nombre;
            formAction.value = 'update';
            formId.value = s.id;
            fNombre.value = s.nombre;
            fPrecio.value = s.precio;
            fImagen.value = s.imagen;
            fCategoria.value = s.categoria;
            submitBtn.textContent = 'Actualizar Servicio';
            cancelBtn.style.display = 'inline-block';
            formTitle.scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            formTitle.textContent = 'Agregar Nuevo Servicio';
            formAction.value = 'add';
            formId.value = '';
            document.getElementById('mainForm').reset();
            submitBtn.textContent = 'Guardar Servicio';
            cancelBtn.style.display = 'none';
        }
    </script>
</body>
</html>
