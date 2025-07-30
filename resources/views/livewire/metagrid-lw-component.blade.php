@include('resources-components::livewire.partials.results-layout', [
    'providerKey' => 'metagrid',
    'providerName' => 'Metagrid',
    'model' => $model,
    'results' => $results,
    'saveAction' => function($result) {
        return "saveResource('{$result->id}', '{$result->uri}', '" . json_encode($result, JSON_UNESCAPED_UNICODE) . "')";
    },
    'result_heading' => function($result) {
        return $result->provider ?? '';
    },
    'result_content' => function($result) {
        $name = $result->name;
        $name = preg_replace('/^([^0-9]+)(\d{4}).*(\d{4}?).*$/', '${1} ($2-$3)', $name);
        $name = preg_replace('/^([^0-9]+)(\d{4})-\d{2}-\d{2}$/', '${1} ($2)', $name);
        
        return "<a href=\"{$result->uri}\" target=\"_blank\">{$name}</a>";
    }
])
