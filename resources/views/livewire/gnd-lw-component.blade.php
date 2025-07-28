<div class="p-2">
    @if(!in_array('gnd', $model->resources->pluck('provider')->toArray()))
    <div>
        <form class="form gnd-form" > 
            <label class="gnd-label pb-2">GND {{ __('Search') }}</label>
            <input wire:model.live="search" class="form-control gnd-input" type="text" placeholder="{{ $placeholder ?? '' }}">
        </form>
        <br>
        @if($results)
            <h5>GND – {{ __('List') }}</h5>
            @foreach($results as $result)
                <button
                    wire:click="saveResource('{{ $result['gndIdentifier'] }}', '{{ $result['url'] }}', '{{ json_encode($result, JSON_UNESCAPED_UNICODE | JSON_ERROR_SYNTAX) }}')"
                    type="submit"
                    class="btn btn-success btn-xs float-right"
                    title="{{ __("Save GND ID for Actor") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>
                <h6>{{ $result['preferredName'] ?? '' }}
                    {{ isset($result['dateOfBirth'][0]) ? substr($result['dateOfBirth'][0],0,4) : '' }}
                    {{ isset($result['dateOfBirth'][0]) || isset($result['dateOfDeath'][0]) ? '–' : '' }}
                    {{ isset($result['dateOfDeath'][0]) ? substr($result['dateOfDeath'][0],0,4) : '' }}
                </h6>
                <small>
                    <a href="{{ $result['url'] }}" target="_blank">{{ $result['url'] }}</a><br>
                    {{ join('<br>', $result['biographicalOrHistoricalInformation'] ?? []) }}
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
