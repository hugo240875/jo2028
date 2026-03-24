<?php
session_start(); require_once("../../../database/database.php"); if(!isset($_SESSION['login'])){ header('Location: ../../../index.php'); exit(); } if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!isset($_POST['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])){ $_SESSION['error']='Token CSRF invalide'; header('Location:add-country.php'); exit(); }
    $nom=trim((string)filter_input(INPUT_POST,'nom',FILTER_SANITIZE_STRING)); if($nom===''){ $_SESSION['error']='Nom requis'; header('Location:add-country.php'); exit(); }
    try{ $chk=$connexion->prepare('SELECT id_pays FROM PAYS WHERE nom_pays=:nom'); $chk->bindParam(':nom',$nom); $chk->execute(); if($chk->rowCount()>0){ $_SESSION['error']='Pays déjà existant'; header('Location:add-country.php'); exit(); } $ins=$connexion->prepare('INSERT INTO PAYS (nom_pays) VALUES (:nom)'); $ins->bindParam(':nom',$nom); $ins->execute(); $_SESSION['success']='Pays ajouté'; header('Location:manage-countries.php'); exit(); }catch(PDOException $e){ error_log('add-country: '.$e->getMessage()); $_SESSION['error']='Erreur'; header('Location:add-country.php'); exit(); }
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
    <title>Ajouter un Pays - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Ajouter un Pays</h1>
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
        <form action="add-countries.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce pays ?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <label for="nom">Nom du Pays :</label>
            <input type="text" id="nom" name="nom" required>
            <input type="submit" value="Ajouter le Pays">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="../admin-countries/manage-countries.php">Retour à la gestion des pays</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

</body>

</html>
