<?php
session_start(); require_once("../../../database/database.php"); if(!isset($_SESSION['login'])){ header('Location: ../../../index.php'); exit(); } if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
// get sports and lieux for selects
try{ $sports=$connexion->query('SELECT id_sport, nom_sport FROM SPORT ORDER BY nom_sport')->fetchAll(PDO::FETCH_ASSOC); $lieux=$connexion->query('SELECT id_lieu, nom_lieu FROM LIEU ORDER BY nom_lieu')->fetchAll(PDO::FETCH_ASSOC);}catch(PDOException $e){ error_log('add-event load lists: '.$e->getMessage()); $sports=[]; $lieux=[]; }
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!isset($_POST['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])){ $_SESSION['error']='Token CSRF invalide'; header('Location:add-event.php'); exit(); }
    $nom=trim((string)filter_input(INPUT_POST,'nom',FILTER_SANITIZE_STRING)); $date=trim((string)filter_input(INPUT_POST,'date',FILTER_SANITIZE_STRING)); $heure=trim((string)filter_input(INPUT_POST,'heure',FILTER_SANITIZE_STRING)); $id_sport=filter_input(INPUT_POST,'id_sport',FILTER_VALIDATE_INT); $id_lieu=filter_input(INPUT_POST,'id_lieu',FILTER_VALIDATE_INT);
    if($nom===''){ $_SESSION['error']='Nom requis'; header('Location:add-event.php'); exit(); }
    try{ $chk=$connexion->prepare('SELECT id_epreuve FROM EPREUVE WHERE nom_epreuve=:nom AND date_epreuve=:date AND heure_epreuve=:heure'); $chk->bindParam(':nom',$nom); $chk->bindParam(':date',$date); $chk->bindParam(':heure',$heure); $chk->execute(); if($chk->rowCount()>0){ $_SESSION['error']='Épreuve déjà existante'; header('Location:add-event.php'); exit(); } $ins=$connexion->prepare('INSERT INTO EPREUVE (nom_epreuve,date_epreuve,heure_epreuve,id_lieu,id_sport) VALUES (:nom,:date,:heure,:id_lieu,:id_sport)'); $ins->bindParam(':nom',$nom); $ins->bindParam(':date',$date); $ins->bindParam(':heure',$heure); $ins->bindParam(':id_lieu',$id_lieu); $ins->bindParam(':id_sport',$id_sport); $ins->execute(); $_SESSION['success']='Épreuve ajoutée'; header('Location:manage-events.php'); exit(); }catch(PDOException $e){ error_log('add-event save: '.$e->getMessage()); $_SESSION['error']='Erreur'; header('Location:add-event.php'); exit(); }
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
    <title>Ajouter une Épreuve - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Ajouter une Épreuve</h1>
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
        <form method="post" action="add-event.php" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cette épreuve ?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <label for="nom">Nom de l'Épreuve :</label>
            <input type="text" id="nom" name="nom" required>

            <label for="date">Date :</label>
            <input type="date" id="date" name="date">

            <label for="heure">Heure :</label>
            <input type="time" id="heure" name="heure">

            <label for="id_sport">Sport :</label>
            <select id="id_sport" name="id_sport">
                <option value="">--</option>
                <?php foreach($sports as $s): ?>
                    <option value="<?php echo (int)$s['id_sport']; ?>"><?php echo htmlspecialchars($s['nom_sport'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="id_lieu">Lieu :</label>
            <select id="id_lieu" name="id_lieu">
                <option value="">--</option>
                <?php foreach($lieux as $l): ?>
                    <option value="<?php echo (int)$l['id_lieu']; ?>"><?php echo htmlspecialchars($l['nom_lieu'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- small spacer between select and submit -->
            <div style="height:12px;">&nbsp;</div>

            <input type="submit" value="Ajouter l'Épreuve">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="../admin-events/manage-events.php">Retour à la gestion du calendrier</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

</body>

</html>
