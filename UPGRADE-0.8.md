# UPGRADE FROM 0.7 to 0.8

## Domain

- Updated to `symfony/messenger@^4.2`
- Updated `DomainMessageBusInterface::dispatch()` to return `void`
- Updated `DomainCollectionInterface::map()` to return `DomainCollectionInterface`
- Renamed `TreeBuilder` to `TreeBuilderHelper`
- Removed `ChainObjectFactory` and `ClassMappingObjectFactory`
- Added `DomainObjectFactoryInterface::getClass()`

## UserEav

- Added the bridge domain
