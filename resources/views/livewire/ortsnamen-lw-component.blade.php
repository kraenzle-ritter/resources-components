<div>
    @if(!in_array('anton', $model->resources->pluck('provider')->toArray()))
    <div>
        <form class="form kba-form" >
            {{-- <label class="anton-label">anton {{ __('Search') }}</label> --}}
            <input wire.model.live="search" class="form-control anton-input" type="text" placeholder="{{ $placeholder ?? '' }}">
        </form>
        <br>

        @if($results)
            <h5>Ortsnamen – {{ __('List') }}</h5>
            @foreach($results as $result)
                <button
                    wire:click="saveResource('{{ $result->id }}', '{{ $result->permalink }}', '{{ json_encode($result, JSON_UNESCAPED_UNICODE) }}')"
                    type="submit"
                    class="btn btn-success btn-xs float-right"
                    title="{{ __("Save Anton ID") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>
                <h6>{{ $result->name ?? '' }} ({{ join(', ', $result->types) }})</h6>
                <small>
                    <a target="_blank" href="{{ $result->permalink }}">{{ $result->permalink }}</a><br>
                     ortsnamen-{{  $result->id }}<br>
                    {{ $result->description[0] ?? '' }}
                </small>
                <hr>
            @endforeach
        @else
            {{ __('No matches') }}
        @endif
    </div>
    @endif
</div>
