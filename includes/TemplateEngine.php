<?php
/**
 * EasyResume - Template Engine
 * Loads and renders resume templates with shared data
 */

class TemplateEngine {
    private $templatesDir;
    private $cacheDir;
    private $availableTemplates = [];
    
    public function __construct($templatesDir = null) {
        $this->templatesDir = $templatesDir ?? __DIR__ . '/../templates/';
        $this->cacheDir = sys_get_temp_dir() . '/easyresume_templates/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        $this->scanTemplates();
    }
    
    private function scanTemplates() {
        $dirs = glob($this->templatesDir . '*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $name = basename($dir);
            $indexFile = $dir . '/index.php';
            $styleFile = $dir . '/style.css';
            $previewFile = $dir . '/preview.php';
            $metaFile = $dir . '/meta.json';
            
            if (file_exists($indexFile)) {
                $meta = [
                    'id' => $name,
                    'name' => $this->formatName($name),
                    'description' => '',
                    'category' => 'general',
                    'thumbnail' => '',
                    'has_preview' => file_exists($previewFile)
                ];
                
                if (file_exists($metaFile)) {
                    $meta = array_merge($meta, json_decode(file_get_contents($metaFile), true));
                }
                
                $this->availableTemplates[$name] = [
                    'meta' => $meta,
                    'index' => $indexFile,
                    'style' => $styleFile,
                    'preview' => $previewFile,
                    'dir' => $dir
                ];
            }
        }
    }
    
    private function formatName($slug) {
        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }
    
    public function getAvailableTemplates() {
        return array_map(function($t) { return $t['meta']; }, $this->availableTemplates);
    }
    
    public function getTemplate($id) {
        return $this->availableTemplates[$id] ?? null;
    }
    
    public function render(ResumeData $resume, $templateId = null, $options = []) {
        $templateId = $templateId ?? $resume->template;
        $template = $this->getTemplate($templateId);
        
        if (!$template) {
            throw new Exception("Template '$templateId' not found");
        }
        
        // Prepare CSS variables from theme
        $cssVars = $this->generateCssVariables($resume->theme);
        
        // Make resume data available to template
        $resumeData = $resume->toArray();
        
        // Capture template output
        ob_start();
        include $template['index'];
        $html = ob_get_clean();
        
        // Inject CSS variables into HTML
        $html = $this->injectCssVariables($html, $cssVars);
        
        // Add template stylesheet
        if (file_exists($template['style']) && !isset($options['no_styles'])) {
            $css = file_get_contents($template['style']);
            $css = $this->injectCssVariables($css, $cssVars);
            $html = $this->injectStyles($html, $css);
        }
        
        return $html;
    }
    
    public function renderPreview(ResumeData $resume, $templateId = null) {
        $templateId = $templateId ?? $resume->template;
        $template = $this->getTemplate($templateId);
        
        if (!$template || !$template['preview']) {
            // Fallback to mini render
            return $this->renderMiniPreview($resume, $templateId);
        }
        
        $cssVars = $this->generateCssVariables($resume->theme);
        
        ob_start();
        include $template['preview'];
        $html = ob_get_clean();
        
        $html = $this->injectCssVariables($html, $cssVars);
        
        if (file_exists($template['style'])) {
            $css = file_get_contents($template['style']);
            $css = $this->injectCssVariables($css, $cssVars);
            $html = $this->injectStyles($html, $css);
        }
        
        return $html;
    }
    
    private function renderMiniPreview(ResumeData $resume, $templateId) {
        // Generate a simple SVG preview based on template type
        $meta = $this->availableTemplates[$templateId]['meta'] ?? ['name' => $templateId];
        
        return '<div class="template-mini-preview" data-template="' . htmlspecialchars($templateId) . '">
            <div class="mini-preview-header">' . htmlspecialchars($meta['name']) . '</div>
            <div class="mini-preview-canvas"></div>
        </div>';
    }
    
    private function generateCssVariables($theme) {
        $vars = [];
        foreach ($theme as $key => $value) {
            $vars['--' . str_replace('_', '-', $key)] = $value;
        }
        return $vars;
    }
    
    private function injectCssVariables($content, $variables) {
        $cssVars = '';
        foreach ($variables as $name => $value) {
            $cssVars .= "$name: $value; ";
        }
        
        // Replace :root { ... } or inject before </style> or in style attribute
        if (preg_match('/:root\s*\{[^}]*\}/', $content)) {
            return preg_replace(
                '/:root\s*\{[^}]*\}/',
                ":root { $cssVars }",
                $content,
                1
            );
        }
        
        // Inject into first <style> tag
        return preg_replace(
            '/<style([^>]*)>/i',
            "<style$1>:root { $cssVars }",
            $content,
            1
        );
    }
    
    private function injectStyles($html, $css) {
        $styleTag = "<style data-template-style>$css</style>";
        
        if (preg_match('/<\/head>/i', $html)) {
            return preg_replace('/<\/head>/i', "$styleTag\n</head>", $html, 1);
        }
        
        return $styleTag . $html;
    }
    
    public function generateThumbnail($templateId, $width = 300, $height = 400) {
        // This would use a headless browser (puppeteer, chrome-headless) in production
        // For now, return an SVG placeholder
        $template = $this->getTemplate($templateId);
        if (!$template) return '';
        
        $meta = $template['meta'];
        $colors = ['primary' => '#6c63ff', 'secondary' => '#3a1c8e'];
        
        return '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="#f9f9fb"/>
            <rect x="10" y="10" width="30" height="30" rx="4" fill="' . $colors['primary'] . '"/>
            <text x="50" y="32" font-family="system-ui" font-size="14" font-weight="600" fill="#1c1a29">' . htmlspecialchars($meta['name']) . '</text>
            <rect x="10" y="50" width="80%" height="4" rx="2" fill="#e5e7eb"/>
            <rect x="10" y="60" width="60%" height="3" rx="1.5" fill="#9ca3af"/>
            <rect x="10" y="68" width="70%" height="3" rx="1.5" fill="#9ca3af"/>
            <rect x="10" y="80" width="80%" height="30" rx="4" fill="#e5e7eb"/>
            <rect x="10" y="120" width="40%" height="3" rx="1.5" fill="' . $colors['primary'] . '"/>
            <rect x="10" y="130" width="90%" height="3" rx="1.5" fill="#9ca3af"/>
            <rect x="10" y="138" width="70%" height="3" rx="1.5" fill="#9ca3af"/>
            <rect x="10" y="150" width="80%" height="40" rx="4" fill="#e5e7eb"/>
        </svg>';
    }
}