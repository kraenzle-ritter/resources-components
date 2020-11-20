<div>
    @php
        $base_url = 'https://www.wikidata.org/wiki/';
    @endphp

    @if(!in_array('wikidata', $model->resources->pluck('provider')->toArray()))
    <div>
        <form class="form gnd-form" > 
            <label class="gnd-label">Wikidata {{ __('Search') }}</label>
            <input wire:model="search" class="form-control wikidata-input" type="text" placeholder="{{ $placeholder ?? '' }}">
        </form>
        <br>
        @if($results)
            <h5>Wikidata – {{ __('List') }}</h5>
            @foreach($results as $result)
                <button
                    wire:click="saveResource('{{ $result->id }}', '{{ $base_url . $result->id }}', '{{ json_encode($result, JSON_UNESCAPED_UNICODE) }}')"
                    type="submit"
                    class="btn btn-success btn-xs float-right"
                    title="{{ __("Save Wikidata ID for Actor") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>

                <h6>{{ $result->label ?? '' }}</h6>
                <small>
                    <a href="{{ $result->id }}" target="_blank">{{ $base_url . $result->id }}</a><br>
                    {{ $result->description }}
                </small>
                <hr>

            @endforeach
        @else
            {{ __('No matches') }}
        @endif
    </div>
    @endif
</div>
