@php
    // Ensure $base_url is defined - should be passed from the component
    // The base_url depends on the selected language (via providerKey)
    $base_url = $base_url ?? 'https://de.wikipedia.org/wiki/';

    // Zusätzlich zu URL-Debugging auch den kompletten Query-String ausgeben
    $debugInfo = 'Base URL: ' . $base_url;
    if (isset($_SERVER['QUERY_STRING'])) {
        $debugInfo .= ', Query: ' . $_SERVER['QUERY_STRING'];
    }

    // For debugging
    if (class_exists('\Log')) {
        \Log::debug('Wikipedia view debug info: ' . $debugInfo);
        \Log::debug('Wikipedia view using base_url: ' . $base_url);

        // Prüfe, ob die URL für die richtige Sprache ist
        if (preg_match('/https?:\/\/([a-z]{2})\.wikipedia\.org\/wiki\//', $base_url, $matches)) {
            $language = $matches[1];
            \Log::debug('Wikipedia view language detected: ' . $language);
        } else {
            \Log::warning('Wikipedia view could not detect language from URL: ' . $base_url);
        }
    }
@endphp

@php
    // Extrahiere den korrekten Provider-Namen aus der base_url, falls vorhanden
    $displayProviderKey = 'wikipedia';

    // Versuche, die Sprache aus der URL zu extrahieren
    if (preg_match('/https?:\/\/([a-z]{2})\.wikipedia\.org\/wiki\//', $base_url, $matches)) {
        $language = $matches[1];
        $displayProviderKey = 'wikipedia-' . $language;
    }

    if (class_exists('\Log')) {
        \Log::debug('Wikipedia view using providerKey for display: ' . $displayProviderKey);
    }
@endphp

@include('resources-components::livewire.partials.results-layout', [
    'providerKey' => $displayProviderKey,
    'providerName' => config('resources-components.providers.' . $displayProviderKey . '.label', 'Wikipedia'),
    'model' => $model,
    'results' => $results,
    'saveAction' => function($result) use ($base_url) {
        // Debug-Ausgabe für die URL direkt vor der Verwendung
        if (class_exists('\Log')) {
            \Log::debug('Wikipedia saveAction URL: ' . $base_url . $result->title);
        }

        // URL korrekt für JavaScript-Attribut kodieren
        $encodedTitle = str_replace("'", "\\'", $result->title);
        $encodedUrl = str_replace("'", "\\'", $base_url . $result->title);

        return "saveResource('{$result->pageid}', '{$encodedUrl}', '{$encodedTitle}')";
    },
    'result_heading' => function($result) {
        return $result->title ?? ''; // Titel als Überschrift verwenden
    },
    'result_content' => function($result) use ($base_url) {
        // Titel und URL für die Anzeige vorbereiten
        $title = $result->title ?? '';
        $url = $base_url . str_replace(' ', '_', $title);

        // URL korrekt für HTML kodieren
        $encodedTitle = htmlspecialchars($title);
        $encodedUrl = htmlspecialchars($url);

        $output = "<a href=\"{$encodedUrl}\" target=\"_blank\">{$encodedUrl}</a>";

        // Verwende die bereits extrahierte firstSentence und stelle sicher, dass HTML korrekt kodiert ist
        if (!empty($result->firstSentence)) {
            $cleanSentence = htmlspecialchars($result->firstSentence);
            $output .= "<br>" . $cleanSentence;
        }

        return $output;
    }
])
