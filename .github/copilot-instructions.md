
# kraenzle-ritter/resources-components

Das Packet soll für ein Laravel Model, das den Trait `hasResource` (kraenzle-ritter/resources), resources (Links) bei verschiedenen auswählbaren Providern suchen (`provider-select.blade.php`). Ausserdem soll es anzeigen, welche Resourcen bereits mit dem Model verlinkt sind (`resources-list.blade.php`).

- Laravel 11 und  Livewire 3.4, Bootstrap >= 5 

```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
```

- Die Suche bei den Provider erfolgt mit einem einfachen Suchstring.

### Als Benutzer:
- Ich möchte für ein $model, z.B. in der GND nach einer Übereinstimmung suchen und aus einer Trefferliste, den richtigen Eintrag auswählen und in der resources Tabelle speichern.
- Ich möchte bereits verknüpfte Resourcen von einem Model löschen können.
- Ich möchte manuell Links speichern können.
- Ich möchte bei Orten in Geonames mit der Speicherung die Geokoordinaten übernehmen.

### Als Admin:
Ich möchte konfigurieren, welche Provider für welche models zur Verfügung stehen. 
Ich möchte weitere Sprachversionen für Wikipedia hinzufügen können. 
Ich möchte Provider hinzufügen, die die Anton API verwenden.

### Als Developer:
Ich möchte eine Dokumentation und Hilfestellung, um einen neuen Provider zu implementieren.
Ich möchte aussagekräftige Tests haben, die die Funktionstüchtigkeit der Componenten sicherstellen.

## Verwendung in einer Applikation

Im Projekt Anton, für das die Komponente gedacht ist, sieht die Verwendung aktuell so aus:

```
  <div class="col-md-4">
      <div class="mb-5">
          @livewire('resources-list', [$model, 'deleteButton' => true])
      </div>
      @livewire('provider-select', [$model, $providers, 'actors'])
  </div>
```
Diese Verwendung muss weiterhin funktionieren.

Wobei $providers eine einfache Liste (array) enhält, das den Einträgen in der config('providers') des Pakets bzw. nach vendor publish auch in Anton enspricht, also z.B. `['gnd', 'geonames', 'manual-input']`.

## Wichtige Elemente und Funktionen

Einzelne Providerklassen sollten eine Abstract Class oder ein Interface implementieren, das folgende Funktion enthält. Einzelne Providerklassen sollten diese Funktion dann implementieren:

````
public function search(string $search, array $params = [])
```

In Zukunft sollen weitere Provider hinzugefügt werden. Die Provider können auch in der config/resources-components.php konfiguriert werden, wenn das Paket in einem Projekt installiert ist.

Die Funktion `function processResult($results): array` sollte dann die Rückgabe von `search` verarbeiten und vereinheitlichen, so dass es in der Livewire View einheitlich in der Trefferliste angezeigt werden kann und in der Livewire-View mit `public function saveResource($provider_id, $url, $full_json = null)` angewendet werden kann. Allenfalls kann die Funktion processResult($results) die Ergebnisse so vereinheitlichen, dass nur noch eine View nötig ist. Die Anzeige müsste in der Component entsprechend vorbereitet werden. 

## Todos:
- Umarbeitung auf die neue Config Struktur 
- Anpassung der Wikipedia und der Antonkomponente gemäss neuer Config
- Tests für die Livewire Components (werden sie geladen, funktioniert die Suche, werden die Ergebnisse angezeigt, können sie gespeichert werden)
- Dokumentation für Installation, Verwendung und die Implementierung neuer Providertypen/Provider.


- Alle Kommentare, interne Fehlermeldungen und die Dokumentation (readme.md, changelog.md) sind in Englisch zu schreiben.
