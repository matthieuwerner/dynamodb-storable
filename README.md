![CI status](https://github.com/matthieuwerner/dynamodb-storable/actions/workflows/ci.yml/badge.svg)

# DynamoDB Storable

This component, based on the Symfony serializer and async-aws, is a human-readable and
quick abstraction to easily store serialized objects in DynamoDB 🚀.

This storage use an existing DynamoDB table and create entries with
the following structure :
`{"key", "namespace", "value", "class", "date""}`.

- [DynamoDB Storable Interface](#dynamodb-storable-interface)
    * [Installation](#installation)
    * [Usage](#usage)
        + [Adding service](#adding-service)
            - [Option 1: auto wiring (Symfony/Laravel)](#option-1--auto-wiring--symfony-laravel-)
            - [Option 2: instantiate class](#option-2--instantiate-class)
        + [Write](#write)
        + [Read](#read)
        + [Remove](#remove)
        + [Working with namespaces](#working-with-namespaces)
        + [Changing default values](#changing-default-values)


## Installation

```bash
composer require matthieuwerner/dynamodb-storable 
```

## Usage

### Adding service 

#### Option 1: auto wiring (Symfony/Laravel)

```php
use Storable\Storage;

protected function anyAction(Storage $storage): string
{
    $storage->set('key', 'value');
    // ...
}
```

#### Option 2: instantiate class

```php
use Storable\Storage;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

protected function anyAction(): string
{
    $encoders = [new JsonEncoder()];
    $normalizers = [new ObjectNormalizer()];
    $storage = new Storage($dynamoDbClientMock, new Serializer($normalizers, $encoders));

    $storage->set('key', 'value');
    // ...
}
```

### Write

```php
// String 
$storage->set('key', 'value');

// Object 
$myObject  = new MyObject();
$storage->setObject($myObject);
```

**/!\ Working with objects need that they implement "StorableInterface"**

### Read

```php
// String 
$storage->get('key'); // Return a string

// Object 
$storage->get($objectId); // Return an object
```

### Remove

```php
$storage->remove('key');
```

### Working with namespaces

```php
// Get all objects in a namespace
$storage->getByNamespace('namespace');

// Remove all objects in a namespace
$storage->removeNamespace('namespace');
```

### Changing default values

```php
// Change the default table name (default is "storage")
$storage->setTable('newTableName');

// Change the default namespace (default is "main")
$storage->setNamespace('newNamespaceName');

// Change the default attribute "key" in the table structure
$storage->setAttributeKey('newKeyAttribute');

// Change the default attribute "namespace" in the table structure
$storage->setAttributeNamespace('newNamespaceAttribute');

// Change the default attribute "value" in the table structure
$storage->setAttributeValue('newValueAttribute');

// Change the default attribute "class" in the table structure
$storage->setAttributeClass('newClassAttribute');

// Change the default attribute "date" in the table structure
$storage->setAttributeDate('newDateAttribute');
```
