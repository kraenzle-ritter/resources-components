<div id="provider-select" class="card my-3">
    @if($providers)
        <div class="card-header py-2">
            <h5 class="mb-0">{{ __('resources-components::messages.New links') }}</h5>
        </div>
        <div>
            <form class="px-3 py-2">
                <div class="mb-3">
                    <label for="provider-select" class="form-label">{{ __('resources-components::messages.Provider') }}</label>
                    <select wire:change="setProvider($event.target.value)" class="form-select" id="provider-select" aria-label="{{ __('resources-components::messages.Provider') }}">
                        @foreach($providers as $value)
                            <option value="{{ $value }}" {{ ($providerKey == $value) ? 'selected' : '' }}>
                                {{-- Get the label from the new config structure --}}
                                {{ config('resources-components.providers.' . $value . '.label', ucfirst($value)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
            @if($componentToRender)
                @livewire(
                    $componentToRender,
                    $componentParams,
                    key($providerKey)
                )
            @else
                <div class="p-3">
                    <div class="alert alert-info">
                        {{ __('resources-components::messages.No provider selected or all available resources already linked') }}
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
