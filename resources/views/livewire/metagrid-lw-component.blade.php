<div class="p-2">
    @if(!in_array('metagrid', $model->resources->pluck('provider')->toArray()))
    <div>
        <form class="form metagrid-form" >
            <label class="metagrid-label pb-2">Metagrid {{ __('Search') }}</label>
            <input wire:model.live="search" class="form-control metagrid-input" type="text" placeholder="{{ $placeholder ?? '' }}">
        </form>
        <br>
        @if($results)
            <h5>Metagrid – {{ __('List') }}</h5>
            @foreach($results as $result)
                <button
                    wire:click="saveResource('{{ $result['provider_id'] }}', '{{ $result['url'] }}', '{{ json_encode($result, JSON_UNESCAPED_UNICODE) }}')"
                    type="submit"
                    class="btn btn-success btn-xs float-right"
                    title="{{ __("Save Metagrid Concordance for Actor") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>
                <h6>
                    <strong>{{ $result['preferredName'] }}</strong>{{ $result['dates'] ?? '' }}
                    @if(!empty($result['type']) && is_array($result['type']))
                        <small class="text-muted">({{ implode(', ', $result['type']) }})</small>
                    @endif
                </h6>
                <small>
                    <a href="{{ $result['url'] }}" target="_blank">{{ $result['url'] }}</a><br>
                    metagrid-{{ $result['provider_id'] }}<br>
                    @if($result['description'])
                        {{ $result['description'] }}<br>
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
