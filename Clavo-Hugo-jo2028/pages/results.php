<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/styles-computer.css">
    <link rel="stylesheet" href="../css/styles-responsive.css">
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <title>Résultats - Jeux Olympiques - Los Angeles 2028</title>
</head>

<body>
    <header>
        <nav>
            <ul class="menu">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="sports.php">Sports</a></li>
                <li><a href="events.php">Calendrier des évènements</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="login.php">Accès administrateur</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Résultats</h1>

        <?php
        require_once("../database/database.php");

        // Vérifier que la connexion PDO existe
        if (!isset($connexion) || !$connexion) {
            echo "<p style='color:red;'>Erreur : connexion à la base non établie. Vérifiez database/database.php.</p>";
            exit;
        }

        try {
            // Requête pour récupérer les résultats : participant, épreuve, sport, date/heure et lieu
            $query = "
                SELECT
                    SPORT.nom_sport,
                    EPREUVE.nom_epreuve,
                    EPREUVE.date_epreuve,
                    EPREUVE.heure_epreuve,
                    LIEU.nom_lieu AS lieu,
                    ATHLETE.nom_athlete,
                    ATHLETE.prenom_athlete,
                    PARTICIPER.resultat
                FROM PARTICIPER
                JOIN ATHLETE ON PARTICIPER.id_athlete = ATHLETE.id_athlete
                JOIN EPREUVE ON PARTICIPER.id_epreuve = EPREUVE.id_epreuve
                JOIN SPORT ON EPREUVE.id_sport = SPORT.id_sport
                LEFT JOIN LIEU ON EPREUVE.id_lieu = LIEU.id_lieu
                ORDER BY EPREUVE.date_epreuve, EPREUVE.heure_epreuve, SPORT.nom_sport, EPREUVE.nom_epreuve
            ";

            $statement = $connexion->prepare($query);
            $statement->execute();

            if ($statement->rowCount() > 0) {
                echo "<table>";
                echo "<tr><th class='color'>Sport</th><th class='color'>Épreuve</th><th class='color'>Athlète</th><th class='color'>Résultat</th><th class='color'>Date</th><th class='color'>Heure</th><th class='color'>Lieu</th></tr>";

                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $sport = htmlspecialchars($row['nom_sport'], ENT_QUOTES, 'UTF-8');
                    $epreuve = htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8');
                    $athlete = htmlspecialchars(($row['prenom_athlete'] . ' ' . $row['nom_athlete']), ENT_QUOTES, 'UTF-8');
                    $resultat = htmlspecialchars($row['resultat'] ?? '', ENT_QUOTES, 'UTF-8');

                    $dateFormatted = htmlspecialchars($row['date_epreuve'] ?? '', ENT_QUOTES, 'UTF-8');
                    if (!empty($row['date_epreuve'])) {
                        $dt = date_create($row['date_epreuve']);
                        if ($dt !== false) $dateFormatted = date_format($dt, 'd/m/Y');
                    }

                    $heureFormatted = htmlspecialchars($row['heure_epreuve'] ?? '', ENT_QUOTES, 'UTF-8');
                    if (!empty($row['heure_epreuve'])) {
                        $t = date_create($row['heure_epreuve']);
                        if ($t !== false) $heureFormatted = date_format($t, 'H:i');
                    }

                    $lieu = htmlspecialchars($row['lieu'] ?? '', ENT_QUOTES, 'UTF-8');

                    echo "<tr>";
                    echo "<td>{$sport}</td>";
                    echo "<td>{$epreuve}</td>";
                    echo "<td>{$athlete}</td>";
                    echo "<td>{$resultat}</td>";
                    echo "<td>{$dateFormatted}</td>";
                    echo "<td>{$heureFormatted}</td>";
                    echo "<td>{$lieu}</td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun résultat trouvé.</p>";
            }
        } catch (PDOException $e) {
            // Message utilisateur générique + log pour le serveur
            error_log("Erreur PDO (results.php) : " . $e->getMessage());
            echo "<p style='color: red;'>Erreur : Impossible de récupérer les résultats. Veuillez réessayer plus tard.</p>";
        }
        ?>

        <p class="paragraph-link">
            <a class="link-home" href="../index.php">Retour Accueil</a>
        </p>

    </main>
    <footer>
        <figure>
            <img src="../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>

</html>
