<?php

namespace Microsoft\Kiota\Abstractions\Store;

use Microsoft\Kiota\Abstractions\Serialization\Parsable;
use Microsoft\Kiota\Abstractions\Serialization\SerializationWriter;
use Microsoft\Kiota\Abstractions\Serialization\SerializationWriterFactory;
use Microsoft\Kiota\Abstractions\Serialization\SerializationWriterProxyFactory;

/**Proxy implementation of SerializationWriterFactory for the backing store that automatically sets the state of the backing store when serializing. */

class BackingStoreSerializationWriterProxyFactory extends SerializationWriterProxyFactory {

    /**
     * Initializes a new instance of the BackingStoreSerializationWriterProxyFactory class given a concrete implementation of SerializationWriterFactory.
     * @param SerializationWriterFactory $concreteSerializationWriterFactory a concrete implementation of SerializationWriterFactory to wrap.
     */
    public function __construct(SerializationWriterFactory $concreteSerializationWriterFactory){
        $onBeforeObjectSerialization = function (Parsable $model) {
            if ($model instanceof BackedModel && $model->getBackingStore()) {
                $model->getBackingStore()->setReturnOnlyChangedValues(true);
            }
        };

        $onAfterObjectSerialization = function (Parsable $model) {
            if ($model instanceof BackedModel && $model->getBackingStore()) {
                $model->getBackingStore()->setReturnOnlyChangedValues(false);
                $model->getBackingStore()->setIsInitializationCompleted(true);
            }
        };

        $onStartObjectSerialization = function (Parsable $model, SerializationWriter $serializationWriter) {
            if ($model instanceof BackedModel && $model->getBackingStore()) {
                    $keys = $model->getBackingStore()->enumerateKeysForValuesChangedToNull();
                    foreach ($keys as $key) {
                        $serializationWriter->writeNullValue($key);
                    }
            }
        };
        parent::__construct($concreteSerializationWriterFactory, $onBeforeObjectSerialization,
            $onAfterObjectSerialization, $onStartObjectSerialization);
    }
}
