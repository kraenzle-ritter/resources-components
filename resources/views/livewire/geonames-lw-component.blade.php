<div class="p-2">
    @php
        $base_url = 'https://www.geonames.org/';
    @endphp

    @if(!in_array('geonames', $model->resources->pluck('provider')->toArray()))
    <div>
        <form class="form gnd-form" > 
            <label class="gnd-label pb-2">Geonames {{ __('Search') }}</label>
            <input wire:model.live="search" class="form-control gnd-input" type="text" placeholder="{{ $placeholder ?? '' }}">
        </form>
        <br>
        @if($results)
            <h5 class="card-title">Geonames – {{ __('List') }}</h5>
            @foreach($results as $result)
                <button
                    wire:click="saveResource('{{ $result['provider_id'] }}', '{{ $result['url'] }}', '{{ json_encode($result, JSON_UNESCAPED_UNICODE) }}')"
                    type="submit"
                    class="btn btn-success btn-xs float-right"
                    title="{{ __("Save Geonames ID for Actor") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>

                <h6>{{ $result['preferredName'] ?? '' }}</h6>
                <small>
                    <a href="{{ $result['url'] }}" target="_blank">{{ $result['url'] }}</a><br>
                    {{ $result['description'] ?? '' }}<br>
                    {{ $result['countryName'] ?? '' }}
                    @if(!empty($result['lat']) && !empty($result['lng']))
                        <br>Coordinates: {{ $result['lat'] }}, {{ $result['lng'] }}
                    @endif
                </small>
                @if (!$loop->last)
                    <hr>
                @endif
            @endforeach
        @else
            {{ __('No matches') }}
        @endif
    </div>
    @endif
</div>
