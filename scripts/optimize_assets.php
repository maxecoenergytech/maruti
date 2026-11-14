<?php

function optimizePng($src, $dst, $maxW, $colors = 128) {
    $im = imagecreatefrompng($src);
    $w = imagesx($im);
    $h = imagesy($im);
    $targetW = min($maxW, $w);
    $targetH = round($h * ($targetW / $w));
    
    $opt = imagecreatetruecolor($targetW, $targetH);
    imagealphablending($opt, false);
    imagesavealpha($opt, true);
    $transparent = imagecolorallocatealpha($opt, 0, 0, 0, 127);
    imagefilledrectangle($opt, 0, 0, $targetW, $targetH, $transparent);
    imagecopyresampled($opt, $im, 0, 0, 0, 0, $targetW, $targetH, $w, $h);
    
    imagetruecolortopalette($opt, true, $colors);
    imagesavealpha($opt, true);
    
    imagepng($opt, $dst, 9);
    imagedestroy($im);
    imagedestroy($opt);
    
    echo basename($src) . ": " . round(filesize($src)/1024, 1) . " KB -> " . round(filesize($dst)/1024, 1) . " KB\n";
}

optimizePng(__DIR__ . '/../public/images/maruti-logo.png', __DIR__ . '/../public/images/maruti-logo-opt.png', 400, 128);
optimizePng(__DIR__ . '/../public/images/maruti-signature.png', __DIR__ . '/../public/images/maruti-signature-opt.png', 320, 128);
