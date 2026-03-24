<?php
session_start();
require_once("../../../database/database.php");
if (!isset($_SESSION['login'])) {
	header('Location: ../../../index.php');
	exit();
}
if (empty($_SESSION['csrf_token']))
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$id = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
	$_SESSION['error'] = 'ID invalide';
	header('Location:manage-athletes.php');
	exit();
}
try {
	$st = $connexion->prepare('SELECT * FROM ATHLETE WHERE id_athlete=:id');
	$st->bindParam(':id', $id, PDO::PARAM_INT);
	$st->execute();
	if ($st->rowCount() === 0) {
		$_SESSION['error'] = 'Athlète introuvable';
		header('Location:manage-athletes.php');
		exit();
	}
	$row = $st->fetch(PDO::FETCH_ASSOC);
	$pays = $connexion->query('SELECT id_pays, nom_pays FROM PAYS ORDER BY nom_pays')->fetchAll(PDO::FETCH_ASSOC);
	$genres = $connexion->query('SELECT id_genre, nom_genre FROM GENRE ORDER BY id_genre')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	error_log('modify-athlete load: ' . $e->getMessage());
	$_SESSION['error'] = 'Erreur';
	header('Location:manage-athletes.php');
	exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
		$_SESSION['error'] = 'Token CSRF invalide';
		header('Location:modify-athletes.php?id_athlete=' . $id);
		exit();
	}
	$nom = trim((string) filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING));
	$prenom = trim((string) filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING));
	$id_pays = filter_input(INPUT_POST, 'id_pays', FILTER_VALIDATE_INT);
	$id_genre = filter_input(INPUT_POST, 'id_genre', FILTER_VALIDATE_INT);
	if ($nom === '') {
		$_SESSION['error'] = 'Nom requis';
		header('Location:modify-athletes.php?id_athlete=' . $id);
		exit();
	}
	try {
		$chk = $connexion->prepare('SELECT id_athlete FROM ATHLETE WHERE nom_athlete=:nom AND prenom_athlete=:prenom AND id_athlete<>:id');
		$chk->bindParam(':nom', $nom);
		$chk->bindParam(':prenom', $prenom);
		$chk->bindParam(':id', $id, PDO::PARAM_INT);
		$chk->execute();
		if ($chk->rowCount() > 0) {
			$_SESSION['error'] = 'Athlète déjà existant';
			header('Location:modify-athletes.php?id_athlete=' . $id);
			exit();
		}
		$upd = $connexion->prepare('UPDATE ATHLETE SET nom_athlete=:nom, prenom_athlete=:prenom, id_pays=:id_pays, id_genre=:id_genre WHERE id_athlete=:id');
		$upd->bindParam(':nom', $nom);
		$upd->bindParam(':prenom', $prenom);
		$upd->bindParam(':id_pays', $id_pays);
		$upd->bindParam(':id_genre', $id_genre);
		$upd->bindParam(':id', $id, PDO::PARAM_INT);
		$upd->execute();
		$_SESSION['success'] = 'Modifié';
		header('Location:manage-athletes.php');
		exit();
	} catch (PDOException $e) {
		error_log('modify-athlete save: ' . $e->getMessage());
		$_SESSION['error'] = 'Erreur';
		header('Location:modify-athletes.php?id_athlete=' . $id);
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
	<title>Modifier un Athlète - Jeux Olympiques - Los Angeles 2028</title>
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
		<h1>Modifier un Athlète</h1>
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
		<form method="post" action="modify-athletes.php?id_athlete=<?php echo (int) $id; ?>"
			onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet athlète ?')">
			<input type="hidden" name="csrf_token"
				value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

			<label for="nom">Nom :</label>
			<input type="text" id="nom" name="nom" required
				value="<?php echo htmlspecialchars($row['nom_athlete'], ENT_QUOTES, 'UTF-8'); ?>">

			<label for="prenom">Prénom :</label>
			<input type="text" id="prenom" name="prenom"
				value="<?php echo htmlspecialchars($row['prenom_athlete'], ENT_QUOTES, 'UTF-8'); ?>">

			<label for="id_pays">Pays :</label>
			<select id="id_pays" name="id_pays">
				<option value="">--</option>
				<?php foreach ($pays as $p):
					$sel = ($p['id_pays'] == $row['id_pays']) ? ' selected' : ''; ?>
					<option value="<?php echo (int) $p['id_pays']; ?>" <?php echo $sel; ?>>
						<?php echo htmlspecialchars($p['nom_pays'], ENT_QUOTES, 'UTF-8'); ?></option>
				<?php endforeach; ?>
			</select>

			<label for="id_genre">Genre :</label>
			<select id="id_genre" name="id_genre">
				<option value="">--</option>
				<?php foreach ($genres as $g):
					$sel = ($g['id_genre'] == $row['id_genre']) ? ' selected' : ''; ?>
					<option value="<?php echo (int) $g['id_genre']; ?>" <?php echo $sel; ?>>
						<?php echo htmlspecialchars($g['nom_genre'], ENT_QUOTES, 'UTF-8'); ?></option>
				<?php endforeach; ?>
			</select>

			<input type="submit" value="Enregistrer les modifications">
		</form>

		<p class="paragraph-link">
			<a class="link-home" href="../admin-athletes/manage-athletes.php">Retour à la gestion des athlètes</a>
		</p>
	</main>

	<footer>
		<figure>
			<img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
		</figure>
	</footer>

</body>

</html>