<?php

namespace Tests\Unit;

use App\Tenancy\DomainNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DomainNormalizerTest extends TestCase
{
    #[DataProvider('domains')]
    public function test_it_normalizes_domains(string $input, string $expected): void
    {
        $this->assertSame($expected, DomainNormalizer::normalize($input));
    }

    public static function domains(): array
    {
        return [
            ['Example.COM', 'example.com'],
            ['https://www.example.com/path', 'www.example.com'],
            ['tenant.test:8080', 'tenant.test'],
            ['tenant.test.', 'tenant.test'],
        ];
    }

    public function test_it_rejects_invalid_domains(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DomainNormalizer::normalize('not a domain');
    }
}
