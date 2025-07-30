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
        $output = "<a href=\"{$result->links[0]->url}\" target=\"_blank\">{$result->links[0]->url}</a>";

        // Beschreibung, falls vorhanden
        if (!empty($result->description)) {
            // Ersten Satz extrahieren
            $firstSentence = preg_split('/[.!?]/', $result->description, 2);
            if (!empty($firstSentence[0])) {
                $output .= "<br>" . trim($firstSentence[0]) . ".";
            } else {
                $output .= "<br>" . $result->description;
            }
        }

        return $output;
    }
])
