<?php

namespace Microsoft\Kiota\Abstractions\Tests\Store;

use Microsoft\Kiota\Abstractions\Store\BackedModel;
use Microsoft\Kiota\Abstractions\Store\BackingStore;
use Microsoft\Kiota\Abstractions\Store\BackingStoreFactorySingleton;
use Microsoft\Kiota\Abstractions\Store\InMemoryBackingStore;
use PHPUnit\Framework\TestCase;

class InMemoryBackingStoreTest extends TestCase
{
    private InMemoryBackingStore $backingStore;

    protected function setUp(): void
    {
        $this->backingStore = new InMemoryBackingStore();
    }

    public function testGetFromBackingStore(): void
    {
        $this->backingStore->set('key', 'value');
        $this->assertEquals('value', $this->backingStore->get('key'));
    }

    public function testReturnOnlyChangedValues(): void
    {
        $this->backingStore->setReturnOnlyChangedValues(true);
        $this->backingStore->setIsInitializationCompleted(false);

        $this->backingStore->set('key', 'value');
        $this->assertNull($this->backingStore->get('key'));
        $this->assertEquals([], $this->backingStore->enumerate());

        $this->backingStore->setReturnOnlyChangedValues(false);
        $this->assertEquals('value', $this->backingStore->get('key'));
        $this->assertEquals(['key' => 'value'], $this->backingStore->enumerate());

        $this->backingStore->setIsInitializationCompleted(true);
        $this->backingStore->setReturnOnlyChangedValues(true);
        $this->backingStore->set('key', 'value2');
        $this->assertEquals('value2', $this->backingStore->get('key'));
        $this->assertEquals(['key' => 'value2'], $this->backingStore->enumerate());
    }

    public function testEnumerateUserInitializedModelReturnsAllValues(): void
    {
        $model = new SampleBackedModel(10, null);

        $this->assertTrue($model->getBackingStore()->getIsInitializationCompleted());
        $this->assertFalse($model->getBackingStore()->getReturnOnlyChangedValues());

        $storeValues = $model->getBackingStore()->enumerate();
        $this->assertEquals(2, sizeof($storeValues));

        $changedToNull = $model->getBackingStore()->enumerateKeysForValuesChangedToNull();
        $this->assertEquals(1, sizeof($changedToNull));
        $this->assertEquals("name", $changedToNull[0]);
    }

    public function testSubscription(): void
    {
        $callbackExecuted = false;
        $callback = function ($key, $oldVal, $newVal) use (&$callbackExecuted) {
            $callbackExecuted = $key === 'key' && $oldVal === 'value' && $newVal === 'value2';
        };
        $this->backingStore->set('key', 'value');
        $this->backingStore->subscribe($callback);
        $this->backingStore->set('key', 'value2');
        $this->assertTrue($callbackExecuted);

    }

    public function testUnsubscribe(): void
    {
        $callbackExecuted = false;
        $callback = function ($key, $oldVal, $newVal) use (&$callbackExecuted) {
            $callbackExecuted = $key === 'key' && $oldVal === 'value' && $newVal === 'value2';
        };
        $this->backingStore->set('key', 'value');
        $subscriptionId = $this->backingStore->subscribe($callback);
        $this->backingStore->set('key', 'value2');
        $this->assertTrue($callbackExecuted);

        $this->backingStore->unsubscribe($subscriptionId);
        $callbackExecuted = false;
        $this->assertFalse($callbackExecuted);
    }

    public function testClearStore(): void
    {
        $this->backingStore->set('key', 'value');
        $this->backingStore->clear();
        $this->assertNull($this->backingStore->get('key'));
        $this->assertEmpty($this->backingStore->enumerate());
    }

    public function testEnumerateKeysForValuesChangedToNull(): void
    {
        $this->backingStore->set('key', 'value');
        $this->assertEquals([], $this->backingStore->enumerateKeysForValuesChangedToNull());

        $this->backingStore->set('key', null);
        $this->assertEquals(['key'], $this->backingStore->enumerateKeysForValuesChangedToNull());
    }

    public function testChangesToCollectionSize(): void
    {
        $this->backingStore->setReturnOnlyChangedValues(true);
        $this->backingStore->setIsInitializationCompleted(false);

        $this->backingStore->set('key', [1, 2, 3]);
        $this->assertNull($this->backingStore->get('key'));

        $this->backingStore->setIsInitializationCompleted(true);
        $this->backingStore->set('key', [1, 2, 3, 4]);
        $this->assertEquals([1, 2, 3, 4], $this->backingStore->get('key'));
    }

    public function testChangesToBackedModelValue(): void
    {
        $model = new SampleBackedModel(10, 'name');

        $this->backingStore->setReturnOnlyChangedValues(true);
        $this->backingStore->setIsInitializationCompleted(false);
        $this->backingStore->set('key', $model);
        $this->assertNull($this->backingStore->get('key'));

        $this->backingStore->setIsInitializationCompleted(true);
        $model->setAge(5);
        $this->assertInstanceOf(SampleBackedModel::class, $this->backingStore->get('key'));
        $this->assertEquals(5, $this->backingStore->get('key')->getAge());
    }

    public function testChangesToBackedModelCollection(): void
    {
        $model = new SampleBackedModel(5, 'name2');
        $collection = [new SampleBackedModel(10, 'name'), $model];

        $this->backingStore->setReturnOnlyChangedValues(true);
        $this->backingStore->setIsInitializationCompleted(false);
        $this->backingStore->set('key', $collection);

        $this->assertNull($this->backingStore->get('key'));

        $this->backingStore->setIsInitializationCompleted(true);
        $model->setAge(250);
        $this->assertIsArray($this->backingStore->get('key'));
        $this->assertEquals(2, sizeof($this->backingStore->get('key')));
        $this->assertEquals(250, $this->backingStore->get('key')[1]->getAge());
    }


    public function testChangesToBackedModelAsBackingStoreValueMakesEntireModelDirty(): void
    {
        $nestedBackedModel = new SampleBackedModel(10, 'name');

        $this->backingStore->set('user', $nestedBackedModel);
        $this->backingStore->setIsInitializationCompleted(true);

        $nestedBackedModel->setAge(5);

        $this->backingStore->setReturnOnlyChangedValues(true);
        $nestedBackedModel->getBackingStore()->setReturnOnlyChangedValues(true);

        $this->assertInstanceOf(BackedModel::class, $this->backingStore->get('user'));
        $this->assertEquals(2, sizeof(array_keys($this->backingStore->get('user')->getBackingStore()->enumerate())));
    }

    public function testChangesToBackedModelCollectionAsBackingStoreValueMakesEntireModelDirty(): void
    {
        $nestedBackedModel = new SampleBackedModel(10, 'name');
        $anotherNestedBackedModel = new SampleBackedModel(100, 'FirstName');

        $this->backingStore->set('user', [$nestedBackedModel, $anotherNestedBackedModel]);
        $this->backingStore->setIsInitializationCompleted(true);

        $nestedBackedModel->setAge(5);

        $this->backingStore->setReturnOnlyChangedValues(true);
        $nestedBackedModel->getBackingStore()->setReturnOnlyChangedValues(true);

        $this->assertIsArray($this->backingStore->get('user'));
        $this->assertEquals(2, sizeof($this->backingStore->get('user')));
        $this->assertEquals(2, sizeof(array_keys($this->backingStore->get('user')[0]->getBackingStore()->enumerate())));
        $this->assertEquals(2, sizeof(array_keys($this->backingStore->get('user')[1]->getBackingStore()->enumerate())));
    }
}

class SampleBackedModel implements BackedModel
{
    private BackingStore $backingStore;

    public function __construct(?int $age = null, ?string $name = null)
    {
        $this->backingStore = BackingStoreFactorySingleton::getInstance()->createBackingStore();
        $this->setAge($age);
        $this->setName($name);
    }

    /**
     * @return int|null
     */
    public function getAge(): ?int
    {
        return $this->backingStore->get("age");
    }

    /**
     * @param int|null  $age
     */
    public function setAge(?int $age): void
    {
        $this->backingStore->set("age", $age);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->backingStore->get("name");
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->backingStore->set("name", $name);
    }

    public function getBackingStore(): ?BackingStore
    {
        return $this->backingStore;
    }
}
