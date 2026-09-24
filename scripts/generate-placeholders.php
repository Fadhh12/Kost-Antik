<?php
// Generator placeholder foto Kost Antik (SVG): fasad, kamar, dan close-up tegel kunci.
// Jalankan: php scripts/generate-placeholders.php
$out = __DIR__.'/../public/images/placeholders';
@mkdir($out, 0777, true);

$palettes = [
    ['wall' => '#E4ECE8', 'wall2' => '#D2DED8', 'dark' => '#0F4D48', 'deep' => '#082B28', 'brass' => '#C39A3F', 'tile' => '#17645D', 'tile2' => '#F6F7F5', 'wood' => '#7A4E2D'],
    ['wall' => '#F1EEE6', 'wall2' => '#E3DDD0', 'dark' => '#4E311C', 'deep' => '#2E1D10', 'brass' => '#C39A3F', 'tile' => '#7A4E2D', 'tile2' => '#F6F7F5', 'wood' => '#5E3B22'],
    ['wall' => '#DCE6EA', 'wall2' => '#C9D6DC', 'dark' => '#23454F', 'deep' => '#15303A', 'brass' => '#E2C27A', 'tile' => '#23756D', 'tile2' => '#EDEFEC', 'wood' => '#7A4E2D'],
    ['wall' => '#EDEFEC', 'wall2' => '#DDE1DD', 'dark' => '#2E3B3A', 'deep' => '#14201F', 'brass' => '#C39A3F', 'tile' => '#B23A2A', 'tile2' => '#F6F7F5', 'wood' => '#7A4E2D'],
];

function tilePattern(string $id, array $p, int $size = 80): string
{
    $h = $size / 2;
    $q = $size / 3;
    $sq = $size - $q;
    $d = $q * 0.8;
    $a = $h - $d;
    $b = $h + $d;
    $r = $size * 0.06;
    $q2 = $q * 0.62;
    $sq2 = $size - $q2;
    $sw = $size * 0.035;
    $ring = "M0 {$q}A{$q} {$q} 0 0 0 {$q} 0 M{$size} {$q}A{$q} {$q} 0 0 1 {$sq} 0 M{$size} {$sq}A{$q} {$q} 0 0 0 {$sq} {$size} M0 {$sq}A{$q} {$q} 0 0 1 {$q} {$size}";
    $fillQ = "M0 {$q2}A{$q2} {$q2} 0 0 0 {$q2} 0H0z M{$size} {$q2}A{$q2} {$q2} 0 0 1 {$sq2} 0H{$size}z M{$size} {$sq2}A{$q2} {$q2} 0 0 0 {$sq2} {$size}H{$size}z M0 {$sq2}A{$q2} {$q2} 0 0 1 {$q2} {$size}H0z";

    return <<<SVG
<pattern id="{$id}" width="{$size}" height="{$size}" patternUnits="userSpaceOnUse">
  <rect width="{$size}" height="{$size}" fill="{$p['tile2']}"/>
  <path d="{$fillQ}" fill="{$p['tile']}"/>
  <path d="{$ring}" fill="none" stroke="{$p['tile']}" stroke-width="{$sw}"/>
  <path d="M{$h} {$a}L{$b} {$h}L{$h} {$b}L{$a} {$h}z" fill="{$p['brass']}"/>
  <circle cx="{$h}" cy="{$h}" r="{$r}" fill="{$p['tile2']}"/>
  <rect width="{$size}" height="{$size}" fill="none" stroke="{$p['deep']}" stroke-opacity=".12" stroke-width="2"/>
</pattern>
SVG;
}

function facade(array $p, int $variant): string
{
    $pat = tilePattern('t', $p, 70);
    $windows = '';
    $cols = $variant % 2 ? 4 : 5;
    $w = 1100 / $cols;
    foreach ([260, 520] as $y) {
        for ($i = 0; $i < $cols; $i++) {
            $x = 250 + $i * $w + ($w - 130) / 2;
            if ($y === 520 && $x + 130 > 700 && $x < 900) {
                continue;
            }
            $windows .= "<rect x='{$x}' y='{$y}' width='130' height='170' rx='6' fill='{$p['dark']}'/>";
            $windows .= "<rect x='".($x + 10)."' y='".($y + 10)."' width='50' height='150' rx='3' fill='{$p['wall2']}' opacity='.55'/>";
            $windows .= "<rect x='".($x + 70)."' y='".($y + 10)."' width='50' height='150' rx='3' fill='{$p['wall2']}' opacity='.35'/>";
            $windows .= "<rect x='".($x - 10)."' y='".($y + 170)."' width='150' height='12' rx='3' fill='{$p['wood']}'/>";
        }
    }

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 1000" preserveAspectRatio="xMidYMid slice">
<defs>{$pat}</defs>
<rect width="1600" height="1000" fill="{$p['wall2']}"/>
<rect x="200" y="150" width="1200" height="700" fill="{$p['wall']}"/>
<rect x="170" y="120" width="1260" height="40" rx="6" fill="{$p['deep']}"/>
<rect x="200" y="455" width="1200" height="18" fill="{$p['wall2']}"/>
{$windows}
<path d="M720 850V640a80 80 0 0 1 160 0v210z" fill="{$p['wood']}"/>
<circle cx="860" cy="760" r="7" fill="{$p['brass']}"/>
<rect x="0" y="850" width="1600" height="150" fill="url(#t)"/>
<rect x="0" y="850" width="1600" height="10" fill="{$p['deep']}" opacity=".25"/>
<circle cx="140" cy="820" r="60" fill="{$p['tile']}" opacity=".85"/><rect x="115" y="820" width="50" height="40" fill="{$p['wood']}"/>
<circle cx="1470" cy="810" r="70" fill="{$p['tile']}" opacity=".85"/><rect x="1440" y="815" width="60" height="45" fill="{$p['wood']}"/>
</svg>
SVG;
}

function room(array $p, int $variant): string
{
    $pat = tilePattern('t', $p, 90);
    $bedX = $variant % 2 ? 860 : 200;
    $deskX = $variant % 2 ? 200 : 1080;
    $b30 = $bedX + 30; $b260 = $bedX + 260;
    $d20 = $deskX + 20; $d284 = $deskX + 284; $d120 = $deskX + 120; $d160 = $deskX + 160;

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 1000" preserveAspectRatio="xMidYMid slice">
<defs>{$pat}</defs>
<rect width="1600" height="700" fill="{$p['wall']}"/>
<rect x="0" y="0" width="1600" height="60" fill="{$p['wall2']}"/>
<rect x="640" y="140" width="320" height="330" rx="8" fill="{$p['dark']}"/>
<rect x="660" y="160" width="135" height="290" rx="4" fill="#FFFFFF" opacity=".75"/>
<rect x="805" y="160" width="135" height="290" rx="4" fill="#FFFFFF" opacity=".6"/>
<rect x="620" y="470" width="360" height="16" rx="4" fill="{$p['wood']}"/>
<rect x="0" y="700" width="1600" height="300" fill="url(#t)"/>
<rect x="0" y="700" width="1600" height="8" fill="{$p['deep']}" opacity=".2"/>
<g>
  <rect x="{$bedX}" y="560" width="540" height="190" rx="18" fill="{$p['wood']}"/>
  <rect x="{$bedX}" y="520" width="540" height="120" rx="18" fill="#FFFFFF"/>
  <rect x="{$b30}" y="495" width="170" height="70" rx="20" fill="{$p['wall2']}"/>
  <rect x="{$b260}" y="560" width="280" height="80" rx="12" fill="{$p['tile']}" opacity=".8"/>
</g>
<g>
  <rect x="{$deskX}" y="560" width="320" height="18" rx="4" fill="{$p['wood']}"/>
  <rect x="{$d20}" y="578" width="16" height="160" fill="{$p['wood']}"/>
  <rect x="{$d284}" y="578" width="16" height="160" fill="{$p['wood']}"/>
  <rect x="{$d120}" y="470" width="90" height="90" rx="45" fill="{$p['brass']}"/>
  <rect x="{$d160}" y="520" width="10" height="40" fill="{$p['dark']}"/>
</g>
</svg>
SVG;
}

function tileClose(array $p): string
{
    $pat = tilePattern('t', $p, 200);

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 1000" preserveAspectRatio="xMidYMid slice">
<defs>{$pat}</defs>
<rect width="1600" height="1000" fill="url(#t)"/>
<rect width="1600" height="1000" fill="{$p['deep']}" opacity=".08"/>
</svg>
SVG;
}

$n = 1;
foreach ($palettes as $i => $p) {
    file_put_contents("$out/kost-$n.svg", facade($p, $i));
    $n++;
}
foreach ($palettes as $i => $p) {
    file_put_contents("$out/kamar-".($i + 1).'.svg', room($p, $i));
}
foreach ($palettes as $i => $p) {
    file_put_contents("$out/tegel-".($i + 1).'.svg', tileClose($p));
}
echo "ok\n";
