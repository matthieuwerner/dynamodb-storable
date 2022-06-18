<?php

namespace Storable\Tests;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Result\GetItemOutput;
use AsyncAws\DynamoDb\Result\ListTablesOutput;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use PHPUnit\Framework\TestCase;
use Storable\Storage;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @internal
 * @coversNothing
 */
class StorageTest extends TestCase
{
    public function testGet()
    {
        // ListTablesOutput Mock
        $listTableOutputMock = $this->createMock(ListTablesOutput::class);
        $listTableOutputMock->method('getTableNames')
            ->willReturn(['storage'])
        ;

        // GetItemOutput mock
        $getItemOutputMock = $this->createMock(GetItemOutput::class);
        $getItemOutputMock->method('getItem')
            ->willReturn([
                'key' => new AttributeValue(['S' => 'myKey']),
                'namespace' => new AttributeValue(['S' => 'main']),
                'object' => new AttributeValue(['S' => '{"id": "42"}']),
                'class' => new AttributeValue(['S' => 'Storable\Tests\ValueObjectTest']),
                'date' => new AttributeValue(['S' => '123456']),
            ])
        ;

        // DynamoDbClient mock
        $dynamoDbClientMock = $this->createMock(DynamoDbClient::class);
        $dynamoDbClientMock->method('listTables')
            ->willReturn($listTableOutputMock)
        ;
        $dynamoDbClientMock->method('getItem')
            ->willReturn($getItemOutputMock)
        ;

        // Initialize new storage
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $storage = new Storage($dynamoDbClientMock, new Serializer($normalizers, $encoders));

        // Try getting some object
        $object = $storage->get('42');

        $this->assertInstanceOf(ValueObjectTest::class, $object);
        $this->assertEquals('42', $object->getId());
    }
}
