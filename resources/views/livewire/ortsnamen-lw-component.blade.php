<div class="p-2">
    @if(!in_array('anton', $model->resources->pluck('provider')->toArray()))
    <div>
        <form class="form kba-form" >
            <label class="gnd-label pb-2">Ortsnamen {{ __('Search') }}</label>
            <input wire:model.live="search" class="form-control anton-input" type="text" placeholder="{{ $placeholder ?? '' }}">
        </form>
        <br>

        @if($results)
            <h5>Ortsnamen – {{ __('List') }}</h5>
            @foreach($results as $result)
                <button
                    wire:click="saveResource('{{ $result['provider_id'] }}', '{{ $result['permalink'] ?: $result['url'] }}', '{{ json_encode($result, JSON_UNESCAPED_UNICODE) }}')"
                    type="submit"
                    class="btn btn-success btn-xs float-right"
                    title="{{ __("Save Ortsnamen ID") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>
                <h6>{{ $result['preferredName'] ?? '' }}
                    @if(!empty($result['types']) && is_array($result['types']))
                        <small class="text-muted">({{ implode(', ', $result['types']) }})</small>
                    @endif
                </h6>
                <small>
                    <a target="_blank" href="{{ $result['permalink'] ?: $result['url'] }}">{{ $result['permalink'] ?: $result['url'] }}</a><br>
                    ortsnamen-{{ $result['provider_id'] }}<br>
                    {{ $result['municipality'] ?? '' }}{{ $result['municipality'] && $result['canton'] ? ', ' : '' }}{{ $result['canton'] ?? '' }}
                    @if($result['lat'] && $result['lng'])
                        <br>Koordinaten: {{ $result['lat'] }}, {{ $result['lng'] }}
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
