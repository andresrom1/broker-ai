<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Solicitud de Cotización #{{ $request->id }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Creada {{ $request->created_at->format('d/m/Y H:i') }} | Hace: {{ $request->created_at->diffForHumans() }}
                </p>
            </div>
            {{-- Flex container for status and back button, allowing wrapping on small screens --}}
            <div class="flex items-center space-x-3 mt-4 sm:mt-0 flex-wrap justify-end">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($request->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                    @elseif($request->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                    @elseif($request->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                    @endif">
                    {{ ucfirst($request->status ?? 'pending') }}
                </span>
                <a href="{{ route('quotes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Información del Vehículo -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Información del Vehículo</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-100 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-900 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Marca y Modelo</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $request->vehicle_brand }} {{ $request->vehicle_model }}</dd>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-900 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Versión</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $request->vehicle_version }}</dd>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 border-2 dark:border-gray-900 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Año</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $request->vehicle_year ?? 'No especificado' }}</dd>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 border-2 dark:border-gray-900 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">GNC</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $request->vehicle_fuel ? "Sí":"No" }}</dd>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 border-2 dark:border-gray-900 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cobertura</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ ucwords(str_replace("_"," ",$request->coverage_type)) ?? 'No especificado' }}</dd>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 border-2 dark:border-gray-900 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Código Postal</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $request->vehicle_postal_code}}</dd>
                        </div>
                    </div>

                    @if(isset($request->customer_name) || isset($request->customer_email))
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Información del Cliente</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if(isset($request->customer_name))
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->customer_name }}</dd>
                            </div>
                            @endif
                            @if(isset($request->customer_email))
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->customer_email }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Formulario de Alternativas -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                        <div class="flex items-center mb-4 sm:mb-0">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Alternativas de Cotización</h3>
                        </div>
                        {{-- El botón "Agregar Alternativa" ha sido removido ya que su funcionalidad dependía de JavaScript. --}}
                    </div>

                    {{-- Añadir enctype para subida de archivos --}}
                    <form method="POST" enctype="multipart/form-data" action="/admin/quotes/{{ $request->id }}" id="alternativesForm">
                        @csrf
                        
                        <div id="alternativesContainer" class="space-y-6">
                            @php
                                // Obtener las alternativas existentes
                                $existingAlternatives = $request->alternatives;
                                // Definir un mínimo de campos vacíos para nuevas entradas
                                $minEmptyFields = 2;
                                // Determinar cuántos campos de alternativa debemos mostrar en total
                                // Esto será el máximo entre el número de alternativas existentes y el mínimo de campos vacíos.
                                $totalFieldsToShow = max($existingAlternatives->count(), $minEmptyFields);
                            @endphp

                            @for($i = 0; $i < $totalFieldsToShow; $i++)
                                @php
                                    // Obtener la alternativa existente si está disponible, de lo contrario será null
                                    $alternative = $existingAlternatives->get($i);
                                @endphp
                                <div class="alternative-card border border-gray-200 dark:border-gray-600 rounded-lg p-6 bg-gray-50 dark:bg-gray-700" data-index="{{ $i }}">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Alternativa {{ $i + 1 }}</h4>
                                        {{-- El botón de remover alternativa ha sido removido. Para eliminar, necesitarías una recarga de página o JS. --}}
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="company_{{ $i }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Compañía de Seguros *
                                            </label>
                                            <input type="text" 
                                                id="company_{{ $i }}"
                                                name="alternatives[{{ $i }}][company]" 
                                                value="{{ old('alternatives.'.$i.'.company', $alternative->company ?? '') }}"
                                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                placeholder="Ej: Mapfre, Sancor, etc."
                                                required>
                                        </div>
                                        
                                        <div>
                                            <label for="price_{{ $i }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Precio Mensual *
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                                                <input type="number" 
                                                    id="price_{{ $i }}"
                                                    name="alternatives[{{ $i }}][price]" 
                                                    value="{{ old('alternatives.'.$i.'.price', $alternative->price ?? '') }}"
                                                    class="w-full pl-8 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                    required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <label for="coverage_{{ $i }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Tipo de Cobertura *
                                        </label>
                                        <select id="coverage_{{ $i }}"
                                                name="alternatives[{{ $i }}][coverage]" 
                                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                required>
                                            <option value="">Seleccionar cobertura</option>
                                            <option value="responsabilidad_civil" {{ (old('alternatives.'.$i.'.coverage', $alternative->coverage ?? '') == 'responsabilidad_civil') ? 'selected' : '' }}>Responsabilidad Civil</option>
                                            <option value="terceros_completo" {{ (old('alternatives.'.$i.'.coverage', $alternative->coverage ?? '') == 'terceros_completo') ? 'selected' : '' }}>Terceros Completo</option>
                                            <option value="todo_riesgo" {{ (old('alternatives.'.$i.'.coverage', $alternative->coverage ?? '') == 'todo_riesgo') ? 'selected' : '' }}>Todo Riesgo</option>
                                            <option value="todo_riesgo_premium" {{ (old('alternatives.'.$i.'.coverage', $alternative->coverage ?? '') == 'todo_riesgo_premium') ? 'selected' : '' }}>Todo Riesgo Premium</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <label for="observations_{{ $i }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Observaciones
                                        </label>
                                        <textarea id="observations_{{ $i }}"
                                                name="alternatives[{{ $i }}][observations]" 
                                                rows="3"
                                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                placeholder="Detalles adicionales, descuentos, condiciones especiales...">{{ old('alternatives.'.$i.'.observations', $alternative->observations ?? '') }}</textarea>
                                    </div>

                                    {{-- Campo de subida de archivo PDF --}}
                                    <div class="mb-3 mt-4">
                                        <label for="alternatives_{{ $i }}_pdf_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adjuntar PDF (Opcional):</label>
                                        <input type="file" name="alternatives[{{ $i }}][pdf_file]" id="alternatives_{{ $i }}_pdf_file" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-100 dark:hover:file:bg-blue-800">
                                        @if($alternative && $alternative->attachments->isNotEmpty())
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Adjuntos actuales:</p>
                                            <ul class="list-disc ml-5">
                                                @foreach($alternative->attachments as $attachment)
                                                    <li><a href="{{ asset($attachment->file_url) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $attachment->file_name }}</a></li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <input type="hidden" name="quote_request_id" value="{{ $request->id }}">
                        
                        <div class="mt-8 flex flex-col sm:flex-row justify-end space-y-4 sm:space-y-0 sm:space-x-4">
                            <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded w-full sm:w-auto dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-gray-200">
                                Guardar como Borrador
                            </button>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded w-full sm:w-auto">
                                Enviar Cotización
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
