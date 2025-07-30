<button
    wire:click="{{ $saveAction }}"
    type="button"
    class="btn btn-success btn-sm"
    title="{{ __('resources-components::messages.Save resource', ['provider' => $providerName ?? '']) }}">
    <i class="fas fa-check" aria-hidden="true"></i>
</button>
