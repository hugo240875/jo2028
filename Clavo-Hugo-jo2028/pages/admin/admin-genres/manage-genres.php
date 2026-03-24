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
    <title>Gestion Genres - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Gestion des Genres</h1>
        <div class="action-buttons">
            <button onclick="openAddGenreForm()">Ajouter un Genre</button>
        </div>
        
        <!-- Tableau des genres -->
        <?php
        require_once("../../../database/database.php");

        try {
            $query = "SELECT * FROM GENRE ORDER BY nom_genre";
            $statement = $connexion->prepare($query);
            $statement->execute();

            if ($statement->rowCount() > 0) {
                echo "<table><tr><th>Genre</th><th>Modifier</th><th>Supprimer</th></tr>";
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_genre'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td><button onclick='openModifyGenreForm({$row['id_genre']})'>Modifier</button></td>";
                    echo "<td><button onclick='deleteGenreConfirmation({$row['id_genre']})'>Supprimer</button></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucun genre trouvé.</p>";
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
        function openAddGenreForm() {
            window.location.href = 'add-genre.php';
        }

        function openModifyGenreForm(id_genre) {
            window.location.href = 'modify-genre.php?id_genre=' + id_genre;
        }

        function deleteGenreConfirmation(id_genre) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce genre ?")) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete-genre.php';

                var tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'csrf_token';
                tokenInput.value = '<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>';

                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_genre';
                idInput.value = id_genre;

                form.appendChild(tokenInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>
