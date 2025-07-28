<div class="multi-language-wikipedia-component">
    <div class="mb-4">
        <div class="flex flex-wrap items-center gap-4 mb-4">
            <!-- Language Selection -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Languages:</label>
                <div class="flex flex-wrap gap-1">
                    @foreach($availableLanguages as $code => $name)
                        <button 
                            wire:click="toggleLanguage('{{ $code }}')"
                            class="px-2 py-1 text-xs rounded-full border {{ in_array($code, $selectedLanguages) ? 'bg-blue-500 text-white border-blue-500' : 'bg-gray-100 text-gray-700 border-gray-300' }}"
                            title="{{ $name }}"
                        >
                            {{ $code }}
                        </button>
                    @endforeach
                </div>
            </div>
            
            <!-- Search All Languages Toggle -->
            <div class="flex items-center gap-2">
                <input 
                    type="checkbox" 
                    wire:model="searchAllLanguages" 
                    wire:change="toggleSearchAllLanguages"
                    id="searchAllLanguages"
                    class="rounded border-gray-300"
                >
                <label for="searchAllLanguages" class="text-sm text-gray-700">Search all languages</label>
            </div>
        </div>

        <!-- Current Search Info -->
        @if(!empty($search))
            <div class="text-sm text-gray-600 mb-2">
                Searching for "<strong>{{ $search }}</strong>" in 
                @if($searchAllLanguages)
                    <strong>all languages</strong>
                @else
                    <strong>{{ implode(', ', $selectedLanguages) }}</strong>
                @endif
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
            <p class="text-sm text-red-600 mt-1">Please try a different search term or contact support if the problem persists.</p>
        </div>
    @endif

    <!-- Results Display -->
    @if(!empty($results))
        <div class="space-y-6">
            @foreach($results as $language => $languageResults)
                @if(!empty($languageResults))
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm mr-2">
                                {{ $language }}
                            </span>
                            {{ $availableLanguages[$language] ?? $language }}
                            <span class="ml-2 text-sm text-gray-500">({{ count($languageResults) }} results)</span>
                        </h3>
                        
                        <div class="space-y-3">
                            @foreach($languageResults as $result)
                                <div class="border-l-4 border-blue-400 bg-blue-50 p-3 rounded-r">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900 mb-1">
                                                <a href="{{ $result->url ?? '#' }}" 
                                                   target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ $result->title }}
                                                </a>
                                            </h4>
                                            @if(isset($result->snippet))
                                                <p class="text-gray-700 text-sm mb-2">
                                                    {!! $result->snippet !!}
                                                </p>
                                            @endif
                                            <div class="text-xs text-gray-500">
                                                Wikipedia {{ $result->language_name ?? $result->language }}
                                                @if(isset($result->size))
                                                    • {{ number_format($result->size) }} bytes
                                                @endif
                                                @if(isset($result->wordcount))
                                                    • {{ number_format($result->wordcount) }} words
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($model && method_exists($model, 'resources'))
                                            <button 
                                                wire:click="saveResource('{{ addslashes($result->title) }}', '{{ $result->url ?? '' }}', 'Wikipedia ({{ $language }})')"
                                                class="ml-4 px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition-colors"
                                                title="Save to resources"
                                            >
                                                Save
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @elseif(!empty($search) && !$hasError)
        <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <p>No Wikipedia articles found for "{{ $search }}" in the selected language(s).</p>
            <p class="text-sm mt-1">Try different search terms or select additional languages.</p>
        </div>
    @endif
</div>
