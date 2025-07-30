@php
    $base_url = 'https://www.geonames.org/';
@endphp

@include('resources-components::livewire.partials.results-layout', [
    'providerKey' => 'geonames',
    'providerName' => 'Geonames',
    'model' => $model,
    'results' => $results,
    'saveAction' => function($result) use ($base_url) {
        return "saveResource('{$result->geonameId}', '{$base_url}{$result->geonameId}', '" . json_encode($result, JSON_UNESCAPED_UNICODE) . "')";
    },
    'result_heading' => function($result) {
        return $result->toponymName ?? '';
    },
    'result_content' => function($result) use ($base_url) {
        $output = "<a href=\"{$base_url}{$result->geonameId}\" target=\"_blank\">{$base_url}{$result->geonameId}</a><br>";
        $output .= ($result->fclName ?? '') . "<br>";
        $output .= $result->countryName ?? '';
        return $output;
    }
])
