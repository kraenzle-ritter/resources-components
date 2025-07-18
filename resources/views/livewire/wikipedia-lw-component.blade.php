<div class="p-2">
    @if(!in_array('wikipedia', $model->resources->pluck('provider')->toArray()))
    <div>
        <form class="form wikipedia-form" >
            <label class="wikipedia-label pb-2">Wikipedia {{ __('Search') }}</label>
            <input wire:model.live="search" class="form-control wikipedia-input" type="text" placeholder="{{ $placeholder ?? '' }}">
        </form>
        <br>
        @if($results)
            <h5>Wikipedia – {{ __('List') }}</h5>
            @foreach($results as $result)
                <button
                    wire:click="saveResource('{{ $result->pageid }}', '{{ $base_url . $result->title }}')"
                    type="submit"
                    class="btn btn-success btn-xs float-right"
                    title="{{ __("Save Wikipedia for Actor") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>

                <small>
                    <a href="{{ $base_url . $result->title }}" target="_blank">{{ $result->title }}</a><br>
                    {{ strip_tags($result->snippet ?? '') }}
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
