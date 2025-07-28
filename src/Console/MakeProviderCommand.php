<?php

namespace KraenzleRitter\ResourcesComponents\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeProviderCommand extends Command
{
    protected $signature = 'make:resources-provider {name : The name of the provider}';

    protected $description = 'Create a new resources provider';

    public function handle()
    {
        $name = $this->argument('name');
        $studlyName = Str::studly($name);
        $lowerName = Str::lower($name);

        $this->createProviderClass($studlyName, $lowerName);
        $this->createLivewireComponent($studlyName, $lowerName);

        $this->info("Provider {$studlyName} created successfully!");
        $this->comment("Don't forget to:");
        $this->comment("1. Register the provider in ProviderFactory");
        $this->comment("2. Register the Livewire component in ResourcesComponentsServiceProvider");
        $this->comment("3. Create the corresponding Blade view");
        $this->comment("4. Add configuration in resources-components.php");
    }

    protected function createProviderClass($studlyName, $lowerName)
    {
        $stub = $this->getProviderStub();
        $content = str_replace(
            ['{{StudlyName}}', '{{lowerName}}', '{{providerName}}'],
            [$studlyName, $lowerName, $studlyName],
            $stub
        );

        $path = app_path("../packages/kraenzle-ritter/resources-components/src/{$studlyName}.php");
        file_put_contents($path, $content);

        $this->info("Created provider class: {$studlyName}.php");
    }

    protected function createLivewireComponent($studlyName, $lowerName)
    {
        $stub = $this->getLivewireStub();
        $content = str_replace(
            ['{{StudlyName}}', '{{lowerName}}', '{{providerName}}'],
            [$studlyName, $lowerName, $studlyName],
            $stub
        );

        $path = app_path("../packages/kraenzle-ritter/resources-components/src/{$studlyName}LwComponent.php");
        file_put_contents($path, $content);

        $this->info("Created Livewire component: {$studlyName}LwComponent.php");
    }

    protected function getProviderStub()
    {
        return '<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;

class {{StudlyName}} extends AbstractProvider
{
    public function getBaseUrl(): string
    {
        return $this->getConfigValue("base_url", "https://api.{{lowerName}}.com/");
    }

    public function getProviderName(): string
    {
        return "{{providerName}}";
    }

    public function search(string $search, array $params = [])
    {
        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);

        // TODO: Implement your search logic here
        $searchQuery = "search?q=" . urlencode($search);
        $limit = $params["limit"] ?? $this->getConfigValue("limit", 5);

        // Add any additional query parameters
        $searchQuery .= "&limit=" . $limit;

        $result = $this->makeRequest("GET", $searchQuery);

        // TODO: Process and return the results
        return $result;
    }
}
';
    }

    protected function getLivewireStub()
    {
        return '<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class {{StudlyName}}LwComponent extends AbstractLivewireComponent
{
    protected function getProviderName(): string
    {
        return "{{providerName}}";
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create("{{lowerName}}");
    }

    protected function getDefaultOptions(): array
    {
        return ["limit" => 5];
    }

    protected function processResults($results)
    {
        if (!$results) {
            return [];
        }

        // TODO: Process the raw results into a standardized format
        $processedResults = [];

        foreach ($results as $result) {
            $processedResults[] = [
                "title" => $result->title ?? "",
                "url" => $result->url ?? "",
                "provider_id" => $result->id ?? "",
                "description" => $result->description ?? "",
            ];
        }

        return $processedResults;
    }

    public function render()
    {
        $results = [];

        if ($this->search) {
            $client = $this->getProviderClient();
            $resources = $client->search($this->search, $this->queryOptions);
            $results = $this->processResults($resources);
        }

        return view($this->getViewName(), [
            "results" => $results
        ]);
    }
}
';
    }
}
