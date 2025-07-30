@include('resources-components::livewire.partials.results-layout', [
    'providerKey' => 'gnd',
    'providerName' => 'GND',
    'model' => $model,
    'results' => $results,
    'saveAction' => function($result) {
        return "saveResource('{$result->gndIdentifier}', '{$result->id}', '" . json_encode($result, JSON_UNESCAPED_UNICODE) . "')";
    },
    'result_heading' => function($result) {
        $heading = $result->preferredName ?? '';
        $birthYear = isset($result->dateOfBirth[0]) ? substr($result->dateOfBirth[0], 0, 4) : '';
        $deathYear = isset($result->dateOfDeath[0]) ? substr($result->dateOfDeath[0], 0, 4) : '';
        $separator = (isset($result->dateOfBirth[0]) || isset($result->dateOfDeath[0])) ? '–' : '';

        return "{$heading} {$birthYear} {$separator} {$deathYear}";
    },
    'result_content' => function($result) {
        $output = "<a href=\"{$result->id}\" target=\"_blank\">{$result->id}</a><br>";

        if(isset($result->biographicalOrHistoricalInformation)) {
            $output .= join('<br>', $result->biographicalOrHistoricalInformation);
        }

        return $output;
    }
])
