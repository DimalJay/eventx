<?php

namespace Helpers;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrHelper
{
    /** Quiet-zone padding in modules on each side. */
    private const QUIET_ZONE = 2;

    /** Cell size in px for the rendered table. */
    private const CELL = 4;

    /** Foreground color in hex, without leading #. */
    private const DARK = '18181b';

    /** Background color in hex, without leading #. */
    private const LIGHT = 'ffffff';

    /**
     * Render a QR code for $data as an HTML <table>.
     * Works in every email client without requiring GD or any binary/image step.
     */
    public static function renderTable(string $data): string
    {
        $matrix = (new QRCode(new QROptions(['eccLevel' => EccLevel::L])))
            ->addByteSegment($data)
            ->getQRMatrix();
        $boolMatrix = $matrix->getBooleanMatrix();

        $size = count($boolMatrix);
        $total = $size + (self::QUIET_ZONE * 2);
        $pixels = $total * self::CELL;

        $bg = '#' . self::LIGHT;
        $fg = '#' . self::DARK;

        $rows = [];
        for ($y = 0; $y < $total; $y++) {
            $cells = [];
            for ($x = 0; $x < $total; $x++) {
                $mx = $x - self::QUIET_ZONE;
                $my = $y - self::QUIET_ZONE;
                $dark = ($mx >= 0 && $my >= 0 && $mx < $size && $my < $size && $boolMatrix[$my][$mx]);
                $bgc = $dark ? $fg : $bg;
                $cells[] = '<td width="' . self::CELL . '" height="' . self::CELL . '" '
                    . 'style="width:' . self::CELL . 'px;height:' . self::CELL . 'px;'
                    . 'background-color:' . $bgc . ';font-size:0;line-height:0;padding:0;margin:0;border:0;mso-table-lspace:0pt;mso-table-rspace:0pt;">&nbsp;</td>';
            }
            $rows[] = '<tr>' . implode('', $cells) . '</tr>';
        }

        return '<table align="center" border="0" cellpadding="0" cellspacing="0" '
            . 'role="presentation" style="border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;'
            . 'width:' . $pixels . 'px;">' . implode('', $rows) . '</table>';
    }
}