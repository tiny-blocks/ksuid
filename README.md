# Ksuid

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div> 

## Overview

Ksuid stands for [K-Sortable Unique Identifier](https://segment.com/blog/a-brief-history-of-the-uuid). It's a way to
generate globally unique IDs which are partially chronologically sortable.

<div id='installation'></div>

## Installation

```bash
composer require tiny-blocks/ksuid
```

<div id='how-to-use'></div>

## How to use

The library exposes a concrete implementation through the `Ksuid` class.

With the `random` method, a new instance of type `Ksuid` is created from a timestamp (_current unix timestamp - EPOCH_)
and a payload (_cryptographically secure pseudo-random bytes_).

```php
$ksuid = Ksuid::random();

echo $ksuid->getValue();     # 2QvY47aUlV3cSyYcpo53FQxgSFg
echo $ksuid->getPayload();   # bdf0a2329620aa70cebe4026ca9ff49c
echo $ksuid->getTimestamp(); # 286235327
```

You can also choose from other factory models.

```php

Ksuid::from(payload: hex2bin("9850EEEC191BF4FF26F99315CE43B0C8"), timestamp: 286235327);

Ksuid::fromPayload(value: '0o5Fs0EELR0fUjHjbCnEtdUwQe3');

Ksuid::fromTimestamp(value: 286235327);
```

## License

Math is licensed under [MIT](/LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
