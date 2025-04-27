<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'database.php';

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['id_admin'];

// Fetch admin name
$stmt = $conn->prepare("SELECT adminNom FROM admins WHERE id_admin = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($adminNom);
$stmt->fetch();
$stmt->close();

$success = "";
$error = "";

// Handle adding modules or students
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type'])) {
    if ($_POST['form_type'] == "add_module") {
        $nom_module = trim($_POST['nom_module']);

        if (!empty($nom_module)) {
            $check_stmt = $conn->prepare("SELECT id_module FROM modules WHERE nom_module = ?");
            $check_stmt->bind_param("s", $nom_module);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error = "Ce module existe déjà.";
            } else {
                $stmt = $conn->prepare("INSERT INTO modules (nom_module) VALUES (?)");
                $stmt->bind_param("s", $nom_module);

                if ($stmt->execute()) {
                    $success = "Module ajouté avec succès.";
                } else {
                    $error = "Erreur lors de l'ajout du module.";
                }
                $stmt->close();
            }
            $check_stmt->close();
        } else {
            $error = "Veuillez entrer un nom de module.";
        }
    }

    if ($_POST['form_type'] == "add_student") {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        // Check if 'module_id' is set and not empty
        if (isset($_POST['module_id']) && !empty($_POST['module_id'])) {
            $module_id = $_POST['module_id'];
        } else {
            $error = "Veuillez sélectionner un module.";
        }
        $password = trim($_POST['password']);

        if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($module_id) && !empty($password)) {
            $check_stmt = $conn->prepare("SELECT id_etudiant FROM etudiants WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows < 0) {
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO etudiants (etudiantNom, etudiantPrenom, email, id_module, mot_de_passe) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssis", $nom, $prenom, $email, $module_id, $hashed_password);

                if ($stmt->execute()) {
                    $success = "Étudiant ajouté avec succès.";
                } else {
                    $error = "Erreur lors de l'ajout de l'étudiant.";
                }
                $stmt->close();
            }
            $check_stmt->close();
        } else {
            $error = "Tous les champs sont requis.";
        }
    }
}

// Handle adding commentaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id']) && isset($_POST['commentaire']) && !isset($_POST['form_type'])) {
    $student_id = intval($_POST['student_id']);
    $commentaire = trim($_POST['commentaire']);

    if (!empty($commentaire)) {
        $update_stmt = $conn->prepare("UPDATE etudiants SET commentaire = ? WHERE id_etudiant = ?");
        $update_stmt->bind_param("si", $commentaire, $student_id);

        if ($update_stmt->execute()) {
            $success = "Commentaire ajouté avec succès.";
        } else {
            $error = "Erreur lors de l'ajout du commentaire.";
        }
        $update_stmt->close();
    } else {
        $error = "Le commentaire ne peut pas être vide.";
    }
}

// Handle student deletion
if (isset($_GET['delete_id'])) {
    $student_id_to_delete = $_GET['delete_id'];
    $delete_student_query = "DELETE FROM etudiants WHERE id_etudiant = ?";
    $stmt = $conn->prepare($delete_student_query);
    $stmt->bind_param("i", $student_id_to_delete);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle module deletion
if (isset($_GET['delete_module_id'])) {
    $module_id_to_delete = intval($_GET['delete_module_id']);

    // First, delete all students linked to this module (if you want to)
    $conn->query("DELETE FROM etudiants WHERE id_module = $module_id_to_delete");

    // Then delete the module
    $delete_module_query = "DELETE FROM modules WHERE id_module = ?";
    $stmt = $conn->prepare($delete_module_query);
    $stmt->bind_param("i", $module_id_to_delete);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Fetch modules
$query_modules = "SELECT id_module, nom_module FROM modules";
$modules_result = $conn->query($query_modules);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Modules & Étudiants</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<footer>
<button id="scroll-module" style="margin: 10px; padding: 8px 12px; cursor: pointer;">+</button>
</footer>

<main>
    <header>
        <h2 class="welcome">Bonjour, <?= htmlspecialchars($adminNom); ?> </h2>
        <?= date('d/m/Y') ?>
    </header>

    <section class="modules-etudiants">
        <h2>Les Modules & Les Étudiants</h2>

        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php while ($module = $modules_result->fetch_assoc()): ?>
            <div class="module-table">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0;">Module: <?= htmlspecialchars($module['nom_module']) ?></h3>
                    <a href="?delete_module_id=<?= $module['id_module'] ?>" onclick="return confirm('Supprimer ce module ? Cela supprimera aussi les étudiants liés.');">
                        <button type="button" style="background: none; border: none; font-size: 20px; cursor: pointer;">🗑️</button>
                    </a>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Commentaire</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $module_id = $module['id_module'];
                        $sql_students = "SELECT * FROM etudiants WHERE id_module = $module_id";
                        $students_result = $conn->query($sql_students);

                        while ($student = $students_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['etudiantNom']) ?></td>
                                <td><?= htmlspecialchars($student['etudiantPrenom']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="student_id" value="<?= $student['id_etudiant'] ?>">
                                        <input type="text" name="commentaire" value="<?= htmlspecialchars($student['commentaire'] ?? '') ?>" placeholder="Ajouter un commentaire">
                                        <button type="submit">💬</button>
                                    </form>
                                </td>
                                <td>
                                    <a href="?delete_id=<?= $student['id_etudiant'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?');">
                                        <button type="button">🗑️</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endwhile; ?>
    </section>

    <section class="ajouter-module">
        <h2>Ajouter Un Module</h2>
        <form method="post">
            <input type="hidden" name="form_type" value="add_module">
            <input type="text" name="nom_module" placeholder="Nom du module" required><br>
            <button type="submit">Ajouter</button>
        </form>
    </section>

    <section class="ajouter-etudiant">
        <h2>Ajouter Un Étudiant</h2>
        <form method="post">
            <input type="hidden" name="form_type" value="add_student">
            <input type="text" name="nom" placeholder="Nom de l'étudiant" required>
            <input type="text" name="prenom" placeholder="Prénom de l'étudiant" required>
            <input type="email" name="email" placeholder="L'email de l'étudiant" required>
            <select name="module_id" required>
                <option value="">Choisissez un module</option>
                <?php
                // Reload modules in the select dropdown
                $modules_result = $conn->query($query_modules);
                while ($module = $modules_result->fetch_assoc()) {
                    echo "<option value='{$module['id_module']}'>{$module['nom_module']}</option>";
                }
                ?>
            </select>
            <input type="password" name="password" placeholder="Mot de passe de l'étudiant" required>
            <button type="submit">Ajouter Étudiant</button>
        </form>
    </section>
</main>

<footer>
    <button class="logout" id="logout-btn" title="Se déconnecter">⎋</button>
</footer>

<script>
document.getElementById('logout-btn').addEventListener('click', function() {
    window.location.href = 'login.php';
});

document.getElementById('scroll-module').addEventListener('click', function() {
    document.querySelector('.ajouter-module').scrollIntoView({ behavior: 'smooth' });
});

// Function to hide success and error messages
function hideMessagesWithTimer() {
    const successMsg = document.querySelector('.success-message');
    const errorMsg = document.querySelector('.error-message');

    if (successMsg) {
        setTimeout(() => {
            successMsg.style.transition = 'opacity 0.5s ease';
            successMsg.style.opacity = '0';
            setTimeout(() => successMsg.style.display = 'none', 500);
        }, 4000);
    }

    if (errorMsg) {
        setTimeout(() => {
            errorMsg.style.transition = 'opacity 0.5s ease';
            errorMsg.style.opacity = '0';
            setTimeout(() => errorMsg.style.display = 'none', 500);
        }, 4000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded and parsed');
    hideMessagesWithTimer();
});
</script>

</body>
</html>

