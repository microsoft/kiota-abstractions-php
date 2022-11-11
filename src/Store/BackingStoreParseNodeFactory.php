<?php

namespace Microsoft\Kiota\Abstractions\Store;


use Microsoft\Kiota\Abstractions\Serialization\ParseNodeFactory;
use Microsoft\Kiota\Abstractions\Serialization\ParseNodeProxyFactory;

/** Proxy implementation of ParseNodeFactory for the backing store that automatically sets the state of the backing store when deserializing. */
class BackingStoreParseNodeFactory extends ParseNodeProxyFactory{

    /**
     * Initializes a new instance of the BackingStoreParseNodeFactory class given the concrete implementation.
     * @param ParseNodeFactory $concrete the concrete implementation of the ParseNodeFactory
     */
    public function __construct(ParseNodeFactory $concrete) {
        parent::__construct($concrete,
           static function ($x) {
                if ($x instanceof BackedModel && $x->getBackingStore()) {
                    $x->getBackingStore()->setIsInitializationCompleted(false);
                }
           },
           static function ($x) {
                if ($x instanceof BackedModel && $x->getBackingStore()) {
                    $x->getBackingStore()->setIsInitializationCompleted(true);
                }
           }
        );
    }

}
