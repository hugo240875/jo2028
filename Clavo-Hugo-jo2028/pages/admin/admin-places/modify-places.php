<?php
session_start();
require_once("../../../database/database.php");
if (!isset($_SESSION['login'])) { header('Location: ../../../index.php'); exit(); }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

$id = filter_input(INPUT_GET,'id_lieu',FILTER_VALIDATE_INT);
if ($id===null || $id===false){ $_SESSION['error']='ID manquant'; header('Location:manage-places.php'); exit(); }
try{ $st=$connexion->prepare('SELECT * FROM LIEU WHERE id_lieu=:id'); $st->bindParam(':id',$id,PDO::PARAM_INT); $st->execute(); if($st->rowCount()===0){ $_SESSION['error']='Lieu introuvable'; header('Location:manage-places.php'); exit(); } $row=$st->fetch(PDO::FETCH_ASSOC);}catch(PDOException $e){ error_log('modify-place load: '.$e->getMessage()); $_SESSION['error']='Erreur'; header('Location:manage-places.php'); exit(); }

if ($_SERVER['REQUEST_METHOD']==='POST'){
    if(!isset($_POST['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])){ $_SESSION['error']='Token CSRF invalide.'; header('Location: modify-place.php?id_lieu='.$id); exit(); }
    $nom=trim((string)filter_input(INPUT_POST,'nom',FILTER_SANITIZE_STRING));
    $adresse=trim((string)filter_input(INPUT_POST,'adresse',FILTER_SANITIZE_STRING));
    $cp=trim((string)filter_input(INPUT_POST,'cp',FILTER_SANITIZE_STRING));
    $ville=trim((string)filter_input(INPUT_POST,'ville',FILTER_SANITIZE_STRING));
    if($nom===''){ $_SESSION['error']='Nom requis'; header('Location: modify-place.php?id_lieu='.$id); exit(); }
    try{
        $chk=$connexion->prepare('SELECT id_lieu FROM LIEU WHERE nom_lieu=:nom AND id_lieu<>:id'); $chk->bindParam(':nom',$nom); $chk->bindParam(':id',$id,PDO::PARAM_INT); $chk->execute(); if($chk->rowCount()>0){ $_SESSION['error']='Nom déjà utilisé'; header('Location: modify-place.php?id_lieu='.$id); exit(); }
        $upd=$connexion->prepare('UPDATE LIEU SET nom_lieu=:nom, adresse_lieu=:adresse, cp_lieu=:cp, ville_lieu=:ville WHERE id_lieu=:id'); $upd->bindParam(':nom',$nom); $upd->bindParam(':adresse',$adresse); $upd->bindParam(':cp',$cp); $upd->bindParam(':ville',$ville); $upd->bindParam(':id',$id,PDO::PARAM_INT); $upd->execute(); $_SESSION['success']='Modifié'; header('Location:manage-places.php'); exit();
    }catch(PDOException $e){ error_log('modify-place save: '.$e->getMessage()); $_SESSION['error']='Erreur lors de la modification'; header('Location:modify-place.php?id_lieu='.$id); exit(); }
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
    <title>Modifier un Lieu - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Modifier un Lieu</h1>
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
        <form method="post" action="modify-place.php?id_lieu=<?php echo (int)$id; ?>" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce lieu ?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($row['nom_lieu'],ENT_QUOTES,'UTF-8'); ?>">

            <label for="adresse">Adresse :</label>
            <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($row['adresse_lieu'],ENT_QUOTES,'UTF-8'); ?>">

            <label for="cp">Code Postal :</label>
            <input type="text" id="cp" name="cp" value="<?php echo htmlspecialchars($row['cp_lieu'],ENT_QUOTES,'UTF-8'); ?>">

            <label for="ville">Ville :</label>
            <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($row['ville_lieu'],ENT_QUOTES,'UTF-8'); ?>">

            <input type="submit" value="Enregistrer les modifications">
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
