<div>
    @php
        $base_url = 'https://www.geonames.org/';
    @endphp

    @if(!in_array('geonames', $model->resources->pluck('provider')->toArray()))
    <div>
        <form class="form gnd-form" > 
            {{-- <label class="gnd-label">Geonames {{ __('Search') }}</label> --}}
            <input wire:model.live="search" class="form-control gnd-input" type="text" placeholder="{{ $placeholder ?? '' }}">
        </form>
        <br>
        @if($results)
            <h5 class="card-title">Geonames – {{ __('List') }}</h5>
            @foreach($results as $result)
                <button
                    wire:click="saveResource('{{ $result->geonameId }}', '{{ $base_url . $result->geonameId }}', '{{ json_encode($result, JSON_UNESCAPED_UNICODE) }}')"
                    type="submit"
                    class="btn btn-success btn-sm float-end"
                    title="{{ __("Save Geonames ID for Actor") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>

                <h6>{{ $result->toponymName ?? '' }}</h6>
                <small>
                    <a href="{{ $base_url . $result->geonameId }}" target="_blank">{{ $base_url . $result->geonameId }}</a><br>
                    {{ $result->fclName ?? '' }}<br>
                    {{ $result->countryName ?? '' }}
                </small>
                <hr>

            @endforeach
        @else
            {{ __('No matches') }}
        @endif
    </div>
    @endif
</div>
