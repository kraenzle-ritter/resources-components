<div id="resources-list">
    @if(count($resources))
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Externe Links</h5>
                <ul class="list-group">
                @foreach($resources as $resource)
                    <li class="list-group-item">
                        <a target="_blank" href="{{ $resource->url }}">{{ $resource->provider }}</a>
                        @if($deleteButton)
                            <button
                                wire:click="removeResource('{{ $resource->id }}')"
                                type="submit"
                                class="btn btn-danger btn-xs float-right"
                                title="{{ __("Remove Resource") }}">
                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                            </button>
                        @endif
                    </li>
                @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
