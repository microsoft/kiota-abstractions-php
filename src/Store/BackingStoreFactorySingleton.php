<?php

namespace Microsoft\Kiota\Abstractions\Store;

/** This class is used to register the backing store factory. */
abstract class BackingStoreFactorySingleton
{
    /**
     * The backing store factory singleton instance.
     * @var BackingStoreFactory|null
     */
    private static ?BackingStoreFactory $instance = null;

    /**
     * One time initialisation of the backing store factory
     *
     * @param BackingStoreFactory $backingStoreFactory
     */
    public static function setInstance(BackingStoreFactory $backingStoreFactory): void {
        self::$instance = self::$instance ?? $backingStoreFactory;
    }

    /**
     * We use the getter method since PHP doesn't support instantiating an instance
     * outside a method.
     * @return BackingStoreFactory
     */
    public static function getInstance(): BackingStoreFactory {
        if (is_null(self::$instance)) {
            self::$instance = new InMemoryBackingStoreFactory();
        }
        return self::$instance;
    }
}
