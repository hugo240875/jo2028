<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
    <title>Gestion Lieux - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Gestion des Lieux</h1>
        <div class="action-buttons">
            <button onclick="openAddPlaceForm()">Ajouter un Lieu</button>
        </div>
        
        <!-- Tableau des lieux -->
        <?php
        require_once("../../../database/database.php");

        try {
            $query = "SELECT * FROM LIEU ORDER BY nom_lieu";
            $statement = $connexion->prepare($query);
            $statement->execute();

            if ($statement->rowCount() > 0) {
                echo "<table><tr><th>Lieu</th><th>Adresse</th><th>CP</th><th>Ville</th><th>Modifier</th><th>Supprimer</th></tr>";
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['adresse_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['cp_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['ville_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td><button onclick='openModifyPlaceForm({$row['id_lieu']})'>Modifier</button></td>";
                    echo "<td><button onclick='deletePlaceConfirmation({$row['id_lieu']})'>Supprimer</button></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucun lieu trouvé.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
        function openAddPlaceForm() {
            window.location.href = 'add-places.php';
        }

        function openModifyPlaceForm(id_lieu) {
            window.location.href = 'modify-places.php?id_lieu=' + id_lieu;
        }

        function deletePlaceConfirmation(id_lieu) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce lieu ?")) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete-places.php';

                var tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'csrf_token';
                tokenInput.value = '<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>';

                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_lieu';
                idInput.value = id_lieu;

                form.appendChild(tokenInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>
