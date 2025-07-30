@include('resources-components::livewire.partials.results-layout', [
    'providerKey' => 'idiotikon',
    'providerName' => 'Idiotikon',
    'model' => $model,
    'results' => $results,
    'saveAction' => function($result) {
        return "saveResource('{$result->lemmaID}', '{$result->url}', '" . json_encode($result, JSON_UNESCAPED_UNICODE) . "')";
    },
    'result_heading' => function($result) {
        return $result->lemmaText ?? '';
    },
    'result_content' => function($result) {
        $output = "<a href=\"{$result->url}\" target=\"_blank\">{$result->url}</a><br>";
        $output .= "idiotikon-{$result->lemmaID}<br>";
        $output .= $result->description[0] ?? '';
        return $output;
    }
])
