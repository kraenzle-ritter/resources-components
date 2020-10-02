<div class="card">
    @if(!in_array('gnd', $model->resources->pluck('provider')->toArray()))
    <div class="card-body">
        <form class="form gnd-form" > 
            {{-- <label class="gnd-label">GND {{ __('Search') }}</label> --}}
            <input wire:model="search" class="form-control gnd-input" type="text" placeholder="{{ $placeholder ?? '' }}">
        </form>
        <br>
        @if($results)
            <h5 class="card-title">GND – {{ __('List') }}</h5>
            @foreach($results as $result)
                <button
                    wire:click="saveResource('{{ $result->gndIdentifier }}', '{{ $result->id }}', '{{ json_encode($result, JSON_UNESCAPED_UNICODE) }}')"
                    type="submit"
                    class="btn btn-success btn-xs float-right"
                    title="{{ __("Save GND ID for Actor") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>

                <h6>{{ $result->preferredName ?? '' }}</h6>
                <small>
                    <a href="{{ $result->id }}" target="_blank">{{ $result->id }}</a><br>
                    {{ join('<br>', $result->biographicalOrHistoricalInformation ?? []) }}
                </small>
                <hr>

            @endforeach
        @else
            {{ __('No matches') }}
        @endif
    </div>
    @endif
</div>



