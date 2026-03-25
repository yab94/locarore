<?php
/**
 * Génère public/assets/images/og-default.jpg
 * Exécuter une seule fois : php bin/gen-og-default.php
 */

$w = 1200;
$h = 630;

$img = imagecreatetruecolor($w, $h);

// Couleur de fond : brand-600 #ff0a52
$bg = imagecolorallocate($img, 0xFF, 0x0A, 0x52);
imagefill($img, 0, 0, $bg);

// Bande décorative droite (brand-700 #d40047)
$accent = imagecolorallocate($img, 0xD4, 0x00, 0x47);
imagefilledrectangle($img, 900, 0, $w, $h, $accent);

// Blanc pour le texte
$white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);
$offwhite = imagecolorallocate($img, 0xFF, 0xD6, 0xE2); // brand-100

// Titre principal (police GD built-in = bitmap, taille 5 = 9×15 px/char)
// On la scale via imagecopyresampled pour obtenir quelque chose de lisible
$tmp = imagecreatetruecolor(strlen('Latyana Evenements') * 9, 15);
$tmpBg = imagecolorallocate($tmp, 0xFF, 0x0A, 0x52);
$tmpW  = imagecolorallocate($tmp, 0xFF, 0xFF, 0xFF);
imagefill($tmp, 0, 0, $tmpBg);
imagestring($tmp, 5, 0, 0, 'Latyana Evenements', $tmpW);

$factor = 7; // ×7
$tw = strlen('Latyana Evenements') * 9 * $factor;
$th = 15 * $factor;
$tx = (int)(($w - $tw) / 2);
$ty = (int)(($h / 2) - $th - 20);
imagecopyresampled($img, $tmp, $tx, $ty, 0, 0, $tw, $th, strlen('Latyana Evenements') * 9, 15);

// Sous-titre
$sub = 'Location de decoration';
$tmp2 = imagecreatetruecolor(strlen($sub) * 9, 15);
$tmpBg2 = imagecolorallocate($tmp2, 0xFF, 0x0A, 0x52);
$tmpW2  = imagecolorallocate($tmp2, 0xFF, 0xD6, 0xE2);
imagefill($tmp2, 0, 0, $tmpBg2);
imagestring($tmp2, 5, 0, 0, $sub, $tmpW2);

$factor2 = 3;
$tw2 = strlen($sub) * 9 * $factor2;
$th2 = 15 * $factor2;
$tx2 = (int)(($w - $tw2) / 2);
$ty2 = (int)($h / 2) + 20;
imagecopyresampled($img, $tmp2, $tx2, $ty2, 0, 0, $tw2, $th2, strlen($sub) * 9, 15);

// Ligne séparatrice
imagesetthickness($img, 3);
imageline($img, 80, $h / 2 + 5, $w - 300, $h / 2 + 5, $offwhite);

// Sauvegarde
$dest = __DIR__ . '/../public/assets/images/og-default.jpg';
imagejpeg($img, $dest, 85);

echo "Généré : $dest (" . round(filesize($dest) / 1024) . " Ko)\n";
