<div class="px-3 py-2">
    @if(!in_array($providerKey, $model->resources->pluck('provider')->toArray()))
        <div>
            @include('resources-components::livewire.partials.search-form', [
                'providerName' => $providerName ?? $providerKey
            ])

            @if($results && count($results))
                <h5 class="mb-3">{{ $providerName ?? $providerKey }} – {{ __('resources-components::messages.List') }}</h5>
                <div class="results-list">
                    @foreach($results as $result)
                        <div class="result-item mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                @if(isset($result_heading) && $result_heading($result))
                                    <h6 class="mb-1">{!! $result_heading($result) !!}</h6>
                                @endif
                                
                                <div class="ms-auto">
                                    @include('resources-components::livewire.partials.save-button', [
                                        'saveAction' => $saveAction($result),
                                        'providerName' => $providerName ?? $providerKey
                                    ])
                                </div>
                            </div>

                            <div class="small">
                                {!! $result_content($result) !!}
                            </div>

                            @if (!$loop->last)
                                <hr class="my-3">
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info mt-3">
                    {{ __('resources-components::messages.No matches') }}
                </div>
            @endif
        </div>
    @endif
</div>
