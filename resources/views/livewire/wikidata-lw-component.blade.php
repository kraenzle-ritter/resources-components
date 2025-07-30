@php
    $base_url = 'https://www.wikidata.org/wiki/';
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
        $output = "<a href=\"{$base_url}{$result->id}\" target=\"_blank\">{$result->id}</a><br>";
        $output .= $result->description ?? '';
        return $output;
    }
])
