<?php

declare(strict_types=1);

namespace MsDashboard\Config;

use Dotenv\Dotenv;

final class Config
{
    private static ?self $instance = null;

    /** @var array<string, string> */
    private readonly array $values;

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();

        $this->values = $_ENV;
    }

    public static function load(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function get(string $key, string $default = ''): string
    {
        return $this->values[$key] ?? $_ENV[$key] ?? $default;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default ? 'true' : 'false');

        return in_array(strtolower($value), ['true', '1', 'yes'], true);
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, (string) $default);

        return (int) $value;
    }

    /**
     * @return list<string>
     */
    public function getList(string $key, string $separator = ','): array
    {
        $value = $this->get($key);

        if ($value === '') {
            return [];
        }

        return array_map('trim', explode($separator, $value));
    }

    /**
     * @return list<int>
     */
    public function getIntList(string $key, string $separator = ','): array
    {
        return array_map('intval', $this->getList($key, $separator));
    }

    public function timezone(): string
    {
        return $this->get('APP_TIMEZONE', 'Asia/Jakarta');
    }

    public function debug(): bool
    {
        return $this->getBool('APP_DEBUG');
    }

    public function storagePath(string $sub = ''): string
    {
        $base = dirname(__DIR__, 2) . '/storage';

        return $sub ? $base . '/' . ltrim($sub, '/') : $base;
    }

    public function logPath(): string
    {
        return $this->storagePath('log');
    }

    public function cookiesPath(): string
    {
        return $this->storagePath('cookies.txt');
    }
}
