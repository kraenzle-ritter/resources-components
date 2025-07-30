<form class="mb-2">
    <label class="form-label">{{ __('resources-components::messages.Search', ['provider' => $providerName ?? '']) }}</label>
    <input 
        wire:model.live="search" 
        class="form-control" 
        type="text" 
        placeholder="{{ $placeholder ?? '' }}"
        aria-label="{{ __('resources-components::messages.Search', ['provider' => $providerName ?? '']) }}">
</form>
