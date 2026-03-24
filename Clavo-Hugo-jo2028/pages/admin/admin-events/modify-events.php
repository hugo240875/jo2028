<?php
session_start(); require_once("../../../database/database.php"); if(!isset($_SESSION['login'])){ header('Location: ../../../index.php'); exit(); } if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
$id = filter_input(INPUT_GET,'id_epreuve',FILTER_VALIDATE_INT); if($id===false||$id===null){ $_SESSION['error']='ID invalide'; header('Location:manage-events.php'); exit(); }
try{ $st=$connexion->prepare('SELECT * FROM EPREUVE WHERE id_epreuve=:id'); $st->bindParam(':id',$id,PDO::PARAM_INT); $st->execute(); if($st->rowCount()===0){ $_SESSION['error']='Épreuve introuvable'; header('Location:manage-events.php'); exit(); } $row=$st->fetch(PDO::FETCH_ASSOC); $sports=$connexion->query('SELECT id_sport, nom_sport FROM SPORT ORDER BY nom_sport')->fetchAll(PDO::FETCH_ASSOC); $lieux=$connexion->query('SELECT id_lieu, nom_lieu FROM LIEU ORDER BY nom_lieu')->fetchAll(PDO::FETCH_ASSOC);}catch(PDOException $e){ error_log('modify-event load: '.$e->getMessage()); $_SESSION['error']='Erreur'; header('Location:manage-events.php'); exit(); }
if($_SERVER['REQUEST_METHOD']==='POST'){ if(!isset($_POST['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])){ $_SESSION['error']='Token CSRF invalide'; header('Location:modify-event.php?id_epreuve='.$id); exit(); } $nom=trim((string)filter_input(INPUT_POST,'nom',FILTER_SANITIZE_STRING)); $date=trim((string)filter_input(INPUT_POST,'date',FILTER_SANITIZE_STRING)); $heure=trim((string)filter_input(INPUT_POST,'heure',FILTER_SANITIZE_STRING)); $id_sport=filter_input(INPUT_POST,'id_sport',FILTER_VALIDATE_INT); $id_lieu=filter_input(INPUT_POST,'id_lieu',FILTER_VALIDATE_INT); if($nom===''){ $_SESSION['error']='Nom requis'; header('Location:modify-event.php?id_epreuve='.$id); exit(); } try{ $chk=$connexion->prepare('SELECT id_epreuve FROM EPREUVE WHERE nom_epreuve=:nom AND date_epreuve=:date AND heure_epreuve=:heure AND id_epreuve<>:id'); $chk->bindParam(':nom',$nom); $chk->bindParam(':date',$date); $chk->bindParam(':heure',$heure); $chk->bindParam(':id',$id,PDO::PARAM_INT); $chk->execute(); if($chk->rowCount()>0){ $_SESSION['error']='Épreuve déjà existante'; header('Location:modify-event.php?id_epreuve='.$id); exit(); } $upd=$connexion->prepare('UPDATE EPREUVE SET nom_epreuve=:nom,date_epreuve=:date,heure_epreuve=:heure,id_lieu=:id_lieu,id_sport=:id_sport WHERE id_epreuve=:id'); $upd->bindParam(':nom',$nom); $upd->bindParam(':date',$date); $upd->bindParam(':heure',$heure); $upd->bindParam(':id_lieu',$id_lieu); $upd->bindParam(':id_sport',$id_sport); $upd->bindParam(':id',$id,PDO::PARAM_INT); $upd->execute(); $_SESSION['success']='Modifié'; header('Location:manage-events.php'); exit(); }catch(PDOException $e){ error_log('modify-event save: '.$e->getMessage()); $_SESSION['error']='Erreur'; header('Location:modify-event.php?id_epreuve='.$id); exit(); } }
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
	<title>Modifier une Épreuve - Jeux Olympiques - Los Angeles 2028</title>
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
		<h1>Modifier une Épreuve</h1>
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
		<form method="post" action="modify-event.php?id_epreuve=<?php echo (int)$id; ?>" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cette épreuve ?')">
			<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

			<label for="nom">Nom de l'Épreuve :</label>
			<input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8'); ?>">

			<label for="date">Date :</label>
			<input type="date" id="date" name="date" value="<?php echo htmlspecialchars($row['date_epreuve'], ENT_QUOTES, 'UTF-8'); ?>">

			<label for="heure">Heure :</label>
			<input type="time" id="heure" name="heure" value="<?php echo htmlspecialchars($row['heure_epreuve'], ENT_QUOTES, 'UTF-8'); ?>">

			<label for="id_sport">Sport :</label>
			<select id="id_sport" name="id_sport">
				<option value="">--</option>
				<?php foreach($sports as $s): $sel = ($s['id_sport']==$row['id_sport'])? ' selected':''; ?>
					<option value="<?php echo (int)$s['id_sport']; ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($s['nom_sport'],ENT_QUOTES,'UTF-8'); ?></option>
				<?php endforeach; ?>
			</select>

			<label for="id_lieu">Lieu :</label>
			<select id="id_lieu" name="id_lieu">
				<option value="">--</option>
				<?php foreach($lieux as $l): $sel = ($l['id_lieu']==$row['id_lieu'])? ' selected':''; ?>
					<option value="<?php echo (int)$l['id_lieu']; ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($l['nom_lieu'],ENT_QUOTES,'UTF-8'); ?></option>
				<?php endforeach; ?>
			</select>

			<input type="submit" value="Enregistrer les modifications">
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
