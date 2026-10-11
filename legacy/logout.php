<?php
require_once 'session.php';

// Destruir completamente la sesión
logoutUser();

// Redirigir al login u otra página pública
header('Location: index.php');
exit;
