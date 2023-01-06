<?php

namespace App\Domain;

/**
 * Class Settings.
 */
class Settings
{
    /** @var array */
    private array $settings;

    /**
     * Settings constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get settings by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->settings[$key] ?? null;
    }
}
