<?php
session_start();
require_once("../../../database/database.php");

// Restreindre l'accès
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Générer CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Token CSRF invalide.';
        header('Location: add-user.php');
        exit();
    }

    // Récupération et nettoyage des données
    $login = trim((string)filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING));
    $prenom = trim((string)filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING));
    $nom = trim((string)filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING));
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

    // Validation basique
    if ($login === '' || $password === '' || $passwordConfirm === '') {
        $_SESSION['error'] = 'Veuillez renseigner le login et le mot de passe.';
        header('Location: add-user.php');
        exit();
    }
    if ($password !== $passwordConfirm) {
        $_SESSION['error'] = 'Les mots de passe ne correspondent pas.';
        header('Location: add-user.php');
        exit();
    }
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        header('Location: add-user.php');
        exit();
    }

    try {
        // Vérifier doublon de login
        $check = $connexion->prepare('SELECT id_admin FROM ADMINISTRATEUR WHERE login = :login');
        $check->bindParam(':login', $login, PDO::PARAM_STR);
        $check->execute();
        if ($check->rowCount() > 0) {
            $_SESSION['error'] = 'Ce login existe déjà.';
            header('Location: add-user.php');
            exit();
        }

        // Hachage du mot de passe avec Bcrypt
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $insert = $connexion->prepare('INSERT INTO ADMINISTRATEUR (nom_admin, prenom_admin, login, password) VALUES (:nom, :prenom, :login, :password)');
        $insert->bindParam(':nom', $nom, PDO::PARAM_STR);
        $insert->bindParam(':prenom', $prenom, PDO::PARAM_STR);
        $insert->bindParam(':login', $login, PDO::PARAM_STR);
        $insert->bindParam(':password', $hash, PDO::PARAM_STR);
        $insert->execute();

        $_SESSION['success'] = 'Utilisateur ajouté.';
        header('Location: manage-users.php');
        exit();
    } catch (PDOException $e) {
        error_log('Erreur PDO (add-user.php): ' . $e->getMessage());
        $_SESSION['error'] = 'Erreur lors de l\'ajout de l\'utilisateur.';
        header('Location: add-user.php');
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon.ico" type="image/x-icon">
    <title>Ajouter un Utilisateur - Jeux Olympiques - Los Angeles 2028</title>
</head>

<body>
    <header>
        <nav>
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-users/manage-users.php">Gestion Utilisateurs</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-genres/manage-genres.php">Gestion Genres</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Ajouter un Utilisateur</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['success']);
        }
        ?>
        <form action="add-user.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet utilisateur ?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <label for="login">Login :</label>
            <input type="text" name="login" id="login" required>

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom">

            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom">

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>

            <label for="password_confirm">Confirmer le mot de passe :</label>
            <input type="password" id="password_confirm" name="password_confirm" required>

            <input type="submit" value="Ajouter l'Utilisateur">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="../admin-users/manage-users.php">Retour à la gestion des utilisateurs</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

</body>

</html>
