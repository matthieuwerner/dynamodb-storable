# DynamoDB Storable Interface

This component, based on the Symfony serializer and async-aws, is human-readable and
quick abstraction to easily store serialized objects in DynamoDB 🚀.

## Installation

```bash
composer require matthieuwerner/dynamodb-storable 
```

## Usage

### Option 1: autowiring 

```php
use Storable\Storage;

protected function anyAction(Storage $storage): string
{
    $word = $storage->get('mot_JOUR', 'word_game');
}
```

// créer la possibilité de gérer clé valeur OU via objets !!! 
// 1 ça bc break pas emojick, 2 c plus cool et ça justiofie l'interface
