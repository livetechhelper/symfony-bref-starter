<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Service for managing theme configurations
 */
class ThemeService
{
    private ParameterBagInterface $parameterBag;
    
    /**
     * Default themes available in the application
     */
    private array $availableThemes = [
        'default' => [
            'name' => 'Default',
            'primary' => [
                50 => '#f0f9ff',
                100 => '#e0f2fe',
                200 => '#bae6fd',
                300 => '#7dd3fc',
                400 => '#38bdf8',
                500 => '#0ea5e9',
                600 => '#0284c7',
                700 => '#0369a1',
                800 => '#075985',
                900 => '#0c4a6e',
                950 => '#082f49',
            ],
            'secondary' => [
                50 => '#f5f3ff',
                100 => '#ede9fe',
                200 => '#ddd6fe',
                300 => '#c4b5fd',
                400 => '#a78bfa',
                500 => '#8b5cf6',
                600 => '#7c3aed',
                700 => '#6d28d9',
                800 => '#5b21b6',
                900 => '#4c1d95',
                950 => '#2e1065',
            ],
        ],
        'emerald' => [
            'name' => 'Emerald',
            'primary' => [
                50 => '#ecfdf5',
                100 => '#d1fae5',
                200 => '#a7f3d0',
                300 => '#6ee7b7',
                400 => '#34d399',
                500 => '#10b981',
                600 => '#059669',
                700 => '#047857',
                800 => '#065f46',
                900 => '#064e3b',
                950 => '#022c22',
            ],
            'secondary' => [
                50 => '#fff7ed',
                100 => '#ffedd5',
                200 => '#fed7aa',
                300 => '#fdba74',
                400 => '#fb923c',
                500 => '#f97316',
                600 => '#ea580c',
                700 => '#c2410c',
                800 => '#9a3412',
                900 => '#7c2d12',
                950 => '#431407',
            ],
        ],
        'rose' => [
            'name' => 'Rose',
            'primary' => [
                50 => '#fff1f2',
                100 => '#ffe4e6',
                200 => '#fecdd3',
                300 => '#fda4af',
                400 => '#fb7185',
                500 => '#f43f5e',
                600 => '#e11d48',
                700 => '#be123c',
                800 => '#9f1239',
                900 => '#881337',
                950 => '#4c0519',
            ],
            'secondary' => [
                50 => '#f8fafc',
                100 => '#f1f5f9',
                200 => '#e2e8f0',
                300 => '#cbd5e1',
                400 => '#94a3b8',
                500 => '#64748b',
                600 => '#475569',
                700 => '#334155',
                800 => '#1e293b',
                900 => '#0f172a',
                950 => '#020617',
            ],
        ],
    ];

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * Get all available themes
     */
    public function getAvailableThemes(): array
    {
        return $this->availableThemes;
    }

    /**
     * Get a specific theme by key
     */
    public function getTheme(string $themeKey): ?array
    {
        return $this->availableThemes[$themeKey] ?? null;
    }

    /**
     * Get CSS variables for a specific theme
     */
    public function getThemeCssVariables(string $themeKey): array
    {
        $theme = $this->getTheme($themeKey);
        if (!$theme) {
            return [];
        }

        $variables = [];
        foreach (['primary', 'secondary'] as $colorType) {
            if (isset($theme[$colorType])) {
                foreach ($theme[$colorType] as $shade => $color) {
                    $variables["--color-{$colorType}-{$shade}"] = $color;
                }
            }
        }

        return $variables;
    }

    /**
     * Generate inline CSS for theme variables
     */
    public function generateThemeInlineCss(string $themeKey): string
    {
        $variables = $this->getThemeCssVariables($themeKey);
        if (empty($variables)) {
            return '';
        }

        $css = ':root {';
        foreach ($variables as $name => $value) {
            $css .= "\n  {$name}: {$value};";
        }
        $css .= "\n}";

        return $css;
    }
} 