<div id="resources-list">
    <!-- this must be within the div since livewire accepts only one root tag per component -->
    <style>
        .btn-group-xs > .btn, .btn-xs {
            padding: 1px 5px;
            font-size: 12px;
            line-height: 1.5;
            border-radius: 3px;
        }
    </style>
    @if(count($resources))
        <div class="card">
            <div class="card-header">
                <h5>Externe Links</h5>
            </div>

            <ul class="list-group  list-group-flush">
                @foreach($resources as $resource)
                    <li class="list-group-item {{ $resource->provider}}">
                        <a target="_blank" href="{{ $resource->url }}">{{ $resource->provider }}</a>
                        @if($deleteButton)
                            <button
                                wire:click="removeResource({{ $resource->id }})"
                                class="btn btn-danger btn-xs float-right"
                                title="{{ __("Remove Resource") }}">
                                <i class="fas fa-trash" aria-hidden="true"></i>
                            </button>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
