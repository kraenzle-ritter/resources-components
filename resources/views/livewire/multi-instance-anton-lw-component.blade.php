<div class="multi-instance-anton-component">
    <div class="mb-4">
        <div class="flex flex-wrap items-center gap-4 mb-4">
            <!-- Instance Selection -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Anton Instance:</label>
                <select 
                    wire:model="selectedInstance" 
                    class="text-sm border border-gray-300 rounded px-2 py-1"
                >
                    @foreach($availableInstances as $instanceKey => $instanceConfig)
                        @if($instanceConfig['enabled'] ?? true)
                            <option value="{{ $instanceKey }}">{{ $instanceConfig['name'] ?? $instanceKey }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            
            <!-- Endpoint Selection -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Endpoint:</label>
                <select 
                    wire:model="endpoint" 
                    class="text-sm border border-gray-300 rounded px-2 py-1"
                >
                    @foreach($availableEndpoints as $endpointKey => $endpointName)
                        <option value="{{ $endpointKey }}">{{ $endpointName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search All Instances Toggle -->
            <div class="flex items-center gap-2">
                <input 
                    type="checkbox" 
                    wire:model="searchAllInstances" 
                    wire:change="toggleSearchAllInstances"
                    id="searchAllInstances"
                    class="rounded border-gray-300"
                >
                <label for="searchAllInstances" class="text-sm text-gray-700">Search all instances</label>
            </div>
        </div>

        <!-- Instance Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-2 mb-4">
            @foreach($instanceStatus as $instanceKey => $status)
                <div class="flex items-center gap-2 text-xs p-2 rounded {{ $status['enabled'] ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }}">
                    <div class="w-2 h-2 rounded-full {{ $status['enabled'] && $status['has_token'] && $status['has_url'] ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <span class="font-medium {{ $selectedInstance === $instanceKey ? 'text-blue-600' : 'text-gray-700' }}">
                        {{ $status['name'] }}
                    </span>
                </div>
            @endforeach
        </div>

        <!-- Current Search Info -->
        @if(!empty($search))
            <div class="text-sm text-gray-600 mb-2">
                Searching for "<strong>{{ $search }}</strong>" in 
                @if($searchAllInstances)
                    <strong>all instances</strong>
                @else
                    <strong>{{ $availableInstances[$selectedInstance]['name'] ?? $selectedInstance }}</strong>
                @endif
                using <strong>{{ $availableEndpoints[$endpoint] ?? $endpoint }}</strong> endpoint
            </div>
        @endif
    </div>

    <!-- Error Display -->
    @if($hasError)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <strong class="text-red-800">Search Error:</strong>
            </div>
            <p class="text-red-700 mt-1">{{ $errorMessage }}</p>
            <p class="text-sm text-red-600 mt-1">Please check your API configuration or try again later.</p>
        </div>
    @endif

    <!-- Results Display -->
    @if(!empty($results))
        @if($searchAllInstances && is_array($results) && isset(array_values($results)[0]) && is_array(array_values($results)[0]))
            <!-- Grouped by instance -->
            <div class="space-y-6">
                @foreach($results as $instance => $instanceResults)
                    @if(!empty($instanceResults))
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-sm mr-2">
                                    {{ $instance }}
                                </span>
                                {{ $availableInstances[$instance]['name'] ?? $instance }}
                                <span class="ml-2 text-sm text-gray-500">({{ count($instanceResults) }} results)</span>
                            </h3>
                            
                            <div class="space-y-3">
                                @foreach($instanceResults as $result)
                                    @include('resources-components::livewire.partials.anton-result-item', ['result' => $result])
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <!-- Single instance results -->
            <div class="space-y-3">
                @foreach($results as $result)
                    @include('resources-components::livewire.partials.anton-result-item', ['result' => $result])
                @endforeach
            </div>
        @endif
    @elseif(!empty($search) && !$hasError)
        <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <p>No results found for "{{ $search }}" in the selected Anton instance(s).</p>
            <p class="text-sm mt-1">Try different search terms or check a different instance.</p>
        </div>
    @endif
</div>
