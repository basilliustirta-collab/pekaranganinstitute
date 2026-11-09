<?php
/**
 * Pengalihan Login Administrator - Pekarangan Institute
 * Mengarahkan permintaan ke halaman login admin utama (/login.php).
 */

require_once __DIR__ . '/../includes/fungsi.php';

$query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
alihkan('login.php' . $query);
exit;
