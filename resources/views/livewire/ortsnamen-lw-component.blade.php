@include('resources-components::livewire.partials.results-layout', [
    'providerKey' => 'ortsnamen',
    'providerName' => 'Ortsnamen',
    'model' => $model,
    'results' => $results,
    'saveAction' => function($result) {
        return "saveResource('{$result->id}', '{$result->permalink}', '" . json_encode($result, JSON_UNESCAPED_UNICODE) . "')";
    },
    'result_heading' => function($result) {
        return ($result->name ?? '') . ' (' . join(', ', $result->types) . ')';
    },
    'result_content' => function($result) {
        $output = "<a href=\"{$result->permalink}\" target=\"_blank\">{$result->permalink}</a><br>";
        $output .= "ortsnamen-{$result->id}<br>";
        $output .= $result->description[0] ?? '';
        return $output;
    }
])
