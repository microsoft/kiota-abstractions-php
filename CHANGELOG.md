# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

### Changed

## [0.8.4] - 2023-10-12

### Added
- exposed the tryAdd method of request headers through request info.

### Changed
- Defaults the content type parameter in `setStreamContent` to `application/octet-stream`

## [0.8.3] - 2023-10-11

### Added
- Adds CHANGELOG. [#86](https://github.com/microsoft/kiota-abstractions-php/pull/86)
- Added a content type parameter to the set stream content method in request information.
- Added a try add method for request headers.

### Changed
- Update tests for date serialization logic. [#89](https://github.com/microsoft/kiota-abstractions-php/pull/89)

## [0.8.2] - 2023-10-05

### Added
- Adds missing fabric bot configuration. [#76](https://github.com/microsoft/kiota-abstractions-php/pull/76)
- Add support for observability. [#80](https://github.com/microsoft/kiota-abstractions-php/pull/80)
- Add tryAdd to RequestHeaders. [#81](https://github.com/microsoft/kiota-abstractions-php/pull/81)

### Changed
- Switch to std-uritemplate. [#78](https://github.com/microsoft/kiota-abstractions-php/pull/78)

## [0.8.1] - 2023-07-10

### Changed
- Validate derived types in collections. [#74](https://github.com/microsoft/kiota-abstractions-php/pull/74)

## [0.8.0] - 2023-06-29

### Changed
- Handle nulls when merging deserializers & serializing intersection wrappers. [#70](https://github.com/microsoft/kiota-abstractions-php/pull/70)

## [0.7.1] - 2023-06-20

### Added
- Add util to validate type of collection values. [#67](https://github.com/microsoft/kiota-abstractions-php/pull/67)

### Changed
- Disable pipeline runs for forks. [#66](https://github.com/microsoft/kiota-abstractions-php/pull/66)

## [0.7.0] - 2023-05-18

### Changed
- Remove unused abandoned `php-http/message-factory` dependency. [#61](https://github.com/microsoft/kiota-abstractions-php/pull/61)

## [0.6.7] - 2023-05-16

### Added
- CAE support - Allow auth layer to receive claims. [#51](https://github.com/microsoft/kiota-abstractions-php/pull/51)

## [0.6.6] - 2023-05-05

### Added
- Add response headers to the api exception class. [#57](https://github.com/microsoft/kiota-abstractions-php/pull/57)

## [0.6.5] - 2023-05-05

### Changed
- Fix visibility of request config properties. [#54](https://github.com/microsoft/kiota-abstractions-php/pull/54)

## [0.6.4] - 2023-04-13

### Added
- Add base type for config and request builder. [#43](https://github.com/microsoft/kiota-abstractions-php/pull/43)

## [0.6.3] - 2023-03-22

### Added
- Add generic PHPDoc types to request adapter. [#47](https://github.com/microsoft/kiota-abstractions-php/pull/47)

## [0.6.2] - 2023-03-21

### Added
- Add generics PHPDoc tags to ParseNode methods. [#42](https://github.com/microsoft/kiota-abstractions-php/pull/42)

## [0.6.1] - 2023-03-07

### Changed
- Refactor request headers. [#37](https://github.com/microsoft/kiota-abstractions-php/pull/37)
- Return empty if request header key doesn't exist. [#38](https://github.com/microsoft/kiota-abstractions-php/pull/38)
- Ignore interfaces from coverage information. [#39](https://github.com/microsoft/kiota-abstractions-php/pull/39)

## [0.6.0] - 2023-02-21

### Added
- Support deserialization of composed types. [#29](https://github.com/microsoft/kiota-abstractions-php/pull/29)
- Adds dependabot auto-merge and conflicts workflows. [#31](https://github.com/microsoft/kiota-abstractions-php/pull/31)
- Add test matrix for supported PHP versions. [#30](https://github.com/microsoft/kiota-abstractions-php/pull/30)
- Add SonarCloud code coverage. [#32](https://github.com/microsoft/kiota-abstractions-php/pull/32)
- Add response status code to API exception. [#34](https://github.com/microsoft/kiota-abstractions-php/pull/34)

### Changed
- Make  return `BackingStoreFactorySingleton` `getInstance()` return type non-nullable. [#33](https://github.com/microsoft/kiota-abstractions-php/pull/33)

### Removed
- Deprecates `[get|set]Response` from `ApiException`. The exception now only exposes the HTTP response status code

For entries before this version, please see [Release Notes](https://github.com/microsoft/kiota-abstractions-php/releases)
