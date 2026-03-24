<?php
session_start();
require_once("../../../database/database.php");
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}
if (empty($_SESSION['csrf_token']))
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$id_ath = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);
$id_epr = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);
if (!$id_ath || !$id_epr) {
    $_SESSION['error'] = 'Paramètres manquants';
    header('Location:manage-results.php');
    exit();
}
try {
    $stmt = $connexion->prepare('SELECT * FROM PARTICIPER WHERE id_athlete=:a AND id_epreuve=:e');
    $stmt->bindParam(':a', $id_ath);
    $stmt->bindParam(':e', $id_epr);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $_SESSION['error'] = 'Résultat introuvable';
        header('Location:manage-results.php');
        exit();
    }
    $athletes = $connexion->query('SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE ORDER BY nom_athlete')->fetchAll(PDO::FETCH_ASSOC);
    $epreuves = $connexion->query('SELECT id_epreuve, nom_epreuve FROM EPREUVE ORDER BY date_epreuve')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('modify-result load: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur';
    header('Location:manage-results.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Token CSRF invalide';
        header('Location:modify-results.php?id_athlete=' . $id_ath . '&id_epreuve=' . $id_epr);
        exit();
    }
    $new_a = filter_input(INPUT_POST, 'id_athlete', FILTER_VALIDATE_INT);
    $new_e = filter_input(INPUT_POST, 'id_epreuve', FILTER_VALIDATE_INT);
    $res = trim((string) filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING));
    if (!$new_a || !$new_e) {
        $_SESSION['error'] = 'Sélection invalide';
        header('Location:modify-results.php?id_athlete=' . $id_ath . '&id_epreuve=' . $id_epr);
        exit();
    }
    try {
        if ($new_a != $id_ath || $new_e != $id_epr) {
            $chk = $connexion->prepare('SELECT * FROM PARTICIPER WHERE id_athlete=:a AND id_epreuve=:e');
            $chk->bindParam(':a', $new_a);
            $chk->bindParam(':e', $new_e);
            $chk->execute();
            if ($chk->rowCount() > 0) {
                $_SESSION['error'] = 'Un résultat existe déjà pour cette combinaison';
                header('Location:modify-results.php?id_athlete=' . $id_ath . '&id_epreuve=' . $id_epr);
                exit();
            }
        }
        $upd = $connexion->prepare('UPDATE PARTICIPER SET id_athlete=:a2, id_epreuve=:e2, resultat=:r WHERE id_athlete=:a AND id_epreuve=:e');
        $upd->bindParam(':a2', $new_a);
        $upd->bindParam(':e2', $new_e);
        $upd->bindParam(':r', $res);
        $upd->bindParam(':a', $id_ath);
        $upd->bindParam(':e', $id_epr);
        $upd->execute();
        $_SESSION['success'] = 'Résultat modifié';
        header('Location:manage-results.php');
        exit();
    } catch (PDOException $e) {
        error_log('modify-result save: ' . $e->getMessage());
        $_SESSION['error'] = 'Erreur';
        header('Location:modify-results.php?id_athlete=' . $id_ath . '&id_epreuve=' . $id_epr);
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
    <title>Modifier un Résultat - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Modifier un Résultat</h1>
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
        <form action="modify-results.php?id_athlete=<?php echo (int) $id_ath; ?>&id_epreuve=<?php echo (int) $id_epr; ?>"
            method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce résultat ?')">
            <input type="hidden" name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="id_athlete">Athlète :</label>
            <select id="id_athlete" name="id_athlete" required>
                <?php foreach ($athletes as $a): ?>
                    <option value="<?php echo (int) $a['id_athlete']; ?>" <?php if ((int) $a['id_athlete'] === (int) $row['id_athlete'])
                           echo 'selected'; ?>>
                        <?php echo htmlspecialchars($a['prenom_athlete'] . ' ' . $a['nom_athlete'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_epreuve">Épreuve :</label>
            <select id="id_epreuve" name="id_epreuve" required>
                <?php foreach ($epreuves as $e): ?>
                    <option value="<?php echo (int) $e['id_epreuve']; ?>" <?php if ((int) $e['id_epreuve'] === (int) $row['id_epreuve'])
                           echo 'selected'; ?>>
                        <?php echo htmlspecialchars($e['nom_epreuve'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="resultat">Résultat :</label>
            <input type="text" id="resultat" name="resultat"
                value="<?php echo htmlspecialchars($row['resultat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

            <input type="submit" value="Modifier le Résultat">
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
