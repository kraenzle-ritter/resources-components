<div id="provider-select" class="card">
    <div class="card-body">
    <form class="form-inline" > 
        <label>Provider </label>
            <select wire:model="provider" class="form-control">
                @foreach($providers as $value)
                    <option>{{ $value }}</option>
                @endforeach
            </select>
        </form>
        <br>
        @switch(strtolower($provider))
            @case(config('resources-components.anton.provider-slug'))
                @livewire('anton-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]], $endpoint])
                @break
            @case('geonames')
                @livewire('geonames-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                @break
            @case('gnd')
                @livewire('gnd-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                @break
            @case('metagrid')
                @livewire('metagrid-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                @break
            @case('wikidata')
                @livewire('wikidata-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                @break
            @case('wikipedia')
                @livewire('wikipedia-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                @break
            @default
                Kein Provider ausgewählt
        @endswitch
    </div>
</div>
