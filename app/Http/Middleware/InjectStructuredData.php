<?php

namespace App\Http\Middleware;

use App\Services\StructuredDataGenerator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectStructuredData
{
    public function __construct(
        private StructuredDataGenerator $generator
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->attributes->get('platform_runtime')) {
            return $response;
        }

        // Only inject on HTML responses
        if (! $this->shouldInject($request, $response)) {
            return $response;
        }

        $scriptTag = $this->generator->getPrimaryBusinessScriptTag();

        if (! $scriptTag) {
            return $response;
        }

        $content = $response->getContent();

        // Inject before </head> tag
        $content = preg_replace(
            '/(<\/head>)/i',
            $scriptTag."\n$1",
            $content,
            1
        );

        $response->setContent($content);

        return $response;
    }

    private function shouldInject(Request $request, Response $response): bool
    {
        // Only inject on successful HTML responses
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html')) {
            return false;
        }

        // Skip admin routes
        if ($request->is('admin/*') || $request->is('admin')) {
            return false;
        }

        return true;
    }
}
