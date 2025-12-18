# Kiota Abstractions Library for PHP

![Build Status](https://github.com/microsoft/kiota-abstractions-php/actions/workflows/pr-validation.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/microsoft/kiota-abstractions/version)](https://packagist.org/packages/microsoft/kiota-abstractions)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=microsoft_kiota-abstractions-php&metric=coverage)](https://sonarcloud.io/dashboard?id=microsoft_kiota-abstractions-php)

The Kiota abstractions Library for PHP is the PHP library defining the basic constructs Kiota projects need once an SDK has been generated from an OpenAPI definition.

A [Kiota](https://github.com/microsoft/kiota) generated project will need a reference to the abstraction package to build and run.

Read more about Kiota [here](https://github.com/microsoft/kiota/blob/main/README.md).

## Using the Abstractions Library
run `composer require microsoft/kiota-abstractions` or add the following to your `composer.json` file:

```
{
    "require": {
        // x-release-please-start-version
        "microsoft/kiota-abstractions": "^1.5.2"
        // x-release-please-end
    }
}
```

## Contributing

This project welcomes contributions and suggestions. Issues and pull requests should be made against the [kiota-php](https://github.com/microsoft/kiota-php/) repository.
This repository is only used for releases.


## Trademarks

This project may contain trademarks or logos for projects, products, or services. Authorized use of Microsoft
trademarks or logos is subject to and must follow
[Microsoft's Trademark & Brand Guidelines](https://www.microsoft.com/en-us/legal/intellectualproperty/trademarks/usage/general).
Use of Microsoft trademarks or logos in modified versions of this project must not cause confusion or imply Microsoft sponsorship.
Any use of third-party trademarks or logos are subject to those third-party's policies.
