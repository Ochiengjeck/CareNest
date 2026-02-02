<?php

namespace App\Services;

class ThemeService
{
    public const THEMES = [
        'ocean-blue' => [
            'name' => 'Ocean Blue',
            'primary' => '#2872A1',
            'secondary' => '#CBDDE9',
            'accent_source' => 'primary',
            'light' => ['accent' => 600, 'accent_content' => 700],
            'dark' => ['accent' => 400, 'accent_content' => 300],
            'fg_light' => '#ffffff',
            'fg_dark' => '#ffffff',
        ],
        'soft-sage' => [
            'name' => 'Soft Sage',
            'primary' => '#ACC8A2',
            'secondary' => '#1A2517',
            'accent_source' => 'secondary',
            'light' => ['accent' => 600, 'accent_content' => 700],
            'dark' => ['accent' => 400, 'accent_content' => 300],
            'fg_light' => '#ffffff',
            'fg_dark' => '#1a1a1a',
        ],
        'deep-burgundy' => [
            'name' => 'Deep Burgundy',
            'primary' => '#5B0E14',
            'secondary' => '#F1E194',
            'accent_source' => 'primary',
            'light' => ['accent' => 600, 'accent_content' => 700],
            'dark' => ['accent' => 400, 'accent_content' => 300],
            'fg_light' => '#ffffff',
            'fg_dark' => '#1a1a1a',
        ],
        'vibrant-orange' => [
            'name' => 'Vibrant Orange',
            'primary' => '#FD802E',
            'secondary' => '#233D4C',
            'accent_source' => 'primary',
            'light' => ['accent' => 600, 'accent_content' => 700],
            'dark' => ['accent' => 400, 'accent_content' => 300],
            'fg_light' => '#ffffff',
            'fg_dark' => '#1a1a1a',
        ],
        'blush-peach' => [
            'name' => 'Blush Peach',
            'primary' => '#FFD2C2',
            'secondary' => '#789A99',
            'accent_source' => 'secondary',
            'light' => ['accent' => 600, 'accent_content' => 700],
            'dark' => ['accent' => 400, 'accent_content' => 300],
            'fg_light' => '#ffffff',
            'fg_dark' => '#1a1a1a',
        ],
        'pale-cream' => [
            'name' => 'Pale Cream',
            'primary' => '#FEFACD',
            'secondary' => '#5F4A8B',
            'accent_source' => 'secondary',
            'light' => ['accent' => 600, 'accent_content' => 700],
            'dark' => ['accent' => 400, 'accent_content' => 300],
            'fg_light' => '#ffffff',
            'fg_dark' => '#1a1a1a',
        ],
    ];

    private const SHADE_LIGHTNESS = [
        50 => 0.97,
        100 => 0.93,
        200 => 0.87,
        300 => 0.78,
        400 => 0.66,
        500 => 0.55,
        600 => 0.45,
        700 => 0.37,
        800 => 0.29,
        900 => 0.21,
        950 => 0.14,
    ];

    public function getThemes(): array
    {
        return self::THEMES;
    }

    public function getActiveThemeSlug(): string
    {
        return app(SettingsService::class)->get('active_theme', 'ocean-blue') ?? 'ocean-blue';
    }

    public function renderThemeCss(?string $slug = null): string
    {
        $slug = $slug ?? $this->getActiveThemeSlug();
        $theme = self::THEMES[$slug] ?? null;

        if (! $theme) {
            return '';
        }

        $primaryShades = $this->generateShades($theme['primary']);
        $secondaryShades = $this->generateShades($theme['secondary']);

        $accentSource = $theme['accent_source'] === 'secondary' ? 'secondary' : 'primary';
        $accentShades = $accentSource === 'secondary' ? $secondaryShades : $primaryShades;

        $lightAccent = $theme['light']['accent'];
        $lightContent = $theme['light']['accent_content'];
        $darkAccent = $theme['dark']['accent'];
        $darkContent = $theme['dark']['accent_content'];

        $css = ":root {\n";
        $css .= "    --theme-primary: {$theme['primary']};\n";
        foreach ($primaryShades as $shade => $oklch) {
            $css .= "    --theme-primary-{$shade}: {$oklch};\n";
        }
        $css .= "    --theme-secondary: {$theme['secondary']};\n";
        foreach ($secondaryShades as $shade => $oklch) {
            $css .= "    --theme-secondary-{$shade}: {$oklch};\n";
        }

        $css .= "    --color-accent: {$accentShades[$lightAccent]};\n";
        $css .= "    --color-accent-content: {$accentShades[$lightContent]};\n";
        $css .= "    --color-accent-foreground: {$theme['fg_light']};\n";
        $css .= "    --theme-accent-fg: {$theme['fg_light']};\n";
        $css .= "    --theme-accent-fg-dark: {$theme['fg_dark']};\n";
        $css .= "}\n";

        $css .= ".dark {\n";
        $css .= "    --color-accent: {$accentShades[$darkAccent]};\n";
        $css .= "    --color-accent-content: {$accentShades[$darkContent]};\n";
        $css .= "    --color-accent-foreground: {$theme['fg_dark']};\n";
        $css .= "}\n";

        return $css;
    }

    public function generateShades(string $hex): array
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $oklch = $this->rgbToOklch($r, $g, $b);
        $hue = $oklch[2];
        $chroma = $oklch[1];

        $shades = [];
        foreach (self::SHADE_LIGHTNESS as $shade => $lightness) {
            $c = $chroma;
            // Reduce chroma at extremes to avoid out-of-gamut values
            if ($lightness > 0.9 || $lightness < 0.2) {
                $c = $chroma * 0.6;
            } elseif ($lightness > 0.8 || $lightness < 0.3) {
                $c = $chroma * 0.8;
            }
            $l = round($lightness, 4);
            $c = round($c, 4);
            $h = round($hue, 2);
            $shades[$shade] = "oklch({$l} {$c} {$h})";
        }

        return $shades;
    }

    public function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        ];
    }

    private function rgbToOklch(float $r, float $g, float $b): array
    {
        // sRGB to linear RGB
        $lr = $this->srgbToLinear($r);
        $lg = $this->srgbToLinear($g);
        $lb = $this->srgbToLinear($b);

        // Linear RGB to Oklab (via LMS)
        $l = 0.4122214708 * $lr + 0.5363325363 * $lg + 0.0514459929 * $lb;
        $m = 0.2119034982 * $lr + 0.6806995451 * $lg + 0.1073969566 * $lb;
        $s = 0.0883024619 * $lr + 0.2817188376 * $lg + 0.6299787005 * $lb;

        $l_ = $this->cbrt($l);
        $m_ = $this->cbrt($m);
        $s_ = $this->cbrt($s);

        $L = 0.2104542553 * $l_ + 0.7936177850 * $m_ - 0.0040720468 * $s_;
        $a = 1.9779984951 * $l_ - 2.4285922050 * $m_ + 0.4505937099 * $s_;
        $bVal = 0.0259040371 * $l_ + 0.7827717662 * $m_ - 0.8086757660 * $s_;

        // Oklab to OKLCH
        $C = sqrt($a * $a + $bVal * $bVal);
        $H = atan2($bVal, $a) * (180 / M_PI);
        if ($H < 0) {
            $H += 360;
        }

        return [$L, $C, $H];
    }

    private function srgbToLinear(float $v): float
    {
        return $v <= 0.04045
            ? $v / 12.92
            : pow(($v + 0.055) / 1.055, 2.4);
    }

    private function cbrt(float $v): float
    {
        return $v >= 0 ? pow($v, 1 / 3) : -pow(-$v, 1 / 3);
    }

    public function relativeLuminance(float $r, float $g, float $b): float
    {
        return 0.2126 * $this->srgbToLinear($r)
             + 0.7152 * $this->srgbToLinear($g)
             + 0.0722 * $this->srgbToLinear($b);
    }

    public function contrastForeground(string $hex): string
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $lum = $this->relativeLuminance($r, $g, $b);

        return $lum > 0.179 ? '#1a1a1a' : '#ffffff';
    }
}
