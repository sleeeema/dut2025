<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'database.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Try admin first
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
        if (password_verify($password, $admin['mot_de_passe'])) {
            $_SESSION['user'] = $admin['email'];
            $_SESSION['role'] = 'admin';
            $_SESSION['id_admin'] = $admin['id_admin'];
            header("Location: index_admin.php");
            exit;
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        // Check student table
        $stmt = $conn->prepare("SELECT * FROM etudiants WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($etudiant = $result->fetch_assoc()) {
            if (password_verify($password, $etudiant['mot_de_passe'])) {
                $_SESSION['user'] = $etudiant['email'];
                $_SESSION['role'] = 'etudiant';
                $_SESSION['id_etudiant'] = $etudiant['id_etudiant'];
                header("Location: index_etudiant.php");
                exit;
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Email introuvable.";
        }
    }
}
?>

<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Login Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Almendra&display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Almendra', serif;
        }
    </style>
</head>
<body class="min-h-screen flex">
    <div class="w-1/2 hidden md:block">
 
    <img alt="External Image" src="https://ic.pics.livejournal.com/dubikvit/65747770/1572473/1572473_600.jpg" class="w-full h-full object-cover" height="800" src="External Image" src="https://ic.pics.livejournal.com/dubikvit/65747770/1572473/1572473_600.jpg" width="600"/>
    </div>
    <div class="w-full md:w-1/2 bg-[#e7dfd8] flex flex-col justify-center items-center px-8 md:px-20">
    <h1 class="text-6xl mb-20 font-bold leading-tight text-center">Bienvenu</h1>

        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-3 rounded-lg mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form class="w-full max-w-lg space-y-8" method="POST" action="">
            <div class="bg-white rounded-full flex items-center px-6 py-4">
                <i class="fas fa-user text-black text-lg mr-4"></i>
                <input class="w-full bg-transparent outline-none text-black text-sm font-normal" placeholder="Votre e-mail" type="email" name="email" required/>
            </div>

            <div class="bg-white rounded-full flex items-center px-6 py-4">
                <i class="fas fa-lock text-black text-lg mr-4"></i>
                <input class="w-full bg-transparent outline-none text-black text-sm font-normal" placeholder="Votre mot de passe" type="password" name="password" required/>
            </div>

            <button class="w-full bg-black text-white text-2xl font-bold rounded-full py-5 mt-10" type="submit">
                Connexion
            </button>
        </form>
    </div>
</body>
</html>
