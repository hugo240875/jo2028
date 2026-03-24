<?php
session_start();
require_once("../../../database/database.php");
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}
if (empty($_SESSION['csrf_token']))
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
try {
    $athletes = $connexion->query('SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE ORDER BY nom_athlete')->fetchAll(PDO::FETCH_ASSOC);
    $epreuves = $connexion->query('SELECT id_epreuve, nom_epreuve FROM EPREUVE ORDER BY date_epreuve')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $athletes = [];
    $epreuves = [];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Token CSRF invalide';
        header('Location:add-results.php');
        exit();
    }
    $id_ath = filter_input(INPUT_POST, 'id_athlete', FILTER_VALIDATE_INT);
    $id_epr = filter_input(INPUT_POST, 'id_epreuve', FILTER_VALIDATE_INT);
    $resultat = trim((string) filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING));
    if (!$id_ath || !$id_epr) {
        $_SESSION['error'] = 'Sélection invalide';
        header('Location:add-results.php');
        exit();
    }
    try {
        $chk = $connexion->prepare('SELECT * FROM PARTICIPER WHERE id_athlete=:a AND id_epreuve=:e');
        $chk->bindParam(':a', $id_ath);
        $chk->bindParam(':e', $id_epr);
        $chk->execute();
        if ($chk->rowCount() > 0) {
            $_SESSION['error'] = 'Résultat déjà enregistré pour ce participant/épreuve';
            header('Location:add-results.php');
            exit();
        }
        $ins = $connexion->prepare('INSERT INTO PARTICIPER (id_athlete, id_epreuve, resultat) VALUES (:a,:e,:r)');
        $ins->bindParam(':a', $id_ath);
        $ins->bindParam(':e', $id_epr);
        $ins->bindParam(':r', $resultat);
        $ins->execute();
        $_SESSION['success'] = 'Résultat ajouté';
        header('Location:manage-results.php');
        exit();
    } catch (PDOException $e) {
        error_log('add-result: ' . $e->getMessage());
        $_SESSION['error'] = 'Erreur';
        header('Location:add-results.php');
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
    <title>Ajouter un Résultat - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Ajouter un Résultat</h1>
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
        <form action="add-results.php" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce résultat ?')">
            <input type="hidden" name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <label for="id_athlete">Athlète :</label>
            <select id="id_athlete" name="id_athlete" required>
                <option value="">--</option>
                <?php foreach ($athletes as $a): ?>
                    <option value="<?php echo (int) $a['id_athlete']; ?>">
                        <?php echo htmlspecialchars($a['prenom_athlete'] . ' ' . $a['nom_athlete'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_epreuve">Épreuve :</label>
            <select id="id_epreuve" name="id_epreuve" required>
                <option value="">--</option>
                <?php foreach ($epreuves as $e): ?>
                    <option value="<?php echo (int) $e['id_epreuve']; ?>">
                        <?php echo htmlspecialchars($e['nom_epreuve'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="resultat">Résultat :</label>
            <input type="text" id="resultat" name="resultat">

            <input type="submit" value="Ajouter le Résultat">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="../admin-results/manage-results.php">Retour à la gestion des résultats</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

</body>

</html>