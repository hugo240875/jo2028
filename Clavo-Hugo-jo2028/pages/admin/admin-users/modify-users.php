<?php
session_start();
require_once("../../../database/database.php");

// Restreindre l'accès
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Générer CSRF si nécessaire
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupérer l'id
if (!isset($_GET['id_admin'])) {
    $_SESSION['error'] = 'ID utilisateur manquant.';
    header('Location: manage-users.php');
    exit();
}

$id_admin = filter_input(INPUT_GET, 'id_admin', FILTER_VALIDATE_INT);
if ($id_admin === false || $id_admin === null) {
    $_SESSION['error'] = 'ID utilisateur invalide.';
    header('Location: manage-users.php');
    exit();
}

// Charger l'utilisateur
try {
    $stmt = $connexion->prepare('SELECT id_admin, login, nom_admin, prenom_admin FROM ADMINISTRATEUR WHERE id_admin = :id');
    $stmt->bindParam(':id', $id_admin, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = 'Utilisateur introuvable.';
        header('Location: manage-users.php');
        exit();
    }
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erreur PDO (modify-user.php load): ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors du chargement de l\'utilisateur.';
    header('Location: manage-users.php');
    exit();
}

// Traitement POST (mise à jour)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Token CSRF invalide.';
        header("Location: modify-user.php?id_admin={$id_admin}");
        exit();
    }

    $login = trim((string)filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING));
    $prenom = trim((string)filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING));
    $nom = trim((string)filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING));
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

    if ($login === '') {
        $_SESSION['error'] = 'Le login ne peut pas être vide.';
        header("Location: modify-user.php?id_admin={$id_admin}");
        exit();
    }

    if ($password !== '' && $password !== $passwordConfirm) {
        $_SESSION['error'] = 'Les mots de passe ne correspondent pas.';
        header("Location: modify-user.php?id_admin={$id_admin}");
        exit();
    }

    try {
        // Vérifier doublon login (exclure l'utilisateur courant)
        $check = $connexion->prepare('SELECT id_admin FROM ADMINISTRATEUR WHERE login = :login AND id_admin <> :id');
        $check->bindParam(':login', $login, PDO::PARAM_STR);
        $check->bindParam(':id', $id_admin, PDO::PARAM_INT);
        $check->execute();
        if ($check->rowCount() > 0) {
            $_SESSION['error'] = 'Ce login est déjà utilisé par un autre utilisateur.';
            header("Location: modify-user.php?id_admin={$id_admin}");
            exit();
        }

        // Construire la requête de mise à jour dynamiquement
        $fields = ['nom_admin' => $nom, 'prenom_admin' => $prenom, 'login' => $login];
        $sqlParts = [];
        foreach ($fields as $col => $val) {
            $sqlParts[] = "$col = :$col";
        }

        $params = $fields;

        // Si mot de passe fourni, le hacher et l'ajouter
        if ($password !== '') {
            if (strlen($password) < 6) {
                $_SESSION['error'] = 'Le mot de passe doit contenir au moins 6 caractères.';
                header("Location: modify-user.php?id_admin={$id_admin}");
                exit();
            }
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sqlParts[] = 'password = :password';
            $params['password'] = $hash;
        }

        $sql = 'UPDATE ADMINISTRATEUR SET ' . implode(', ', $sqlParts) . ' WHERE id_admin = :id';
        $params['id'] = $id_admin;

        $upd = $connexion->prepare($sql);
        foreach ($params as $k => $v) {
            $upd->bindValue(':' . $k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $upd->execute();

        $_SESSION['success'] = 'Utilisateur mis à jour.';
        header('Location: manage-users.php');
        exit();
    } catch (PDOException $e) {
        error_log('Erreur PDO (modify-user.php save): ' . $e->getMessage());
        $_SESSION['error'] = 'Erreur lors de la mise à jour.';
        header("Location: modify-user.php?id_admin={$id_admin}");
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
    <title>Modifier un Utilisateur - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Modifier un Utilisateur</h1>
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
        <form action="modify-user.php?id_admin=<?php echo (int)$id_admin; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet utilisateur ?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <label for="login">Login :</label>
            <input type="text" name="login" id="login" value="<?php echo htmlspecialchars($user['login'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom_admin'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom_admin'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer) :</label>
            <input type="password" id="password" name="password">

            <label for="password_confirm">Confirmer le mot de passe :</label>
            <input type="password" id="password_confirm" name="password_confirm">

            <input type="submit" value="Modifier l'Utilisateur">
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
