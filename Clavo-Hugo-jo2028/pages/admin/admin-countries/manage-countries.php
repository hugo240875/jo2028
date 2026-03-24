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
    <title>Gestion Pays - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Gestion des Pays</h1>
        <div class="action-buttons">
            <button onclick="openAddCountryForm()">Ajouter un Pays</button>
        </div>
        
        <!-- Tableau des pays -->
        <?php
        require_once("../../../database/database.php");

        try {
            $query = "SELECT * FROM PAYS ORDER BY nom_pays";
            $statement = $connexion->prepare($query);
            $statement->execute();

            if ($statement->rowCount() > 0) {
                echo "<table><tr><th>Pays</th><th>Modifier</th><th>Supprimer</th></tr>";
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_pays'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td><button onclick='openModifyCountryForm({$row['id_pays']})'>Modifier</button></td>";
                    echo "<td><button onclick='deleteCountryConfirmation({$row['id_pays']})'>Supprimer</button></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucun pays trouvé.</p>";
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
        function openAddCountryForm() {
            window.location.href = 'add-countries.php';
        }

        function openModifyCountryForm(id_pays) {
            window.location.href = 'modify-countries.php?id_pays=' + id_pays;
        }

        function deleteCountryConfirmation(id_pays) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce pays ?")) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete-countries.php';

                var tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'csrf_token';
                tokenInput.value = '<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>';

                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_pays';
                idInput.value = id_pays;

                form.appendChild(tokenInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>
