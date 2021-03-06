<?php

namespace Storable;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Enum\ComparisonOperator;
use AsyncAws\DynamoDb\Input\DeleteItemInput;
use AsyncAws\DynamoDb\Input\GetItemInput;
use AsyncAws\DynamoDb\Input\PutItemInput;
use AsyncAws\DynamoDb\Input\QueryInput;
use AsyncAws\DynamoDb\Input\UpdateItemInput;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use Storable\Exception\StorageException;
use Storable\Interface\StorableInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class Storage
{
    public const DEFAULT_TABLE = 'storage';
    public const DEFAULT_NAMESPACE = 'main';
    public const DEFAULT_ATTRIBUTE_KEY = 'key';
    public const DEFAULT_ATTRIBUTE_NAMESPACE = 'namespace';
    public const DEFAULT_ATTRIBUTE_VALUE = 'value';
    public const DEFAULT_ATTRIBUTE_CLASS = 'class';
    public const DEFAULT_ATTRIBUTE_DATE = 'date';

    private string $table = self::DEFAULT_TABLE;
    private string $namespace = self::DEFAULT_NAMESPACE;
    private string $attributeKey = self::DEFAULT_ATTRIBUTE_KEY;
    private string $attributeNamespace = self::DEFAULT_ATTRIBUTE_NAMESPACE;
    private string $attributeValue = self::DEFAULT_ATTRIBUTE_VALUE;
    private string $attributeClass = self::DEFAULT_ATTRIBUTE_CLASS;
    private string $attributeDate = self::DEFAULT_ATTRIBUTE_DATE;

    private bool $tableExists;

    public DynamoDbClient $client;

    public SerializerInterface $serializer;

    public function __construct(DynamoDbClient $dynamoDbClient, SerializerInterface $serializer)
    {
        $this->client = $dynamoDbClient;
        $this->serializer = $serializer;
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function setAttributeKey(string $attributeKey): void
    {
        $this->attributeKey = $attributeKey;
    }

    public function setAttributeNamespace(string $attributeNamespace): void
    {
        $this->attributeNamespace = $attributeNamespace;
    }

    public function setAttributeValue(string $attributeValue): void
    {
        $this->attributeValue = $attributeValue;
    }

    public function setAttributeClass(string $attributeClass): void
    {
        $this->attributeClass = $attributeClass;
    }

    public function setAttributeDate(string $attributeDate): void
    {
        $this->attributeDate = $attributeDate;
    }

    private function tableExists(): bool
    {
        if (isset($this->tableExists)) {
            return $this->tableExists;
        }

        $tables = $this->client->listTables();
        foreach ($tables->getTableNames() as $table) {
            if ($table === $this->table) {
                $this->tableExists = true;

                return true;
            }
        }

        $this->tableExists = false;

        return $this->tableExists;
    }

    public function setObject(StorableInterface $object, string $namespace = null): void
    {
        $this->set($object->getId(), $namespace);
    }

    public function set(string $key, string $value, string $namespace = null): void
    {
        if (!$this->get($key, $namespace)) {
            $this->insert($key, $value, $namespace ?? $this->namespace);
        } else {
            $this->update($key, $value, $namespace ?? $this->namespace);
        }
    }

    public function get(string $key, string $namespace = null): StorableInterface|string|null
    {
        if (false === $this->tableExists()) {
            throw new StorageException(sprintf('Table "%s" does not exist.', $this->table));
        }

        $item = $this->client->getItem(new GetItemInput([
            'TableName' => $this->table,
            'ConsistentRead' => true,
            'Key' => [
                $this->attributeKey => new AttributeValue(['S' => $key]),
                $this->attributeNamespace => new AttributeValue(['S' => $namespace ?? $this->namespace]),
            ],
        ]))->getItem();

        if (!$item) {
            return null;
        }

        if (!empty($item[$this->attributeClass]->getS())) {
            return $this->serializer->deserialize($item[$this->attributeValue]->getS(), $item[$this->attributeClass]->getS(), 'json');
        }

        return $item[$this->attributeValue]->getS();
    }

    private function insert(string $key, string $value, string $namespace = null): void
    {
        $this->client->putItem(new PutItemInput([
            'TableName' => $this->table,
            'Item' => [
                $this->attributeKey => new AttributeValue(['S' => $key]),
                $this->attributeNamespace => new AttributeValue(['S' => $namespace ?? $this->namespace]),
                $this->attributeValue => new AttributeValue(['S' => $value]),
                $this->attributeDate => new AttributeValue(['N' => (string) time()]),
            ],
        ]));
    }

    private function update(string $key, string $value, string $namespace = null): void
    {
        $this->client->updateItem(new UpdateItemInput([
            'TableName' => $this->table,
            'Key' => [
                $this->attributeKey => new AttributeValue(['S' => $key]),
                $this->attributeNamespace => new AttributeValue(['S' => $namespace ?? $this->namespace]),
            ],
            'AttributeUpdates' => [
                $this->attributeValue => [
                    'Action' => 'PUT',
                    'Value' => ['S' => $value],
                ],
                $this->attributeDate => [
                    'Action' => 'PUT',
                    'Value' => ['N' => time()],
                ],
            ],
        ]));
    }

    public function remove(string $key, string $namespace = null): void
    {
        $this->client->deleteItem(new DeleteItemInput([
            'TableName' => $this->table,
            'Key' => [
                $this->attributeKey => new AttributeValue(['S' => $key]),
                $this->attributeNamespace => new AttributeValue(['S' => $namespace ?? $this->namespace]),
            ],
        ]));
    }

    public function getByNamespace(string $namespace): \Iterator
    {
        return $this->client->query(new QueryInput([
            'TableName' => $this->table,
            'KeyConditions' => [
                $this->attributeNamespace => [
                    'ComparisonOperator' => ComparisonOperator::EQ,
                    'AttributeValueList' => [
                        ['S' => $namespace],
                    ],
                ],
            ],
        ]))->getItems();
    }

    public function removeNamespace(string $namespace): void
    {
        foreach ($this->getByNamespace($namespace) as $item) {
            $this->remove($item['key']->getS(), $item['namespace']->getS());
        }
    }
}
