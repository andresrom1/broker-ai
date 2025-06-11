<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Peticiones de Cotización') }}
            </h2>
            <div class="flex items-center space-x-4">
                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-medium px-2.5 py-0.5 rounded-full">
                    {{ $requests->count() }} {{ $requests->count() === 1 ? 'petición' : 'peticiones' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if($requests->isEmpty())
                        <div class="text-center py-12">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No hay peticiones de cotización</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comienza creando una nueva petición de cotización.</p>
                        </div>
                    @else
                        <!-- Filtros y búsqueda -->
                        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                            <div class="flex items-center space-x-4">
                                <select class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option>Todos los estados</option>
                                    <option>Pendiente</option>
                                    <option>En proceso</option>
                                    <option>Completado</option>
                                </select>
                                <input type="search" placeholder="Buscar por marca o modelo..." class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            </div>
                        </div>

                        <!-- Lista de peticiones -->
                        <div class="space-y-4">
                            @foreach($requests as $request)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-4">
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                    <a href="{{ route('quotes.show', $request->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                                        {{ $request->vehicle_brand }} {{ $request->vehicle_model }}
                                                    </a>
                                                </h3>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200">
                                                    {{ $request->vehicle_year }}
                                                </span>
                                                @if(isset($request->quoted))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                        @if($request->quoted === 0) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                        @elseif($request->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                        @elseif($request->quoted === 1 ) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                                        @endif">
                                                        {{ ucfirst($request->quoted ? 'Cotizada':'Pendiente') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400 space-x-4">
                                                <span class="flex items-center">
                                                    <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                    </svg>
                                                    ID: #{{ $request->id }}
                                                </span>
                                                @if(isset($request->customer_name))
                                                    <span class="flex items-center">
                                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                        {{ $request->customer_name }}
                                                    </span>
                                                @endif
                                                <span class="flex items-center">
                                                    <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ $request->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('quotes.show', $request->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a {{--href="{{ route('admin.quotes.edit', $request->id) }}"--}} class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Paginación -->
                        @if(method_exists($requests, 'links'))
                            <div class="mt-6">
                                {{ $requests->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
{{-- <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="container">
        <h1>Peticiones de cotización</h1>
        <ul>
            @foreach($requests as $r)
            <li><a href="/admin/quotes/{{ $r->id }}">({{ $r->id }}) {{ $r->vehicle_brand }} {{ $r->vehicle_model }} ({{ $r->vehicle_year }})</a></li>
            @endforeach
        </ul>
    </div>
</x-app-layout> --}}