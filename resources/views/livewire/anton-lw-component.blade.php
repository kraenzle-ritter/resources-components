<div class="p-2">
    @if(!in_array('anton', $model->resources->pluck('provider')->toArray()))
    <div>
        <form class="form kba-form" > 
            <label class="gnd-label pb-2">Anton {{ __('Search') }}</label>
            <input wire:model.live="search" class="form-control anton-input" type="text" placeholder="{{ $placeholder ?? '' }}">
        </form>
        <br>
        @if($results)
            <h5>Anton – {{ __('List') }}</h5>
            @foreach($results as $result)
                <button
                    wire:click="saveResource('{{ $result->id }}', '{{ $result->links[0]->url }}', '{{ json_encode($result, JSON_UNESCAPED_UNICODE) }}')"
                    type="submit"
                    class="btn btn-success btn-xs float-right"
                    title="{{ __("Save Anton ID") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>
                <h6>{{ $result->fullname ?? '' }}</h6>
                <small>
                    <a target="_blank" href="{{ config('resources-components.anton.url') . '/' . $endpoint .'/'. $result->id }}">{{ config('resources-components.anton.url') . '/' . $endpoint .'/'. $result->id }}</a><br>
                    kba-{{ $endpoint }}-{{ $result->id }}<br>
                    {{ $result->description ?? '' }}
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
