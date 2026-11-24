<?php

namespace App\Services;

class HtmlPlacementInjector
{
    public function injectIntoHeaderActions(string $html, string $markup, string $marker): ?string
    {
        if (str_contains($html, 'data-cms-injection="'.$marker.'"')
            || ! preg_match('/<header\b[^>]*>.*?<\/header>/is', $html, $headerMatch, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $header = $headerMatch[0][0];
        $actionsPattern = '/<[^>]+\bclass\s*=\s*(["\'])[^"\']*(?:header-actions|header__actions|site-header__actions|nav-actions)[^"\']*\1[^>]*>/i';
        if (! preg_match($actionsPattern, $header, $actionsMatch, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $insertAt = $actionsMatch[0][1] + strlen($actionsMatch[0][0]);
        $newHeader = substr($header, 0, $insertAt).$markup.substr($header, $insertAt);
        $offset = $headerMatch[0][1];

        return substr($html, 0, $offset).$newHeader.substr($html, $offset + strlen($header));
    }

    public function injectAfterNavigationInHeader(string $html, string $markup, string $marker): ?string
    {
        if (str_contains($html, 'data-cms-injection="'.$marker.'"')
            || ! preg_match('/<header\b[^>]*>.*?<\/header>/is', $html, $headerMatch, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $header = $headerMatch[0][0];
        $navEnd = strripos($header, '</nav>');
        if ($navEnd === false) {
            return null;
        }

        $navEnd += strlen('</nav>');
        $newHeader = substr($header, 0, $navEnd).$markup.substr($header, $navEnd);
        $offset = $headerMatch[0][1];

        return substr($html, 0, $offset).$newHeader.substr($html, $offset + strlen($header));
    }

    public function injectBeforeMarkerInHeader(string $html, string $markup, string $marker): ?string
    {
        if (! preg_match('/<header\b[^>]*>.*?<\/header>/is', $html, $headerMatch, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $header = $headerMatch[0][0];
        $pattern = '/(?=<(?:div|nav)\b[^>]*data-cms-injection=(["\'])'.preg_quote($marker, '/').'\1)/i';
        $newHeader = preg_replace($pattern, $markup, $header, 1, $count);
        if ($newHeader === null || $count === 0) {
            return null;
        }

        $offset = $headerMatch[0][1];

        return substr($html, 0, $offset).$newHeader.substr($html, $offset + strlen($header));
    }

    public function inject(
        string $html,
        string $markup,
        string $position,
        string $marker,
        ?string $fallbackMarkup = null,
    ): string {
        if ($html === '' || str_contains($html, 'data-cms-injection="'.$marker.'"')) {
            return $html;
        }

        if ($position === 'header') {
            $injected = $this->insideSemanticElement($html, 'header', $markup, true);
            if ($injected !== null) {
                return $injected;
            }

            $injected = $this->afterOpeningLandmark($html, 'banner', $markup);
            if ($injected !== null) {
                return $injected;
            }
        } else {
            $injected = $this->insideSemanticElement($html, 'footer', $markup, false);
            if ($injected !== null) {
                return $injected;
            }

            $injected = $this->afterOpeningLandmark($html, 'contentinfo', $markup);
            if ($injected !== null) {
                return $injected;
            }
        }

        $fallbackMarkup ??= $markup;

        if (preg_match('/<body\b[^>]*>/i', $html, $match, PREG_OFFSET_CAPTURE)) {
            $offset = $match[0][1] + strlen($match[0][0]);

            return substr($html, 0, $offset).$fallbackMarkup.substr($html, $offset);
        }

        return $html.$fallbackMarkup;
    }

    private function insideSemanticElement(string $html, string $tag, string $markup, bool $preferNav): ?string
    {
        if (! preg_match('/<'.$tag.'\b[^>]*>.*?<\/'.$tag.'>/is', $html, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $element = $match[0][0];
        $replacement = null;

        if ($preferNav && preg_match('/<nav\b[^>]*>.*?<\/nav>/is', $element, $navMatch, PREG_OFFSET_CAPTURE)) {
            $nav = $navMatch[0][0];
            $newNav = preg_replace('/<\/nav>/i', $markup.'</nav>', $nav, 1);
            if ($newNav !== null) {
                $navOffset = $navMatch[0][1];
                $replacement = substr($element, 0, $navOffset).$newNav.substr($element, $navOffset + strlen($nav));
            }
        }

        $replacement ??= preg_replace('/<\/'.$tag.'>/i', $markup.'</'.$tag.'>', $element, 1);
        if ($replacement === null) {
            return null;
        }

        $offset = $match[0][1];

        return substr($html, 0, $offset).$replacement.substr($html, $offset + strlen($element));
    }

    private function afterOpeningLandmark(string $html, string $role, string $markup): ?string
    {
        if (! preg_match('/<[^>]+\brole\s*=\s*(["\'])'.$role.'\1[^>]*>/i', $html, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $offset = $match[0][1] + strlen($match[0][0]);

        return substr($html, 0, $offset).$markup.substr($html, $offset);
    }
}
