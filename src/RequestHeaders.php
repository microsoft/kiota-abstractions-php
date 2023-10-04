<?php

namespace Microsoft\Kiota\Abstractions;

class RequestHeaders
{
    /** Using array<string, bool> for faster checks since key checks
     * are faster than just looping to check if value exists.
     * @var array<string, array<string,bool>> $headers */
    private array $headers = [];
    /**
     * Get all headers and values stored so far.
     * @return array<string, array<string>>
     */
    public function getAll(): array
    {
        $result = [];
        foreach ($this->headers as $key => $value) {
            $result[$key] = array_keys($value);
        }
        return $result;
    }

    /**
     * Get values for a specific header with a specific key.
     * @param string $key
     * @return array<string>
     */
    public function get(string $key): array
    {
        return array_keys($this->headers[$this->normalize($key)] ?? []);
    }

    /**
     * Add a value to the existing values for that specific header.
     * @param string $key
     * @param string $value
     * @return void
     */
    public function add(string $key, string $value): void
    {
        $lowercaseKey = strtolower($key);
        if (array_key_exists($lowercaseKey, $this->headers)) {
            $this->headers[$lowercaseKey][$value] = true;
        } else {
            $this->headers[$lowercaseKey] = [$value => true];
        }
    }

    /**
     * Try to add a value to the existing values for that specific header if it's not already set.
     * @param string $key
     * @param string $value
     * @return boolean if the value have been added
     */
    public function tryAdd(string $key, string $value): bool
    {
        $lowercaseKey = strtolower($key);
        if (array_key_exists($lowercaseKey, $this->headers)) {
            return false;
        } else {
            $this->headers[$lowercaseKey] = [$value => true];
            return true;
        }
    }

    /**
     * Returns the lowercase version of a string.
     * @param string $key
     * @return string
     */
    private function normalize(string $key): string
    {
        return strtolower(trim($key));
    }

    /**
     * Remove a header with a specific key from the headers list
     * and return the current value from the header.
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        unset($this->headers[$this->normalize($key)]);
    }

    /**
     * Get the names of the current headers set.
     * @return string[]
     */
    public function getHeaderNames(): array
    {
        return array_keys($this->headers);
    }

    /**
     * Counts and returns the count of the current headers.
     * @return int
     */
    public function count(): int
    {
        return count($this->headers);
    }

    /**
     * Clears the current headers.
     * @return void
     */
    public function clear(): void
    {
        $this->headers = [];
    }

    /**
     * Merge all the values to the existing headers.
     * @param array<string, array<string>|string> $headers
     * @return void
     */
    public function putAll(array $headers): void
    {
        foreach ($headers as $key => $headerValue) {
            if (is_array($headerValue)) {
                $this->putAllToKey($key, $headerValue);
            } else {
                $this->add($key, strval($headerValue));
            }
        }
    }

    /**
     * Add all values to a specific header.
     * @param string $key
     * @param string[] $values
     * @return void
     */
    public function putAllToKey(string $key, array $values): void
    {
        foreach ($values as $value) {
            $this->add($key, $value);
        }
    }

    /**
     * Check whether a headers with a given already exists in the headers.
     * @param string $key
     * @return bool
     */
    public function contains(string $key): bool
    {
        return array_key_exists($this->normalize($key), $this->headers);
    }
}
