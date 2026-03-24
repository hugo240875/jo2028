<?php
session_start();
require_once("../../../database/database.php");

// Restreindre l'accès
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// On n'accepte que POST pour supprimer (prévenir CSRF via token)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Mauvaise méthode pour la suppression.';
    header('Location: manage-users.php');
    exit();
}

// Vérifier token CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $_SESSION['error'] = 'Token CSRF invalide.';
    header('Location: manage-users.php');
    exit();
}

$id_admin = filter_input(INPUT_POST, 'id_admin', FILTER_VALIDATE_INT);
if ($id_admin === false || $id_admin === null) {
    $_SESSION['error'] = 'ID utilisateur invalide.';
    header('Location: manage-users.php');
    exit();
}

try {
    $del = $connexion->prepare('DELETE FROM ADMINISTRATEUR WHERE id_admin = :id');
    $del->bindParam(':id', $id_admin, PDO::PARAM_INT);
    $del->execute();
    $_SESSION['success'] = 'Utilisateur supprimé.';
    header('Location: manage-users.php');
    exit();
} catch (PDOException $e) {
    error_log('Erreur PDO (delete-user.php): ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de la suppression.';
    header('Location: manage-users.php');
    exit();
}
