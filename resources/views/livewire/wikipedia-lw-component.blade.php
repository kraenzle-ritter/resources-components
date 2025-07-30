@php
    // Ensure $base_url is defined
    $base_url = $base_url ?? 'https://de.wikipedia.org/wiki/';
@endphp

@include('resources-components::livewire.partials.results-layout', [
    'providerKey' => 'wikipedia',
    'providerName' => 'Wikipedia',
    'model' => $model,
    'results' => $results,
    'saveAction' => function($result) use ($base_url) {
        return "saveResource('{$result->pageid}', '{$base_url}{$result->title}')";
    },
    'result_heading' => function($result) {
        return null; // Wikipedia hat keine separaten Überschriften
    },
    'result_content' => function($result) use ($base_url) {
        $output = "<a href=\"{$base_url}{$result->title}\" target=\"_blank\">{$result->title}</a><br>";
        $output .= strip_tags($result->snippet ?? '');
        return $output;
    }
])
