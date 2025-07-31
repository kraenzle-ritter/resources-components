<div class="px-3 py-2">
    @if(config('app.debug'))
        <div class="debug-info small text-muted mb-2">
            Provider: {{ $providerKey }}, 
            Results: {{ is_countable($results) ? count($results) : '0' }}, 
            Limit: {{ config("resources-components.providers.{$providerKey}.limit") ?? config('resources-components.limit') ?? 5 }}, 
            ShowAll: {{ empty($showAll) ? 'false' : 'true' }}
            <button wire:click="debugComponent" class="btn btn-sm btn-link p-0 ms-2">Debug</button>
            
            @if(is_countable($results) && count($results) >= (config("resources-components.providers.{$providerKey}.limit") ?? config('resources-components.limit') ?? 5))
                <button wire:click="showAllResults" class="btn btn-sm btn-link p-0 ms-2">Force Show All</button>
            @endif
        </div>
    @endif
    
    @if(!in_array($providerKey, $model->resources->pluck('provider')->toArray()))
        <div>
            @include('resources-components::livewire.partials.search-form', [
                'providerName' => $providerName ?? $providerKey
            ])

            @if($results && count($results))
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">{{ __('resources-components::messages.List') }}</h5>
                    
                    @php
                        $configLimit = config("resources-components.providers.{$providerKey}.limit") ?? config('resources-components.limit') ?? 5;
                        $hasMore = count($results) >= $configLimit && empty($showAll);
                        
                        // Debug-Ausgabe, wenn im Debug-Modus
                        if (config('app.debug')) {
                            Log::debug("Button-Debug für {$providerKey}: limit={$configLimit}, count=" . count($results) . ", showAll=" . (empty($showAll) ? 'false' : 'true') . ", hasMore={$hasMore}");
                        }
                    @endphp
                    
                    @if($hasMore)
                        <button wire:click="showAllResults" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-list me-1"></i> {{ __('resources-components::messages.Show All') }}
                        </button>
                    @endif
                </div>
                
                <div class="results-list">
                    @foreach($results as $result)
                        <div class="result-item mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                @if(isset($result_heading) && $result_heading($result))
                                    <h6 class="mb-0 fw-bold">{!! $result_heading($result) !!}</h6>
                                @endif

                                <div class="ms-2">
                                    @include('resources-components::livewire.partials.save-button', [
                                        'saveAction' => $saveAction($result),
                                        'providerName' => $providerName ?? $providerKey
                                    ])
                                </div>
                            </div>

                            <div class="mt-1 small">
                                {!! $result_content($result) !!}
                            </div>

                            @if (!$loop->last)
                                <hr class="my-3">
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert {{ isset($apiLimitReached) && $apiLimitReached ? 'alert-warning' : 'alert-info' }} mt-3">
                    @if(isset($apiLimitReached) && $apiLimitReached)
                        <strong>{{ __('resources-components::messages.API Limit Reached') }}:</strong> {{ __('resources-components::messages.API Limit Message') }}
                        <a href="https://www.geonames.org/login" target="_blank">{{ __('Register for a free account') }}</a>
                        {{ __('and set the username in your .env file') }}: <code>GEONAMES_USERNAME=your_username</code>
                    @else
                        {{ __('resources-components::messages.No matches') }}
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
