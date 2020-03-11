<?php

namespace App\Domain\Utility;

use InvalidArgumentException;

/**
 * Typed array helper. This is useful for linting
 * where tools check if the code is consistent
 */
final class ArrayReader
{
    /**
     * @var array The data
     */
    private $data;

    /**
     * Constructor.
     *
     * @param array $data Data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get value as integer.
     *
     * @param string $key The key
     * @param int|null $default The default value
     *
     * @throws InvalidArgumentException
     *
     * @return int The value
     */
    public function getInt(string $key, int $default = null): int
    {
        $value = $this->find($key, $default);

        if ($value === null) {
            throw new InvalidArgumentException(sprintf('No value found for key "%s"', $key));
        }

        return (int)$value;
    }

    /**
     * Get value as integer or null.
     *
     * @param string $key The key
     * @param int|null $default The default value
     *
     * @return int|null The value
     */
    public function findInt(string $key, int $default = null)
    {
        $result = $this->find($key, $default);

        if ($result === null) {
            return null;
        }

        return (int)$result;
    }

    /**
     * Get value as string.
     *
     * @param string $key The key
     * @param string|null $default The default value
     *
     * @throws InvalidArgumentException
     *
     * @return string The value
     */
    public function getString(string $key, string $default = null): string
    {
        $value = $this->find($key, $default);

        if ($value === null) {
            throw new InvalidArgumentException(sprintf('No value found for key "%s"', $key));
        }

        return (string)$value;
    }

    /**
     * Get value as string or null.
     *
     * @param string $key The key
     * @param string $default The default value
     *
     * @return string|null The value
     */
    public function findString(string $key, string $default = null): ?string
    {
        $value = $this->find($key, $default);

        if ($value === null) {
            return null;
        }

        return (string)$value;
    }

    /**
     * Get value as array.
     *
     * @param string $key The key
     * @param array|null $default The default value
     *
     * @throws InvalidArgumentException
     *
     * @return array The value
     */
    public function getArray(string $key, array $default = null): array
    {
        $value = $this->find($key, $default);

        if ($value === null) {
            throw new InvalidArgumentException(sprintf('No value found for key "%s"', $key));
        }

        return (array)$value;
    }

    /**
     * Get value as array or null.
     *
     * @param string $key The key
     * @param array $default The default value
     *
     * @return array|null The value
     */
    public function findArray(string $key, array $default = null): ?array
    {
        $value = $this->find($key, $default);

        if ($value === null) {
            return null;
        }

        return (array)$value;
    }

    /**
     * Get value as float.
     *
     * @param string $key The key
     * @param float|null $default The default value
     *
     * @throws InvalidArgumentException
     *
     * @return float The value
     */
    public function getFloat(string $key, float $default = null): float
    {
        $value = $this->find($key, $default);

        if ($value === null) {
            throw new InvalidArgumentException(sprintf('No value found for key "%s"', $key));
        }

        return (float)$value;
    }

    /**
     * Get value as float or null.
     *
     * @param string $key The key
     * @param float $default The default value
     *
     * @return float|null The value
     */
    public function findFloat(string $key, float $default = null): ?float
    {
        $value = $this->find($key, $default);

        if ($value === null) {
            return null;
        }

        return (float)$value;
    }

    /**
     * Get value as boolean.
     *
     * @param string $key The key
     * @param bool|null $default The default value
     *
     * @throws InvalidArgumentException
     *
     * @return bool The value
     */
    public function getBool(string $key, bool $default = null): bool
    {
        $value = $this->find($key, $default);

        if ($value === null) {
            throw new InvalidArgumentException(sprintf('No value found for key "%s"', $key));
        }

        return (bool)$value;
    }

    /**
     * Get value as boolean or null.
     *
     * @param string $key The key
     * @param bool $default The default value
     *
     * @return bool|null The value
     */
    public function findBool(string $key, bool $default = null): ?bool
    {
        $value = $this->find($key, $default);

        if ($value === null) {
            return null;
        }

        return (bool)$value;
    }

    /**
     * Find mixed value.
     *
     * @param string $path The path
     * @param mixed|null $default The default value
     *
     * @return mixed|null The value
     */
    public function find(string $path, $default = null)
    {
        $pathKeys = explode('.', $path);

        $arrayCopyOrValue = $this->data;

        foreach ($pathKeys as $pathKey) {
            if (!isset($arrayCopyOrValue[$pathKey])) {
                return $default;
            }
            $arrayCopyOrValue = $arrayCopyOrValue[$pathKey];
        }

        return $arrayCopyOrValue;
    }

    /**
     * Return all data as array.
     *
     * @return array The data
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Test whether or not a given path exists in $data.
     * This method uses the same path syntax as Hash::extract().
     *
     * Checking for paths that could target more than one element will
     * make sure that at least one matching element exists.
     *
     * @param string $path The path to check for
     *
     * @return bool The existence of path
     */
    public function exists(string $path): bool
    {
        $pathKeys = explode('.', $path);

        $arrayCopyOrValue = $this->data;

        foreach ($pathKeys as $pathKey) {
            if (!array_key_exists($pathKey, $arrayCopyOrValue)) {
                return false;
            }
            $arrayCopyOrValue = $arrayCopyOrValue[$pathKey];
        }

        return true;
    }

    /**
     * Is empty.
     *
     * @param string $path The path
     *
     * @return bool Status
     */
    public function isEmpty(string $path): bool
    {
        return empty($this->find($path));
    }
}
