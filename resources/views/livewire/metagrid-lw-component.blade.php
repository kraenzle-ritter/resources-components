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
                <h5 class="card-title">{{ $result->provider ?? '' }}</h5>
                <button
                    wire:click="saveResource('{{ $result->id }}', '{{ $result->uri }}', '{{ json_encode($result, JSON_UNESCAPED_UNICODE) }}')"
                    type="submit"
                    class="btn btn-success btn-xs float-right"
                    title="{{ __("Save Metagrid Concordance for Actor") }}">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </button>
                @php
                    $name = $result->name;
                    $name = preg_replace('/^([^0-9]+)(\d{4}).*(\d{4}?).*$/', '${1} ($2-$3)', $name);
                    $name = preg_replace('/^([^0-9]+)(\d{4})-\d{2}-\d{2}$/', '${1} ($2)', $name);
                @endphp
                <a href="{{ $result->uri }}" target="_blank">{{ $name }}</a><br>
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
