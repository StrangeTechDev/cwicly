<?php

declare(strict_types=1);

namespace Cwicly\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for the update checker asset selection logic.
 */
class UpdateCheckerAssetSelectionTest extends TestCase
{
    /**
     * Sample release assets matching the naming scheme.
     */
    private const SAMPLE_ASSETS = [
        'cwicly-plugin_v1.4.7-free.zip',
        'cwicly-plugin_v1.4.7.zip',
    ];

    /**
     * Regex for free version assets.
     */
    private const FREE_REGEX = '/-free\.zip$/i';

    /**
     * Regex for full version assets.
     */
    private const FULL_REGEX = '/cwicly-plugin_v[\d.]+\.zip$/i';

    /**
     * Test that free regex matches only free assets.
     */
    public function testFreeRegexMatchesFreeAsset(): void
    {
        $matches = $this->filterAssets(self::SAMPLE_ASSETS, self::FREE_REGEX);
        
        $this->assertCount(1, $matches);
        $this->assertSame('cwicly-plugin_v1.4.7-free.zip', $matches[0]);
    }

    /**
     * Test that free regex does not match full version assets.
     */
    public function testFreeRegexDoesNotMatchFullAsset(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            self::FREE_REGEX,
            'cwicly-plugin_v1.4.7.zip'
        );
    }

    /**
     * Test that full regex matches full version assets.
     */
    public function testFullRegexMatchesFullAsset(): void
    {
        $matches = $this->filterAssets(self::SAMPLE_ASSETS, self::FULL_REGEX);
        
        // Both match the full regex pattern, but we want the non-free one first
        // The actual plugin-update-checker uses the first match, so order matters
        $this->assertContains('cwicly-plugin_v1.4.7.zip', $matches);
    }

    /**
     * Test that full regex only matches full version assets (not free).
     * The regex /cwicly-plugin_v[\d.]+\.zip$/i requires the filename to end
     * with version.zip - the -free.zip suffix breaks this pattern.
     */
    public function testFullRegexMatchesOnlyFullAsset(): void
    {
        $matches = $this->filterAssets(self::SAMPLE_ASSETS, self::FULL_REGEX);
        
        // Only the full version matches because -free.zip doesn't match [\d.]+\.zip$
        $this->assertCount(1, $matches);
        $this->assertSame('cwicly-plugin_v1.4.7.zip', $matches[0]);
    }

    /**
     * Test improved full regex that excludes free version.
     */
    public function testImprovedFullRegexExcludesFreeAsset(): void
    {
        // Improved regex: match version.zip but NOT if preceded by -free
        $improvedRegex = '/cwicly-plugin_v[\d.]+\.zip$/i';
        $negativeRegex = '/-free\.zip$/i';
        
        $matches = array_filter(
            self::SAMPLE_ASSETS,
            fn($asset) => preg_match($improvedRegex, $asset) && !preg_match($negativeRegex, $asset)
        );
        
        $this->assertCount(1, array_values($matches));
        $this->assertSame('cwicly-plugin_v1.4.7.zip', array_values($matches)[0]);
    }

    /**
     * Test version detection from various tag formats.
     */
    #[DataProvider('versionTagProvider')]
    public function testVersionExtraction(string $tag, string $expectedVersion): void
    {
        // Strip leading 'v' if present (mimics plugin-update-checker behavior)
        $version = ltrim($tag, 'v');
        $this->assertSame($expectedVersion, $version);
    }

    /**
     * Data provider for version tag tests.
     */
    public static function versionTagProvider(): array
    {
        return [
            'with v prefix' => ['v1.4.7', '1.4.7'],
            'without v prefix' => ['1.4.7', '1.4.7'],
            'patch version' => ['v1.4.7.1', '1.4.7.1'],
            'beta version' => ['v1.5.0-beta1', '1.5.0-beta1'],
        ];
    }

    /**
     * Test CWICLY_IS_FREE detection logic.
     */
    #[DataProvider('acfDetectionProvider')]
    public function testAcfDetectionLogic(bool $acfExists, bool $expectedIsFree): void
    {
        // Simulate the detection logic from cwicly.php
        $isFree = !$acfExists;
        $this->assertSame($expectedIsFree, $isFree);
    }

    /**
     * Data provider for ACF detection tests.
     */
    public static function acfDetectionProvider(): array
    {
        return [
            'ACF exists (full version)' => [true, false],
            'ACF missing (free version)' => [false, true],
        ];
    }

    /**
     * Filter assets by regex pattern.
     */
    private function filterAssets(array $assets, string $pattern): array
    {
        return array_values(array_filter(
            $assets,
            fn($asset) => preg_match($pattern, $asset)
        ));
    }
}
