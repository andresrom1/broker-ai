<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-public-layout>
        <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">
            <div class="w-full max-w-md bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Gestión de OpenAI Assistant</h2>
                
                <form method="POST" action="{{ route('kill.handle') }}" class="space-y-4">
                    @csrf
                    
                    <!-- Thread ID Input -->
                    <div>
                        <label for="thread_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Thread ID
                        </label>
                        <input 
                            type="text" 
                            id="thread_id" 
                            name="thread_id" 
                            required
                            placeholder="Ej: thread_JKNq4yibOZDy53lplWLQQH5e"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
    
                    <!-- Run ID Input -->
                    <div>
                        <label for="run_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Run ID
                        </label>
                        <input 
                            type="text" 
                            id="run_id" 
                            name="run_id" 
                            required
                            placeholder="Ej: run_NYmbPjWAU5qq3WMc5GMVaFGX"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
    
                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Gestionar Run
                    </button>
                </form>
    
                <!-- Mensajes de Estado -->
                @if(session('message'))
                    <div class="mt-4 p-3 rounded-md {{ session('success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ session('message') }}
                    </div>
                @endif
            </div>
        </div>
    </x-public-layout>
</x-app-layout>