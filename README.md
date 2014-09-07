HolyTransaction PHP client
==================================

PHP Library for the HolyTransaction.com API.

This is initial release intended for use by several early adopters.

If you are interested in using HolyTransaction cryptocurrency platform in your project please register at
[http://merchant.holytransaction.com/](http://merchant.holytransaction.com/)

## Version

0.1.3

## Requirements

- [HolyTransaction Merchant Account](http://merchant.holytransaction.com/)
- [Libsodium & libsodium-php](https://github.com/jedisct1/libsodium-php) (only if you will be using API for creating new accounts)
- [Scrypt](https://github.com/DomBlack/php-scrypt) (only if you will be using API for creating new accounts)

## Installation

- Install required libraries
- git clone git@bitbucket.org:noveltylab/ht-client-php.git
- Include this library and use as shown in example.php

## Usage

```php
require_once 'HolyTransaction/HolyTransaction.php';

$exchangeRates = $ht->get('data/exchange_rates', array('show_fiat' => 1), false);
var_dump($exchangeRates);
```

## Changelog

0.1.3

* Correct usage of libsodium-php for key generation

0.1.2

* Updated example

0.1.1

* Initial release

## Contributing

Feel free to submit issues and pull requests at https://bitbucket.org/noveltylab/ht-client-php/

## License

(The MIT License)

Copyright (C) 2014 Noveltylab inc. <contacts@noveltylab.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.