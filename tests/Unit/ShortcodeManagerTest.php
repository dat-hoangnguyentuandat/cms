<?php

namespace Tests\Unit;

use App\Services\Plugin\ShortcodeManager;
use Tests\TestCase;

class ShortcodeManagerTest extends TestCase
{
    public function test_it_renders_registered_shortcode_with_common_attribute_formats(): void
    {
        $manager = new ShortcodeManager;
        $manager->register('button', function (array $attrs) {
            return ($attrs['text'] ?? '').'|'.($attrs['link'] ?? '').'|'.($attrs['style'] ?? '');
        });

        $this->assertSame(
            'Start Tư vấn|/lien-he|outline End',
            $manager->render('Start [button text="Tư vấn" link=\'/lien-he\' style=outline] End'),
        );
    }

    public function test_it_keeps_unknown_shortcodes_unchanged(): void
    {
        $manager = new ShortcodeManager;

        $this->assertSame('[missing id="1"]', $manager->render('[missing id="1"]'));
    }
}
