<?php

namespace Microsoft\Kiota\Abstractions\Store;

use Ramsey\Uuid\Uuid;

class InMemoryBackingStore implements BackingStore
{

    private bool $isInitializationCompleted = true;
    private bool $returnOnlyChangedValues = false;

    /**
     * @var array<string, array<mixed>> $store
     */
    private array $store = [];

    /** @var array<string, callable> $subscriptionStore */
    private array $subscriptionStore = [];

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key) {
        $this->checkCollectionSizeChanged($key);
        $wrapper =  $this->store[$key] ?? null;
        if (is_null($wrapper)) {
            return null;
        }
        return $this->getValueFromWrapper($wrapper);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $oldValue = $this->store[$key] ?? null;
        $valueToAdd = is_array($value) ?
            [$this->isInitializationCompleted, $value, count($value)] : [$this->isInitializationCompleted, $value];

        // Dirty track changes if $value is a model and its properties change
        if (!array_key_exists($key, $this->store)) {
            if ($value instanceof BackedModel && $value->getBackingStore()) {
                $value->getBackingStore()->subscribe(function ($propertyKey, $oldVal, $newVal) use ($key, $value) {
                    // Mark all properties as dirty
                    $value->getBackingStore()->setIsInitializationCompleted(false);
                    $this->set($key, $value);
                });
            }
            if (is_array($value)) {
                array_map(function ($item) use ($key, $value) {
                    if ($item instanceof BackedModel && $item->getBackingStore()) {
                        $item->getBackingStore()->subscribe
                        (function ($propertyKey, $oldVal, $newVal) use ($key, $value, $item) {
                            // Mark all properties as dirty
                            $item->getBackingStore()->setIsInitializationCompleted(false);
                            $this->set($key, $value);
                        });
                    }
                }, $value);
            }
        }

        $this->store[$key] = $valueToAdd;
        foreach ($this->subscriptionStore as $callback) {
            $callback($key, $oldValue[1] ?? null, $value);
        }
    }

    /**
     * Enumerate the values in the store based on $returnOnlyChangedValues
     *
     * @return array<string, mixed> key value pairs
     */
    public function enumerate(): array {
        $result = [];

        foreach ($this->store as $key => $value) {
            $this->checkCollectionSizeChanged($key);
            if (!$this->returnOnlyChangedValues || $value[0]) {
                $result[$key] = $value[1];
            }
        }
        return $result;
    }

    /**
     * Adds a callback to subscribe to events in the store
     *
     * @param callable $callback
     * @param string|null $subscriptionId
     * @return string
     */
    public function subscribe(callable $callback, ?string $subscriptionId = null): string {
        if ($subscriptionId === null) {
            $subscriptionId = Uuid::uuid4()->toString();
        }
        $this->subscriptionStore[$subscriptionId] = $callback;
        return $subscriptionId;
    }

    /**
     * De-register the callback with the given subscriptionId
     *
     * @param string $subscriptionId
     */
    public function unsubscribe(string $subscriptionId): void {
        unset($this->subscriptionStore[$subscriptionId]);
    }

    /**
     * Empties the store
     */
    public function clear(): void {
        $this->store = [];
    }

    /**
     * @param bool $value
     */
    public function setIsInitializationCompleted(bool $value): void {
        $this->isInitializationCompleted = $value;
        // Update existing values in store
        foreach ($this->store as $key => $storedValue) {
            $storedValue[0] = !$storedValue[0];
            if ($storedValue[1] instanceof BackedModel && $storedValue[1]->getBackingStore()) {
                $storedValue[1]->getBackingStore()->setIsInitializationCompleted($value);
            }
            if (is_array($storedValue[1])) {
                array_map(function ($item) use ($value) {
                    if ($item instanceof BackedModel && $item->getBackingStore()) {
                        $item->getBackingStore()->setIsInitializationCompleted($value);
                    }
                }, $storedValue[1]);
            }
        }
    }

    /**
     * @return bool
     */
    public function getIsInitializationCompleted(): bool {
        return $this->isInitializationCompleted;
    }

    /**
     *
     */
    public function setReturnOnlyChangedValues(bool $value): void {
        $this->returnOnlyChangedValues = $value;
    }

    /**
     * @return bool
     */
    public function getReturnOnlyChangedValues(): bool {
        return $this->returnOnlyChangedValues;
    }

    /**
     * Returns a list of keys in the store that have changed to null
     *
     * @return iterable<string>
     */
    public function enumerateKeysForValuesChangedToNull(): iterable {
        $result = [];

        foreach ($this->store as $key => $val) {
            if ($val[1] === null && $val[0]) {
                $result []= $key;
            }
        }
        return $result;
    }

    /**
     * Returns value from $wrapper based on $returnOnlyChangedValues configuration
     *
     * @param array<mixed> $wrapper
     * @return mixed
     */
    private function getValueFromWrapper(array $wrapper) {
        $hasChangedValue = $wrapper[0];
        if (!$this->returnOnlyChangedValues || $hasChangedValue) {
            return $wrapper[1];
        }
        return null;
    }

    /**
     * Checks if collection of values has changed in size. If so, dirty tracks the change by calling set()
     *
     * @param string $key
     * @return void
     */
    private function checkCollectionSizeChanged(string $key): void {
        $wrapper = $this->store[$key] ?? null;
        if ($wrapper) {
            if (is_array($wrapper[1])) {
                array_map(function ($item) {
                    if ($item instanceof BackedModel && $item->getBackingStore()) {
                        // Call get() on nested properties so that this method may be called recursively
                        // to ensure collections are consistent
                        array_map(
                            fn ($itemKey) => $item->getBackingStore()->get($itemKey),
                            array_keys($item->getBackingStore()->enumerate())
                        );
                    }
                }, $wrapper[1]);

                if (sizeof($wrapper[1]) != $wrapper[2]) {
                    $this->set($key, $wrapper[1]);
                }
            }
            if ($wrapper[1] instanceof BackedModel && $wrapper[1]->getBackingStore()) {
                // Call get() on nested properties so that this method may be called recursively
                // to ensure collections are consistent
                array_map(
                    fn ($itemKey) => $wrapper[1]->getBackingStore()->get($itemKey),
                    array_keys($wrapper[1]->getBackingStore()->enumerate())
                );
            }
        }
    }
}
