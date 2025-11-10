<?php
// gen_icons.php - generate PWA icons using GD
// Run by visiting http://localhost/Auth/gen_icons.php in your browser (or php gen_icons.php CLI)

$outDir = __DIR__ . '/icons';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

function draw_icon($w, $h, $bg_hex, $file) {
    $img = imagecreatetruecolor($w, $h);
    // allocate colors
    list($r, $g, $b) = sscanf($bg_hex, "%02x%02x%02x");
    $bg = imagecolorallocate($img, $r, $g, $b);
    $white = imagecolorallocate($img, 255,255,255);
    // fill background
    imagefilledrectangle($img, 0,0,$w,$h,$bg);

    // draw a stylized fork and spoon (simple shapes)
    // fork: three prongs as rectangles at top center
    $cx = $w/2;
    $prong_w = max(4, (int)($w * 0.06));
    $prong_h = (int)($h * 0.28);
    $prong_spacing = (int)($prong_w * 0.6);
    $start_x = $cx - $prong_w - $prong_spacing;
    for ($i=0;$i<3;$i++) {
        imagefilledrectangle($img, $start_x + $i*($prong_w + $prong_spacing), $h*0.08, $start_x + $i*($prong_w + $prong_spacing) + $prong_w, $h*0.08 + $prong_h, $white);
    }
    // fork handle
    $handle_w = (int)($w*0.12);
    imagefilledrectangle($img, $cx - $handle_w/2, $h*0.08 + $prong_h, $cx + $handle_w/2, $h*0.64, $white);
    // rounded end
    imagefilledellipse($img, $cx, $h*0.76, $handle_w*1.6, $handle_w*1.6, $white);

    // spoon overlay on left side (circle + handle)
    $sx = (int)($w*0.25);
    $sy = (int)($h*0.45);
    $sr = (int)($w*0.16);
    imagefilledellipse($img, $sx, $sy, $sr*1.4, $sr*1.6, $white);
    imagefilledrectangle($img, $sx - $sr*0.12, $sy + $sr*0.6, $sx - $sr*0.12 + $handle_w*0.6, $h*0.92, $white);

    // export PNG
    imagepng($img, $file, 8);
    imagedestroy($img);
}

$bg = 'f97316'; // orange
try {
    draw_icon(192,192,$bg, $outDir . '/icon-192x192.png');
    draw_icon(512,512,$bg, $outDir . '/icon-512x512.png');
    echo "Generated icons:\n";
    echo " - $outDir/icon-192x192.png\n";
    echo " - $outDir/icon-512x512.png\n";
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}

echo "\nOpen your site in the browser and check Developer Tools > Application > Manifest to confirm icons are detected.\n";

?>