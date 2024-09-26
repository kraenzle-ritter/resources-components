<div id="provider-select" class="card my-4">
@if($providers)
    <div class="card-header">
        <h5>New links</h5>
    </div>
    <div>
    <form class="form-inline p-2">
        <label class="py-2">Provider</label>
        <select wire:change="setProvider($event.target.value)" class="form-select">
            @foreach($providers as $value)
                <option {{ ($provider == $value) ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
        </select>
    </form>
        <br>
        @switch(strtolower($provider))
            @case(config('resources-components.anton.provider-slug'))
                @livewire('anton-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]], $endpoint])
                @break
            @case('geonames')
                @livewire('geonames-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['maxRows' => 5]]])
                @break
            @case('gnd')
                @livewire('gnd-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                @break
            @case('idiotikon')
                @livewire('idiotikon-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                @break
            @case('metagrid')
                @livewire('metagrid-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                @break
            @case('ortsnamen')
                @livewire('ortsnamen-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                @break
            @case('wikidata')
                @livewire('wikidata-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                @break
            @case('wikipedia')
                @livewire('wikipedia-lw-component', [$model, 'search' => $model->resource_search ?? $model->name, 'params' => ['queryOptions' => ['size' => 5]]])
                 @break
            @case('manual-input')
                @livewire('manual-input-lw-component', [$model])
                @break
            @default
                Kein Provider ausgewählt
        @endswitch
@endif
</div>
