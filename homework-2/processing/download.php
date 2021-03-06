<?php

session_start();

require '../includes/functions.php';

if (!existLoggedUser()) {
    header('Location: ../index.php');
    exit();
}

$postId = (int) $_GET['post'];
$userId = $_SESSION['userId'];

$fileToDownload = $postId . '.jpg';
$dir = realpath('../users/user-' . $userId);
$fullPath = $dir . DIRECTORY_SEPARATOR . $postId . '.jpg';

downloadFile($fullPath);