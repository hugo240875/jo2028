<?php
session_start();
require_once("../../../database/database.php");
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location:manage-results.php');
    exit();
}
if (empty($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = 'Token CSRF invalide';
    header('Location:manage-results.php');
    exit();
}
$id_ath = filter_input(INPUT_POST, 'id_athlete', FILTER_VALIDATE_INT);
$id_epr = filter_input(INPUT_POST, 'id_epreuve', FILTER_VALIDATE_INT);
if (!$id_ath || !$id_epr) {
    $_SESSION['error'] = 'Paramètres manquants';
    header('Location:manage-results.php');
    exit();
}
try {
    $del = $connexion->prepare('DELETE FROM PARTICIPER WHERE id_athlete=:a AND id_epreuve=:e');
    $del->bindParam(':a', $id_ath);
    $del->bindParam(':e', $id_epr);
    $del->execute();
    $_SESSION['success'] = 'Résultat supprimé';
    header('Location:manage-results.php');
    exit();
} catch (PDOException $e) {
    error_log('delete-result: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur suppression';
    header('Location:manage-results.php');
    exit();
}
?>