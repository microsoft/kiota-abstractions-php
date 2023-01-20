<?php

namespace Microsoft\Kiota\Abstractions;

class RequestHeaders implements \Countable
{
    /** Using array<string, bool> for faster checks since key checks
     * are faster than just looping to check if value exists.
     * @var array<string, array<string,bool>> $headers */
    private array $headers = [];
    /**
     * Get all headers and values stored so far.
     * @return array<string, array<string>>
     */
    public function values(): array {
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
    public function get(string $key): array {
        return array_keys($this->headers[strtolower($key)] ?? []);
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
     * Returns the lowercase version of a string.
     * @param string $key
     * @return string
     */
    private function normalize(string $key): string {
        return strtolower($key);
    }

    /**
     * Remove a header with a specific key from the headers list
     * and return the current value from the header.
     * @param string $key
     * @return void
     */
    public function remove(string $key): void {
        unset($this->headers[$this->normalize($key)]);
    }

    /**
     * Get the names of the current headers set.
     * @return string[]
     */
    public function getHeaderNames(): array {
        return array_keys($this->headers);
    }

    /**
     * Gets all the headers.
     * @return array<string,string[]>
     */
    public function getAll(): array {
        return $this->values();
    }

    /**
     * Counts and returns the count of the current headers.
     * @return int
     */
    public function count(): int {
        return count($this->headers);
    }

    /**
     * Clears the current headers.
     * @return void
     */
    public function clear(): void {
        $this->headers = [];
    }

    /**
     * Add all the values to the specific header.
     * @param string $key
     * @param array<string> $values
     * @return void
     */
    public function addAll(string $key, array $values): void {
        foreach ($values as $value) {
            $this->add($key, $value);
        }
    }

    /**
     * Check whether a headers with a given already exists in the headers.
     * @param string $key
     * @return bool
     */
    public function contains(string $key): bool {
        return array_key_exists($this->normalize($key), $this->headers);
    }

    /**
     * Create a new RequestHeaders object from array of headers.
     * @param array<string,array<string>> $values
     * @return self
     */
    public static function from(array $values): self {
        $headers = new self;
        foreach ($values as $key => $value) {
            $headers->addAll($key, $value);
        }
        return $headers;
    }
}
