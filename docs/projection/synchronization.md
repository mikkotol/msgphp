# Projection Synchronization

`MsgPhp\Domain\Projection\ProjectionSynchronization` is a utility domain service. Its purpose is to ease synchronizing
[projection documents](documents.md) from source objects.

## API

### `synchronize(): iterable`

Yields all projection documents attempted to be synchronized. The actual document status can be read from [`ProjectionDocument::$status`][api-projection-document-status].

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\{ProjectionDocument, ProjectionDocumentProvider, ProjectionDocumentTransformerInterface, ProjectionRepositoryInterface, ProjectionSynchronization, ProjectionTypeRegistryInterface};

// --- SETUP ---

class MyEntity
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}

/** @var ProjectionTypeRegistryInterface $typeRegistry */
$typeRegistry = ...;
/** @var ProjectionRepositoryInterface $repository */
$repository = ...;
/** @var ProjectionDocumentTransformerInterface $transformer */
$transformer = ...;
$provider = new ProjectionDocumentProvider($transformer, [
    function (): iterable {
        yield new MyEntity(1);
        yield new MyEntity(2);
    },
]);
$synchronization = new ProjectionSynchronization($typeRegistry, $repository, $provider);

// --- USAGE ---

foreach ($synchronization->synchronize() as $document) {
    if (ProjectionDocument::STATUS_SYNCHRONIZED === $document->status) {
        echo 'Synchronized projection for '.get_class($document->source).' with ID '.$document->source->id.PHP_EOL;
        continue;
    }

    echo 'Invalid projection for '.get_class($document->source).' with ID '.$document->source->id.PHP_EOL;

    if (null !== $document->error) {
        echo 'An error occurred for '.get_class($document->source).' with ID '.$document->source->id.PHP_EOL;
        echo $document->error->getMessage().' in '.$document->error->getFile().' at '.$document->error->getLine().PHP_EOL;
    }
}
```

## Command Line Interface

A synchronization can be ran using the CLI when working with Symfony Console.

- [Read more](../infrastructure/symfony-console.md#synchronizeprojectionscommand)

[api-projection-document-status]: https://msgphp.github.io/api/MsgPhp/Domain/Projection/ProjectionDocument.html#property_status
