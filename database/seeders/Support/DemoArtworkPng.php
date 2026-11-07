<?php

namespace Database\Seeders\Support;

use RuntimeException;

/**
 * Deterministic original abstract artwork images, encoded as standard RGB PNG.
 * Uses only core PHP + zlib; no network calls, GD or Imagick required.
 */
final class DemoArtworkPng
{
    private const WIDTH = 640;
    private const HEIGHT = 800;

    private const PALETTES = [
        [[23, 40, 60], [47, 66, 83], [236, 175, 108], [238, 222, 193], [170, 81, 60]],
        [[208, 218, 209], [133, 168, 160], [41, 92, 103], [236, 211, 173], [27, 54, 69]],
        [[217, 177, 136], [176, 120, 91], [125, 55, 50], [247, 227, 197], [51, 46, 49]],
        [[21, 64, 59], [38, 91, 77], [158, 186, 135], [240, 177, 132], [230, 219, 174]],
        [[32, 40, 92], [72, 66, 117], [107, 180, 186], [234, 207, 110], [209, 121, 149]],
        [[193, 172, 193], [228, 199, 192], [138, 78, 113], [245, 234, 209], [88, 68, 103]],
    ];

    public function render(int $number): string
    {
        if (! function_exists('gzcompress')) {
            throw new RuntimeException('PHP zlib is required to generate demo artwork PNGs.');
        }

        $palette = self::PALETTES[$number % count(self::PALETTES)];
        $motif = $number % 6;
        $raw = '';
        $w = self::WIDTH;
        $h = self::HEIGHT;

        for ($y = 0; $y < $h; $y++) {
            $row = "\x00"; // PNG row filter: None
            $v = $y / $h;

            for ($x = 0; $x < $w; $x++) {
                $u = $x / $w;
                $layer = -1;

                if ($motif === 0) {
                    // Architectural planes and a glowing solar disc.
                    if ($this->ellipse($u, $v, .74, .23, .23, .18)) $layer = 2;
                    if ($u > .17 && $u < .68 && $v > .34 && $v < .93) $layer = 3;
                    if ($u > .29 && $u < .77 && $v > .48 && $v < .93) $layer = 4;
                    if ($u > .34 && $u < .55 && $v > .57 && $v < .93) $layer = 2;
                } elseif ($motif === 1) {
                    // A pale moon above rhythmic tidal bands.
                    if ($this->ellipse($u, $v, .68, .23, .19, .15)) $layer = 3;
                    if ($v > .48 + .06 * sin($u * 7.5)) $layer = 2;
                    if ($v > .66 + .045 * sin($u * 11.0 + 1.7)) $layer = 4;
                    if ($v > .85 + .025 * sin($u * 14.0)) $layer = 2;
                } elseif ($motif === 2) {
                    // Overlapping terracotta arches, inspired by cut-paper collages.
                    if ($u > .16 && $u < .82 && $v > .31 && $v < .91) $layer = 3;
                    if ($this->ellipse($u, $v, .49, .31, .33, .27)) $layer = 2;
                    if ($u > .31 && $u < .67 && $v > .44 && $v < .91) $layer = 4;
                    if ($this->ellipse($u, $v, .49, .43, .18, .17)) $layer = 4;
                    if ($this->ellipse($u, $v, .77, .76, .21, .16)) $layer = 2;
                } elseif ($motif === 3) {
                    // A botanical silhouette with large elliptical leaves.
                    if (abs($u - (.5 + .12 * sin($v * 3.0))) < .008 && $v > .21) $layer = 3;
                    if ($this->ellipse($u, $v, .30, .40, .19, .11)) $layer = 2;
                    if ($this->ellipse($u, $v, .68, .52, .21, .12)) $layer = 2;
                    if ($this->ellipse($u, $v, .33, .70, .22, .13)) $layer = 4;
                    if ($this->ellipse($u, $v, .64, .29, .15, .10)) $layer = 4;
                    if ($v > .88) $layer = 3;
                } elseif ($motif === 4) {
                    // Strong circles, a split color field and fine geometric bars.
                    if ($u > .57) $layer = 4;
                    if ($this->ellipse($u, $v, .51, .44, .34, .27)) $layer = 2;
                    if ($this->ellipse($u, $v, .51, .44, .21, .17)) $layer = 0;
                    if (abs(($u - .19) + .30 * ($v - .50)) < .018) $layer = 3;
                    if ($u > .15 && $u < .84 && $v > .76 && $v < .79) $layer = 3;
                } else {
                    // Soft horizon, concentric swells and a floating object.
                    if ($v > .57 + .055 * sin($u * 9.5)) $layer = 2;
                    if ($v > .72 + .04 * sin($u * 11.0 + 2.0)) $layer = 4;
                    if ($this->ellipse($u, $v, .49, .37, .22, .21)) $layer = 3;
                    if ($this->ellipse($u, $v, .49, .37, .12, .11)) $layer = 4;
                }

                $c = $layer >= 0 ? $palette[$layer] : [
                    (int) round($palette[0][0] * (1 - $v) + $palette[1][0] * $v),
                    (int) round($palette[0][1] * (1 - $v) + $palette[1][1] * $v),
                    (int) round($palette[0][2] * (1 - $v) + $palette[1][2] * $v),
                ];

                // Fine deterministic grain keeps the images from looking flat.
                $grain = (($x * 13 + $y * 29 + $number * 17) % 11) - 5;
                $row .= pack('CCC',
                    max(0, min(255, $c[0] + $grain)),
                    max(0, min(255, $c[1] + $grain)),
                    max(0, min(255, $c[2] + $grain)),
                );
            }
            $raw .= $row;
        }

        return "\x89PNG\r\n\x1a\n"
            .$this->chunk('IHDR', pack('NNCCCCC', $w, $h, 8, 2, 0, 0, 0))
            .$this->chunk('IDAT', gzcompress($raw, 6))
            .$this->chunk('IEND', '');
    }

    private function ellipse(float $x, float $y, float $cx, float $cy, float $rx, float $ry): bool
    {
        return (($x - $cx) / $rx) ** 2 + (($y - $cy) / $ry) ** 2 <= 1.0;
    }

    private function chunk(string $type, string $data): string
    {
        return pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
    }
}
