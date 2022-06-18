<?php

namespace Storable\Tests;

use AsyncAws\Core\Response;
use AsyncAws\Core\Test\Http\SimpleMockedResponse;
use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Result\GetItemOutput;
use AsyncAws\DynamoDb\Result\ListTablesOutput;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Storable\Storage;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Serializer;

class StorageTest extends TestCase
{

    public function testGet()
    {
        // ListTablesOutput Mock
        $listTableOutputMock = $this->createMock(ListTablesOutput::class);
        $listTableOutputMock->method('getTableNames')
            ->willReturn(['storage']);

        // GetItemOutput mock
        $dynamoDbClientMock = $this->createMock(GetItemOutput::class);
        $dynamoDbClientMock->method('getItem')
            ->willReturn([
                ''
            ]);

        // DynamoDbClient mock
        $dynamoDbClientMock = $this->createMock(DynamoDbClient::class);
        $dynamoDbClientMock->method('listTables')
            ->willReturn($listTableOutputMock);

        // Initialize new storage
        $storage =  new Storage($dynamoDbClientMock, new Serializer(), new JsonSerializableNormalizer());
        $data = $storage->get('hey!', 'ho!');

        var_dump($data);exit();
    }
}
