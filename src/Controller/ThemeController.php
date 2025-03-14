<?php

namespace App\Controller;

use App\Service\ThemeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController extends AbstractController
{
    #[Route('/theme', name: 'app.theme.list', methods: ['GET'])]
    public function listThemes(ThemeService $themeService): Response
    {
        $themes = $themeService->getAvailableThemes();
        $themeList = [];
        
        foreach ($themes as $key => $theme) {
            $themeList[$key] = [
                'name' => $theme['name'],
                'key' => $key,
            ];
        }
        
        return $this->render('theme/list.html.twig', [
            'themes' => $themeList,
        ]);
    }
    
    #[Route('/theme/{themeKey}', name: 'app.theme.preview', methods: ['GET'])]
    public function previewTheme(string $themeKey, ThemeService $themeService): Response
    {
        $theme = $themeService->getTheme($themeKey);
        
        if (!$theme) {
            throw $this->createNotFoundException('Theme not found');
        }
        
        return $this->render('theme/preview.html.twig', [
            'theme' => $theme,
            'themeKey' => $themeKey,
            'cssVariables' => $themeService->getThemeCssVariables($themeKey),
        ]);
    }
    
    #[Route('/api/theme/{themeKey}', name: 'app.theme.get_css', methods: ['GET'])]
    public function getThemeCss(string $themeKey, ThemeService $themeService): Response
    {
        $theme = $themeService->getTheme($themeKey);
        
        if (!$theme) {
            return new JsonResponse(['error' => 'Theme not found'], Response::HTTP_NOT_FOUND);
        }
        
        $css = $themeService->generateThemeInlineCss($themeKey);
        
        return new Response($css, Response::HTTP_OK, [
            'Content-Type' => 'text/css',
        ]);
    }
    
    #[Route('/api/themes', name: 'app.theme.list_api', methods: ['GET'])]
    public function listThemesApi(ThemeService $themeService): JsonResponse
    {
        $themes = $themeService->getAvailableThemes();
        $themeList = [];
        
        foreach ($themes as $key => $theme) {
            $themeList[$key] = [
                'name' => $theme['name'],
                'key' => $key,
            ];
        }
        
        return new JsonResponse($themeList);
    }
} 