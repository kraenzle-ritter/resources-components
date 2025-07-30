@include('resources-components::livewire.partials.results-layout', [
    'providerKey' => 'anton',
    'providerName' => 'Anton',
    'model' => $model,
    'results' => $results,
    'saveAction' => function($result) {
        return "saveResource('{$result->id}', '{$result->links[0]->url}', '" . json_encode($result, JSON_UNESCAPED_UNICODE) . "')";
    },
    'result_heading' => function($result) {
        return $result->fullname ?? '';
    },
    'result_content' => function($result) {
        $output = "kba-" . ($endpoint ?? 'persons') . "-{$result->id}<br>";
        $output .= $result->description ?? '';
        return $output;
    }
])
