[![deutsche Version](https://logos.oxidmodule.com/de2_xs.svg)](README.md)
[![english version](https://logos.oxidmodule.com/en2_xs.svg)](README.en.md)

# D³ Debug Bar für OXID eShop

Die Debug Bar ermöglicht die Darstellung relevanter Debuginformationen im Shopfrontend.

![screenshot](screenshot.jpg "Screenshot")

## Inhaltsverzeichnis

- [Installation](#installation)
- [Changelog](#changelog)
- [Beitragen](#beitragen)
- [Lizenz](#lizenz)
- [Weitere Lizenzen und Nutzungsbedingungen](#weitere-lizenzen-und-nutzungsbedingungen)

## Installation

Dieses Paket erfordert einen mit Composer installierten OXID eShop in einer in der [composer.json](composer.json) definierten Version.

Bitte tragen Sie den folgenden Abschnitt in die `composer.json` Ihres Projektes ein:

```
  "extra": {
    "ajgl-symlinks": {
      "maximebf/debugbar": {
        "src/DebugBar/Resources": "source/out/debugbar"
      }
    },
    "enable-patching": "true",
    "patches": {
      "oxid-esales/oxideshop-ce": {
        "Add overridable functions for advanced profiling in Debug Bar": "https://git.d3data.de/D3Public/DebugBar/raw/branch/patches/overridablefunctions.patch"
      }
    }
  }
```

Öffnen Sie eine Kommandozeile und navigieren Sie zum Stammverzeichnis des Shops (Elternverzeichnis von source und vendor). Führen Sie den folgenden Befehl aus. Passen Sie die Pfadangaben an Ihre Installationsumgebung an.


```bash
php composer require d3/oxid-debugbar:^1.0
``` 

Sofern nötig, bestätigen Sie bitte, dass Sie `composer-symlinker` und `composer-patches` erlauben, Code auszuführen.

Aktivieren Sie das Modul im Shopadmin unter "Erweiterungen -> Module".

## Verwendung

Die DebugBar stellt folgende Tabs dar:
- Messages
  kann individuelle Debugausgaben enthalten. Die Nachrichten können innerhalb des PHP-Codes mit `debugVar($message)` gesetzt werden und entspricht der OXID-Funktion `dumpVar(...)`.
- Request
  zeigt alle Angaben aus GET- und POST-Requests, sowie Session-, Cookie- und Servervariablen
- Timeline
  stellt alle mit `startProfile` und `stopProfile` definierten Bereiche mit einzelner und summierter Ausführungszeit sowie als Wasserfalldiagramm dar
- Monolog
  listet alle an den Monolog Logger übergebenen Lognachrichten
- Database
  zeigt alle zur Generierung der aktuellen Seite nötigen Datenbankabfragen
- Smarty
  listet alle Smarty-Variablen, die auf der aktuellen Shopseite zur Verfügung stehen
- Configuration
  stellt alle Konfigurationseinstellungen des Shops aus Datenbank und Dateien zur Verfügung

## Changelog

Siehe [CHANGELOG](CHANGELOG.md) für weitere Informationen.

## Beitragen

Wenn Sie eine Verbesserungsvorschlag haben, legen Sie einen Fork des Repositories an und erstellen Sie einen Pull Request. Alternativ können Sie einfach ein Issue erstellen. Fügen Sie das Projekt zu Ihren Favoriten hinzu. Vielen Dank.

- Erstellen Sie einen Fork des Projekts
- Erstellen Sie einen Feature Branch (git checkout -b feature/AmazingFeature)
- Fügen Sie Ihre Änderungen hinzu (git commit -m 'Add some AmazingFeature')
- Übertragen Sie den Branch (git push origin feature/AmazingFeature)
- Öffnen Sie einen Pull Request

## Lizenz
(Stand: 30.07.2022)

Vertrieben unter der GPLv3 Lizenz.

```
Copyright (c) D3 Data Development (Inh. Thomas Dartsch)

Diese Software wird unter der GNU GENERAL PUBLIC LICENSE Version 3 vertrieben.
```

Die vollständigen Copyright- und Lizenzinformationen entnehmen Sie bitte der [LICENSE](LICENSE.md)-Datei, die mit diesem Quellcode verteilt wurde.

## weitere Lizenzen und Nutzungsbedingungen

### Smarty-Collector
([https://github.com/Junker/php-debugbar-smarty/blob/master/LICENSE](https://github.com/Junker/php-debugbar-smarty/blob/master/LICENSE) - Stand 31.07.2022)

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