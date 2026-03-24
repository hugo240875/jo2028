<?php
session_start();
require_once("../../../database/database.php");

// Restreindre l'accès aux administrateurs connectés
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Générer un token CSRF si nécessaire
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Messages flash
$msgError = $_SESSION['error'] ?? null;
$msgSuccess = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

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
    <title>Gestion Utilisateurs - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Gestion des Utilisateurs</h1>
        <div class="action-buttons">
            <button onclick="openAddUserForm()">Ajouter un Utilisateur</button>
        </div>
        
        <!-- Tableau des utilisateurs -->
        <?php
        if ($msgError): ?>
            <p style="color: red;"><?php echo htmlspecialchars($msgError, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if ($msgSuccess): ?>
            <p style="color: green;"><?php echo htmlspecialchars($msgSuccess, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php
        try {
            $query = "SELECT id_admin, login, nom_admin, prenom_admin FROM ADMINISTRATEUR ORDER BY login";
            $statement = $connexion->prepare($query);
            $statement->execute();

            if ($statement->rowCount() > 0) {
                echo "<table><tr><th>Login</th><th>Nom</th><th>Prénom</th><th>Modifier</th><th>Supprimer</th></tr>";
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['login'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_admin'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['prenom_admin'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td><button onclick='openModifyUserForm({$row['id_admin']})'>Modifier</button></td>";
                    echo "<td><button onclick='deleteUserConfirmation({$row['id_admin']})'>Supprimer</button></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucun utilisateur trouvé.</p>";
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
        function openAddUserForm() {
            window.location.href = 'add-users.php';
        }

        function openModifyUserForm(id) {
            window.location.href = 'modify-users.php?id_admin=' + id;
        }

        function deleteUserConfirmation(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete-users.php';

                var tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'csrf_token';
                tokenInput.value = '<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>';

                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_admin';
                idInput.value = id;

                form.appendChild(tokenInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>
