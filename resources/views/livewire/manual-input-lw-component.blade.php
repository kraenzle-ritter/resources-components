<div class="p-2">
@if(!in_array('manual-input', $model->resources->pluck('provider')->toArray()))
    <div>
        <h5>Manual input</h5>
        <form class="form manual-input-form">
            <div class="form-group">
                <label for="title" title="The Name of the Provider: eg. Wikipedia (en).">Provider</label>
                <input type="text" wire:model.live="provider" class="form-control" id="provider">
                @error('provider') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="title" title="The ID which is related to the provider. Most likely the last part of the URL.">Provider ID</label>
                <input type="text" wire:model.live="provider_id" class="form-control" id="providerName">
                @error('provider_id') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="title" title="Link to the resource.">URL</label>
                <input type="text" wire:model.live="url" class="form-control" id="providerName">
                @error('url') <span class="error">{{ $message }}</span> @enderror
            </div>
            <button
                wire:click="saveResource()"
                type="submit"
                class="btn btn-success btn-xs float-right"
                title="{{ __("Save resource") }}">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
        </form>
        <br>
    </div>
@endif
</div>
