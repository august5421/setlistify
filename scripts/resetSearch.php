<?php
session_start();

unset($_SESSION['searchParams']);
unset($_SESSION['searchResults']);
unset($_SESSION['step']);

header("Location: ../index.php");
exit();
?>
