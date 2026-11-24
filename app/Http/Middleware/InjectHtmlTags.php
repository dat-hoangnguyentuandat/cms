<?php

namespace App\Http\Middleware;

use App\Models\HtmlTag;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectHtmlTags
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->attributes->get('platform_runtime')) {
            return $response;
        }

        // Only process frontend HTML responses
        if ($request->is('admin*') || $request->is('admin/*')) {
            return $response;
        }

        if (! $response instanceof \Illuminate\Http\Response) {
            return $response;
        }

        if (! str_contains($response->headers->get('Content-Type', 'text/html'), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (! $content) {
            return $response;
        }

        $head = HtmlTag::renderPosition('head');
        $bodyStart = HtmlTag::renderPosition('body_start');
        $bodyEnd = HtmlTag::renderPosition('body_end');

        if ($head) {
            $content = str_replace('</head>', $head."\n</head>", $content);
        }

        if ($bodyStart) {
            $content = preg_replace('/<body([^>]*)>/i', '<body$1>'."\n".$bodyStart, $content, 1);
        }

        if ($bodyEnd) {
            $content = str_replace('</body>', $bodyEnd."\n</body>", $content);
        }

        $response->setContent($content);

        return $response;
    }
}
