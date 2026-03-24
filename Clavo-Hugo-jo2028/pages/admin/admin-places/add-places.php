<?php
session_start();
require_once("../../../database/database.php");
if (!isset($_SESSION['login'])) { header('Location: ../../../index.php'); exit(); }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { $_SESSION['error']='Token CSRF invalide.'; header('Location: add-place.php'); exit(); }
    $nom = trim((string)filter_input(INPUT_POST,'nom',FILTER_SANITIZE_STRING));
    $adresse = trim((string)filter_input(INPUT_POST,'adresse',FILTER_SANITIZE_STRING));
    $cp = trim((string)filter_input(INPUT_POST,'cp',FILTER_SANITIZE_STRING));
    $ville = trim((string)filter_input(INPUT_POST,'ville',FILTER_SANITIZE_STRING));
    if ($nom==='') { $_SESSION['error']='Le nom du lieu est requis.'; header('Location:add-place.php'); exit(); }
    try {
        $check = $connexion->prepare('SELECT id_lieu FROM LIEU WHERE nom_lieu = :nom'); $check->bindParam(':nom',$nom); $check->execute();
        if ($check->rowCount()>0){ $_SESSION['error']='Lieu déjà existant.'; header('Location:add-place.php'); exit(); }
        $ins = $connexion->prepare('INSERT INTO LIEU (nom_lieu, adresse_lieu, cp_lieu, ville_lieu) VALUES (:nom,:adresse,:cp,:ville)');
        $ins->bindParam(':nom',$nom); $ins->bindParam(':adresse',$adresse); $ins->bindParam(':cp',$cp); $ins->bindParam(':ville',$ville);
        $ins->execute(); $_SESSION['success']='Lieu ajouté.'; header('Location: manage-places.php'); exit();
    } catch (PDOException $e) { error_log('add-place: '.$e->getMessage()); $_SESSION['error']='Erreur lors de l\'ajout.'; header('Location:add-place.php'); exit(); }
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
    <title>Ajouter un Lieu - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Ajouter un Lieu</h1>
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
        <form action="add-place.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce lieu ?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <label for="nom">Nom du Lieu :</label>
            <input type="text" name="nom" id="nom" required>

            <label for="adresse">Adresse :</label>
            <input type="text" id="adresse" name="adresse">

            <label for="cp">Code Postal :</label>
            <input type="text" id="cp" name="cp">

            <label for="ville">Ville :</label>
            <input type="text" id="ville" name="ville">

            <input type="submit" value="Ajouter le Lieu">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="../admin-places/manage-places.php">Retour à la gestion des lieux</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

</body>

</html>
