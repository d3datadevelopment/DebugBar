[![deutsche Version](https://logos.oxidmodule.com/de2_xs.svg)](README.md)
[![english version](https://logos.oxidmodule.com/en2_xs.svg)](README.en.md)

# D³ Debug Bar for OXID eShop

The debug bar enables the display of relevant debug information in the shop frontend.

## Table of content

- [Installation](#installation)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [License](#license)
- [Further licences and terms of use](#further-licences-and-terms-of-use)

## Installation

This package requires an OXID eShop installed with Composer in a version defined in the [composer.json](composer.json).

Please enter the following section in the `composer.json` of your project:

```
  "extra": {
    "ajgl-symlinks": {
      "maximebf/debugbar": {
        "src/DebugBar/Resources": { "source/out/debugbar".
      }
    }
  }
```

Open a command line and navigate to the root directory of the shop (parent directory of source and vendor). Execute the following command. Adapt the path details to your installation environment.


```bash
php composer require d3/modulename:^2.0
``` 

If necessary, please confirm that you allow `composer-symlinker` to execute code.

Activate the module in Shopadmin under "Extensions -> Modules".

## Changelog

See [CHANGELOG](CHANGELOG.md) for further informations.

## Contributing

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue. Don't forget to give the project a star! Thanks again!

- Fork the Project
- Create your Feature Branch (git checkout -b feature/AmazingFeature)
- Commit your Changes (git commit -m 'Add some AmazingFeature')
- Push to the Branch (git push origin feature/AmazingFeature)
- Open a Pull Request

## Licence
(status: 2022-07-30)

Distributed under the GPLv3 license.

```
Copyright (c) D3 Data Development (Inh. Thomas Dartsch)

This software is distributed under the GNU GENERAL PUBLIC LICENSE version 3.
```

For full copyright and licensing information, please see the [LICENSE](LICENSE.md) file distributed with this source code.

## Further licences and terms of use

### Smarty collector
([https://github.com/Junker/php-debugbar-smarty/blob/master/LICENSE](https://github.com/Junker/php-debugbar-smarty/blob/master/LICENSE) - status 2022-07-31)

```
The MIT License (MIT)

Copyright (c) 2016 Dmitry Kosenkov

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```