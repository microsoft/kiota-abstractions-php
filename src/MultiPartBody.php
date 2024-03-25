<?php

namespace Microsoft\Kiota\Abstractions;

use Exception;
use InvalidArgumentException;
use Microsoft\Kiota\Abstractions\Serialization\Parsable;
use Microsoft\Kiota\Abstractions\Serialization\SerializationWriter;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class MultiPartBody implements Parsable
{

    /**
     * @var array<string, array{string, mixed}>
     */
    private array $parts = [];

    private string $boundary;

    private ?RequestAdapter $requestAdapter = null;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->boundary = random_bytes(32);
    }

    /**
     * @param RequestAdapter $requestAdapter
     */
    public function setRequestAdapter(RequestAdapter $requestAdapter): void
    {
        $this->requestAdapter = $requestAdapter;
    }

    /**
     * @inheritDoc
     */
    public function getFieldDeserializers(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function serialize(SerializationWriter $writer): void
    {
        $first = count($this->parts) > 0;

        foreach ($this->parts as $key => $value) {
            if (!$first) {
                $this->addNewLine($writer);
            }
            $partName = $key;
            $partValue = $value[1];
            $contentType = $value[0];
            $writer->writeStringValue("", "--$this->boundary");
            $writer->writeStringValue("Content-Type", $contentType);
            $writer->writeStringValue("Content-Disposition", "form-data; name=\"$partName\"");
            $this->addNewLine($writer);
            if ($partValue instanceof Parsable) {
                $this->writeParsable($writer, $contentType, $partValue);
            }
            elseif (is_string($partValue)) {
                   $writer->writeStringValue("", $partValue);
            }
            elseif ($partValue instanceof StreamInterface) {
                $writer->writeBinaryContent("", $partValue);
                $partValue->rewind();
            }
            else {
                $type = gettype($partValue);
                throw new InvalidArgumentException("Unsupported type {$type} for part {$partName}");
            }
            $first = false;
        }
        $this->addNewLine($writer);
        $writer->writeStringValue("", "--$this->boundary--");
    }

    /**
     * @param SerializationWriter $writer
     * @param string $contentType
     * @param Parsable $value
     * @return void
     */

    public function writeParsable(SerializationWriter $writer, string $contentType, Parsable $value): void
    {
        if ($this->requestAdapter === null) {
            throw new RuntimeException('Request adapter cannot be null.');
        }
        $partWriter = $this->requestAdapter->getSerializationWriterFactory()->getSerializationWriter($contentType);
        $partWriter->writeObjectValue("", $value);
        $partContent = $partWriter->getSerializedContent();
        $writer->writeBinaryContent('', $partContent);
        $partContent->rewind();
    }

    private function addNewLine(SerializationWriter $writer): void
    {
        $writer->writeStringValue('', '');
    }

    /**
     * @param string $partName
     * @param string $contentType
     * @param mixed $partValue
     * @return void
     */
    public function addOrReplacePart(string $partName, string $contentType, $partValue): void
    {
        if ($partValue === null) {
            throw new RuntimeException('Part value can not be null.');
        }
        $this->parts[$partName] = [$contentType, $partValue];
    }

    /**
     * @param string $partName
     * @return mixed|null
     */
    public function getPartValue(string $partName)
    {
        return $this->parts[$partName][1] ?? null;
    }

    public function removePart(string $partName): bool
    {
        if (array_key_exists($partName, $this->parts)) {
            unset($this->parts[$partName]);
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getBoundary(): string
    {
        return $this->boundary;
    }
}
