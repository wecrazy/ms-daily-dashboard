<?php

declare(strict_types=1);

namespace MsDashboard\Config;

/**
 * Partner configuration — defines the list of partners and their rotation order.
 */
final class Partners
{
    /**
     * All registered partner slugs.
     * Slug is the URL-safe name (e.g., "ARTAJASA", "CIMB_NIAGA").
     * Display name is derived by replacing underscores with spaces.
     *
     * @var list<string>
     */
    private const ALL = [
        'ARTAJASA',
        'CIMB_NIAGA',
        'DANA',
        'MANDIRI',
        'MTI',
        'NDP',
        'OVO',
    ];

    /**
     * Default rotation order for the auto-slideshow.
     * CIMB_NIAGA is excluded from auto-rotation.
     *
     * @var list<string>
     */
    private const DEFAULT_ROTATION = [
        'ARTAJASA',
        'DANA',
        'MANDIRI',
        'MTI',
        'NDP',
        'OVO',
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return self::ALL;
    }

    public static function isValid(string $slug): bool
    {
        return in_array(strtoupper($slug), self::ALL, true);
    }

    public static function displayName(string $slug): string
    {
        return str_replace('_', ' ', $slug);
    }

    /**
     * Get the rotation order (from env or default).
     *
     * @return list<string>
     */
    public static function rotation(?Config $config = null): array
    {
        if ($config !== null) {
            $envRotation = $config->getList('PARTNER_ROTATION');
            if (!empty($envRotation)) {
                return $envRotation;
            }
        }

        return self::DEFAULT_ROTATION;
    }

    /**
     * Get the next partner in rotation after the given slug.
     */
    public static function nextInRotation(string $currentSlug, ?Config $config = null): string
    {
        $rotation = self::rotation($config);
        $index = array_search($currentSlug, $rotation, true);

        if ($index === false) {
            return $rotation[0];
        }

        $nextIndex = ($index + 1) % count($rotation);

        return $rotation[$nextIndex];
    }
}
