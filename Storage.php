<?php

namespace App\Storage;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Enum\ComparisonOperator;
use AsyncAws\DynamoDb\Input\DeleteItemInput;
use AsyncAws\DynamoDb\Input\DescribeTableInput;
use AsyncAws\DynamoDb\Input\GetItemInput;
use AsyncAws\DynamoDb\Input\PutItemInput;
use AsyncAws\DynamoDb\Input\QueryInput;
use AsyncAws\DynamoDb\Input\UpdateItemInput;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class Storage
{
    // @todo Set this into config
    public const TABLE = 'emojick_storage';

    public const DEFAULT_NAMESPACE = 'main';

    public const ATTRIBUTE_KEY = 'key';
    public const ATTRIBUTE_NAMESPACE = 'namespace';
    public const ATTRIBUTE_OBJECT = 'object';
    public const ATTRIBUTE_CLASS = 'class';
    public const ATTRIBUTE_DATE = 'date';

    /**
     * @var DynamoDbClient
     */
    public DynamoDbClient $client;

    /**
     * @var SerializerInterface
     */
    public SerializerInterface $serializer;

    public NormalizerInterface $normalizer;

    public function __construct(DynamoDbClient $dynamoDbClient, SerializerInterface $serializer, NormalizerInterface $normalizer)
    {
        $this->client = $dynamoDbClient;
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;

        if (false === $this->tableExists()) {
            throw new RuntimeException(sprintf('Table "%s" does not exist.', self::TABLE));
        }
    }

    protected function tableExists(): bool
    {
        $result = $this->client->listTables();

        foreach ($result->getTableNames() as $table) {
            if ($table === self::TABLE) {
                return true;
            }
        }

        return false;
    }

    public function set(string $key, string $value, string $namespace = self::DEFAULT_NAMESPACE): void
    {
        if (!$this->get($key, $namespace)) {
            $this->insert($key, $value, $namespace);
        } else {
            $this->update($key, $value, $namespace);
        }
    }

    public function get(string $key, string $namespace = self::DEFAULT_NAMESPACE): ?string
    {
        $item = $this->client->getItem(new GetItemInput([
            'TableName' => self::TABLE,
            'ConsistentRead' => true,
            'Key' => [
                self::ATTRIBUTE_KEY => new AttributeValue(['S' => $key]),
                self::ATTRIBUTE_NAMESPACE => new AttributeValue(['S' => $namespace]),
            ],
        ]))->getItem();

        if (!$item) {
            return null;
        }

        return $this->serializer->deserialize($item[self::ATTRIBUTE_OBJECT]->getS(), $item[self::ATTRIBUTE_CLASS]->getS());
    }

    // @todo --------- CONTINUE HERE ----------------

    public function insert(string $key, string $value, string $namespace = self::DEFAULT_NAMESPACE)
    {
        $this->client->putItem(new PutItemInput([
            'TableName' => self::TABLE,
            'Item' => [
                self::ATTRIBUTE_KEY => new AttributeValue(['S' => $key]),
                self::ATTRIBUTE_NAMESPACE => new AttributeValue(['S' => $namespace]),
                self::ATTRIBUTE_OBJECT => new AttributeValue(['S' => $value]),
                self::ATTRIBUTE_DATE => new AttributeValue(['N' => time()]),
            ],
        ]));
    }

    public function update(string $key, string $value, string $namespace = self::DEFAULT_NAMESPACE)
    {
        $this->client->updateItem(new UpdateItemInput([
            'TableName' => self::TABLE,
            'Key' => [
                self::ATTRIBUTE_KEY => new AttributeValue(['S' => $key]),
                self::ATTRIBUTE_NAMESPACE => new AttributeValue(['S' => $namespace]),
            ],
            'AttributeUpdates' => [
                self::ATTRIBUTE_OBJECT => [
                    'Action' => 'PUT',
                    'Value' => ['S' => $value ]
                    ],
                self::ATTRIBUTE_DATE => [
                    'Action' => 'PUT',
                    'Value' => ['N' => time() ]
                ],
            ],
        ]));
    }

    public function remove(string $key, string $namespace = self::DEFAULT_NAMESPACE)
    {
        $this->client->deleteItem(new DeleteItemInput([
            'TableName' => self::TABLE,
            'Key' => [
                self::ATTRIBUTE_KEY => new AttributeValue(['S' => $key]),
                self::ATTRIBUTE_NAMESPACE => new AttributeValue(['S' => $namespace]),
            ],
        ]));
    }

    public function getByNamespace(string $namespace): \Iterator
    {
        return $this->client->query(new QueryInput([
            'TableName' => self::TABLE,
            'KeyConditions' => [
                self::ATTRIBUTE_NAMESPACE => [
                    'ComparisonOperator' => ComparisonOperator::EQ,
                    'AttributeValueList' => [
                        ['S' => $namespace]
                    ]
                ]
            ]
        ]))->getItems();
    }

    public function removeNamespace(string $namespace): void
    {
        foreach ($this->getByNamespace($namespace) as $item) {
            $this->remove($item['key']->getS(), $item['namespace']->getS());
        }
    }
}
