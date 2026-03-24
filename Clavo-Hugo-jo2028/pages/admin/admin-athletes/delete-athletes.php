<?php
session_start();
require_once("../../../database/database.php");
if(!isset($_SESSION['login'])){ header('Location: ../../../index.php'); exit(); }
if($_SERVER['REQUEST_METHOD']!=='POST'){ $_SESSION['error']='Mauvaise méthode'; header('Location:manage-athletes.php'); exit(); }
if(!isset($_POST['csrf_token'])||!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])){ $_SESSION['error']='Token CSRF invalide'; header('Location:manage-athletes.php'); exit(); }
$id=filter_input(INPUT_POST,'id_athlete',FILTER_VALIDATE_INT); if($id===false||$id===null){ $_SESSION['error']='ID invalide'; header('Location:manage-athletes.php'); exit(); }
try{ $del=$connexion->prepare('DELETE FROM ATHLETE WHERE id_athlete=:id'); $del->bindParam(':id',$id,PDO::PARAM_INT); $del->execute(); $_SESSION['success']='Athlète supprimé'; header('Location:manage-athletes.php'); exit(); }catch(PDOException $e){ error_log('delete-athlete: '.$e->getMessage()); $_SESSION['error']='Erreur'; header('Location:manage-athletes.php'); exit(); }







