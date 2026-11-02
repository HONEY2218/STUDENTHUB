<?php
require 'functions.php';
$_SESSION = [];
session_destroy();
session_start();
flash('info', 'You have been logged out.');
header('Location: login.php');
exit();
