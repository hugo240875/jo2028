<?php
session_start();
require_once("../../../database/database.php");
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch results
try {
    $sql = "SELECT PARTICIPER.id_athlete, PARTICIPER.id_epreuve, PARTICIPER.resultat, ATHLETE.nom_athlete, ATHLETE.prenom_athlete, EPREUVE.nom_epreuve, SPORT.nom_sport
            FROM PARTICIPER
            JOIN ATHLETE ON PARTICIPER.id_athlete = ATHLETE.id_athlete
            JOIN EPREUVE ON PARTICIPER.id_epreuve = EPREUVE.id_epreuve
            LEFT JOIN SPORT ON EPREUVE.id_sport = SPORT.id_sport
            ORDER BY EPREUVE.date_epreuve, SPORT.nom_sport";
    $st = $connexion->prepare($sql);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('manage-results: ' . $e->getMessage());
    $rows = [];
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
    <title>Gestion Résultats - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Gestion des Résultats</h1>
        <div class="action-buttons">
            <button onclick="openAddResultForm()">Ajouter un Résultat</button>
        </div>

        <!-- Tableau des résultats -->
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['success']);
        }

        if (count($rows) > 0) {
            echo "<table><tr><th>Sport</th><th>Épreuve</th><th>Athlète</th><th>Résultat</th><th>Modifier</th><th>Supprimer</th></tr>";
            foreach ($rows as $r) {
                $idAth = (int) $r['id_athlete'];
                $idEpr = (int) $r['id_epreuve'];
                echo "<tr>";
                echo "<td>" . htmlspecialchars($r['nom_sport'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                echo "<td>" . htmlspecialchars($r['nom_epreuve'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                echo "<td>" . htmlspecialchars($r['prenom_athlete'] . ' ' . $r['nom_athlete'], ENT_QUOTES, 'UTF-8') . "</td>";
                echo "<td>" . htmlspecialchars($r['resultat'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                echo "<td><button onclick='openModifyResultForm({$idAth}, {$idEpr})'>Modifier</button></td>";
                echo "<td><button onclick='deleteResultConfirmation({$idAth}, {$idEpr})'>Supprimer</button></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Aucun résultat trouvé.</p>";
        }
        ?>

        <p class="paragraph-link">
            <a class="link-home" href="../admin.php">Accueil administration</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

    <script>
        function openAddResultForm() {
            window.location.href = 'add-results.php';
        }

        function openModifyResultForm(id_athlete, id_epreuve) {
            window.location.href = 'modify-results.php?id_athlete=' + id_athlete + '&id_epreuve=' + id_epreuve;
        }

        function deleteResultConfirmation(id_athlete, id_epreuve) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce résultat ?")) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete-results.php';

                var tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'csrf_token';
                tokenInput.value = '<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>';

                var ath_Input = document.createElement('input');
                ath_Input.type = 'hidden';
                ath_Input.name = 'id_athlete';
                ath_Input.value = id_athlete;

                var epr_Input = document.createElement('input');
                epr_Input.type = 'hidden';
                epr_Input.name = 'id_epreuve';
                epr_Input.value = id_epreuve;

                form.appendChild(tokenInput);
                form.appendChild(ath_Input);
                form.appendChild(epr_Input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>