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

### Creating a Ksuid

With the `random` method, a new instance of type `Ksuid` is created from a timestamp (_current unix timestamp - EPOCH_)
and a payload (_cryptographically secure pseudo-random bytes_).

```php
$ksuid = Ksuid::random();

echo $ksuid->getValue();     # 2QzPUGEaAKHhVcQYrqQodbiZat1
echo $ksuid->getPayload();   # 464932c1194da98e752145d72b8f0aab
echo $ksuid->getUnixTime();  # 1686353450
echo $ksuid->getTimestamp(); # 286353450
```

You can also choose from other factory models.

```php

Ksuid::from(payload: hex2bin('9850EEEC191BF4FF26F99315CE43B0C8'), timestamp: 286235327);

Ksuid::fromPayload(value: '0o5Fs0EELR0fUjHjbCnEtdUwQe3');

Ksuid::fromTimestamp(value: 286235327);
```

### Inspecting a Ksuid

You can inspect the components used to create a `Ksuid`, using the `inspectFrom` method.

```php
$ksuid = Ksuid::inspectFrom(ksuid: '2QzPUGEaAKHhVcQYrqQodbiZat1');

print_r($ksuid); # Array
                 # (
                 #    [time] => 2023-06-09 20:30:50 -0300 -03
                 #    [payload] => 464932c1194da98e752145d72b8f0aab
                 #    [timestamp] => 286353450
                 # )
```

## License

Math is licensed under [MIT](/LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
