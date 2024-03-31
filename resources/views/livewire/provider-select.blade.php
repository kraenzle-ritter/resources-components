<div id="provider-select" class="card">
@if($providers)
    <div class="card-body">
    <form class="form-inline" >
        <label>Provider</label>
            <select wire.model.live="provider" class="form-control">
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
    </div>
@endif
</div>
