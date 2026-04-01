<?php
/**
 * Konfigurasi Email SMTP untuk MAGIS
 * Silakan ganti dengan data akun Gmail/Yahoo Anda.
 * 
 * PENTING: Untuk Gmail, gunakan "App Password" (bukan password utama).
 */

define('SMTP_HOST', 'smtp.gmail.com');        // smtp.mail.yahoo.com untuk Yahoo
define('SMTP_PORT', 465);                      // 465 (SSL) atau 587 (TLS)
define('SMTP_USER', 'hikmatun_nazilah@teknokrat.ac.id');   // Ganti dengan email Anda
define('SMTP_PASS', 'mntembqudjmflhys');      // Ganti dengan App Password Anda
define('SMTP_FROM', 'MAGIS System');           // Nama pengirim
?>
