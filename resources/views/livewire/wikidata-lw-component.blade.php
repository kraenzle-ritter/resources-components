@php
    // $base_url wird jetzt von der Komponente bereitgestellt
    $base_url = $base_url ?? 'https://www.wikidata.org/wiki/'; // Fallback, falls die Komponente keinen Wert liefert

    // Debug-Ausgabe
    if (class_exists('\Log')) {
    }
@endphp

@include('resources-components::livewire.partials.results-layout', [
    'providerKey' => 'wikidata',
    'providerName' => 'Wikidata',
    'model' => $model,
    'results' => $results,
    'saveAction' => function($result) use ($base_url) {
        return "saveResource('{$result->id}', '{$base_url}{$result->id}', '" . json_encode($result, JSON_UNESCAPED_UNICODE) . "')";
    },
    'result_heading' => function($result) {
        return $result->label ?? '';
    },
    'result_content' => function($result) use ($base_url) {
        $output = "<a href=\"{$base_url}{$result->id}\" target=\"_blank\">{$base_url}{$result->id}</a>";

        if (!empty($result->description)) {
            $output .= "<br>" . $result->description;
        }

        return $output;
    }
])
