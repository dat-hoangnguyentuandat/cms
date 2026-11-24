<?php

namespace App\Helpers;

class Icon
{
    private static array $icons = [
        'helmet' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 13a8 8 0 1 1 16 0v4a2 2 0 0 1-2 2h-3"/><path d="M4 13h16"/><path d="M7 19h4"/></svg>',
        'phone' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.61 2.62a2 2 0 0 1-.45 2.11L8 9.91a16 16 0 0 0 6.09 6.09l1.46-1.27a2 2 0 0 1 2.11-.45c.84.28 1.72.49 2.62.61A2 2 0 0 1 22 16.92z"/></svg>',
        'chat' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'check' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
        'shield' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'truck' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
        'spark' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 1.8 5.2L19 9l-5.2 1.8L12 16l-1.8-5.2L5 9l5.2-1.8z"/></svg>',
        'support' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0v7a3 3 0 0 0 3 3h1"/><path d="M15 18a3 3 0 0 1-3 3h-1"/><path d="M22 12v3a2 2 0 0 1-2 2h-2V9h2a2 2 0 0 1 2 2z"/><path d="M2 12v3a2 2 0 0 0 2 2h2V9H4a2 2 0 0 0-2 2z"/></svg>',
        'location' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'mail' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/></svg>',
        'clock' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
        'arrow' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
        'send' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>',
        'facebook' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.88 3.77-3.88 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12"/></svg>',
        'up' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>',
        'cart' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
    ];

    public static function get(string $name): string
    {
        return static::$icons[$name] ?? '';
    }
}
