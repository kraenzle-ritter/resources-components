<div class="border-l-4 border-purple-400 bg-purple-50 p-3 rounded-r">
    <div class="flex justify-between items-start">
        <div class="flex-1">
            <h4 class="font-medium text-gray-900 mb-1">
                @if(isset($result->title))
                    {{ $result->title }}
                @elseif(isset($result->name))
                    {{ $result->name }}
                @elseif(isset($result->label))
                    {{ $result->label }}
                @else
                    Anton Result
                @endif
            </h4>
            
            @if(isset($result->description))
                <p class="text-gray-700 text-sm mb-2">{{ $result->description }}</p>
            @endif
            
            @if(isset($result->type))
                <span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 text-xs rounded mr-2">
                    {{ $result->type }}
                </span>
            @endif
            
            @if(isset($result->dates))
                <span class="inline-block bg-blue-100 text-blue-700 px-2 py-1 text-xs rounded mr-2">
                    {{ $result->dates }}
                </span>
            @endif
            
            <div class="text-xs text-gray-500 mt-2">
                @if(isset($result->anton_instance_name))
                    {{ $result->anton_instance_name }}
                @elseif(isset($result->anton_instance))
                    Anton {{ $result->anton_instance }}
                @else
                    Anton
                @endif
                
                @if(isset($result->id))
                    • ID: {{ $result->id }}
                @endif
                
                @if(isset($result->created_at))
                    • Created: {{ $result->created_at }}
                @endif
            </div>
        </div>
        
        @if($model && method_exists($model, 'resources'))
            <button 
                wire:click="saveResource('{{ addslashes($result->title ?? $result->name ?? $result->label ?? 'Anton Result') }}', '{{ $result->url ?? '' }}', 'Anton ({{ $result->anton_instance ?? 'default' }})')"
                class="ml-4 px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition-colors"
                title="Save to resources"
            >
                Save
            </button>
        @endif
    </div>
</div>
