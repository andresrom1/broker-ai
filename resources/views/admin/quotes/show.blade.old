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
                        <svg class="h-6 w-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <div class="bg-gray-100 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-900 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Año</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $request->vehicle_year ?? 'No especificado' }}</dd>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-900 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">GNC</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $request->fuel ? "Si":"No" }}</dd>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-900 p-4 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cobertura</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ ucwords(str_replace("_"," ",$request->coverage_type)) ?? 'No especificado' }}</dd>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-900 p-4 rounded-lg">
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
                            <svg class="h-6 w-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Alternativas de Cotización</h3>
                        </div>
                        <button type="button" id="addAlternative" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm w-full sm:w-auto">
                            + Agregar Alternativa
                        </button>
                    </div>

                    <form method="POST" action="/admin/quotes/{{ $request->id }}" id="alternativesForm">
                        @csrf
                        
                        <div id="alternativesContainer" class="space-y-6">
                            @for($i = 0; $i < 2; $i++)
                            <div class="alternative-card border border-gray-200 dark:border-gray-600 rounded-lg p-6 bg-gray-50 dark:bg-gray-700" data-index="{{ $i }}">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Alternativa {{ $i + 1 }}</h4>
                                    @if($i > 1)
                                    <button type="button" class="remove-alternative text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="company_{{ $i }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Compañía de Seguros *
                                        </label>
                                        <input type="text" 
                                               id="company_{{ $i }}"
                                               name="alternatives[{{ $i }}][company]" 
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
                                        <option value="responsabilidad_civil">Responsabilidad Civil</option>
                                        <option value="todo_riesgo_premium">Robo e Incendio</option>
                                        <option value="terceros_completo">Terceros Completo</option>
                                        <option value="todo_riesgo">Todo Riesgo</option>
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
                                              placeholder="Detalles adicionales, descuentos, condiciones especiales..."></textarea>
                                </div>
                            </div>
                            @endfor
                        </div>

                        <input type="hidden" name="quote_request_id" value="{{ $request->id }}">
                        
                        {{-- Buttons now stack on small screens and spread on larger ones --}}
                        <div class="mt-8 flex flex-col sm:flex-row justify-end space-y-4 sm:space-y-0 sm:space-x-4">
                            <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded w-full sm:w-auto">
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

    @push('scripts')
    <script>
        let alternativeCount = 2; // Initialize with the number of alternatives already rendered by Blade
        
        document.getElementById('addAlternative').addEventListener('click', function() {
            const container = document.getElementById('alternativesContainer');
            const newAlternative = createAlternativeCard(alternativeCount);
            container.appendChild(newAlternative);
            alternativeCount++;
            updateAlternativeNumbers(); // Update numbers after adding
        });
        
        function createAlternativeCard(index) {
            const div = document.createElement('div');
            div.className = 'alternative-card border border-gray-200 dark:border-gray-600 rounded-lg p-6 bg-gray-50 dark:bg-gray-700';
            div.setAttribute('data-index', index);
            
            div.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Alternativa ${index + 1}</h4>
                    <button type="button" class="remove-alternative text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Compañía de Seguros *</label>
                        <input type="text" name="alternatives[${index}][company]" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Ej: Mapfre, Sancor, etc." required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio Mensual *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                            <input type="number" name="alternatives[${index}][price]" class="w-full pl-8 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="0.00" step="0.01" required>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Cobertura *</label>
                    <select name="alternatives[${index}][coverage]" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                        <option value="">Seleccionar cobertura</option>
                        <option value="responsabilidad_civil">Responsabilidad Civil</option>
                        <option value="terceros_completo">Terceros Completo</option>
                        <option value="todo_riesgo">Todo Riesgo</option>
                        <option value="todo_riesgo_premium">Todo Riesgo Premium</option>
                    </select>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                    <textarea name="alternatives[${index}][observations]" rows="3" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Detalles adicionales, descuentos, condiciones especiales..."></textarea>
                </div>
            `;
            
            // Add event listener to the remove button
            div.querySelector('.remove-alternative').addEventListener('click', function() {
                div.remove();
                updateAlternativeNumbers();
            });
            
            return div;
        }
        
        // Event listeners for existing remove buttons (initial render)
        // This attaches event listeners to all remove buttons currently in the DOM.
        document.querySelectorAll('.remove-alternative').forEach(button => {
            button.addEventListener('click', function(e) {
                e.target.closest('.alternative-card').remove();
                updateAlternativeNumbers();
            });
        });

        // Ensure that the initial alternative count matches the number of rendered alternatives
        // This is important if $request->alternatives is populated and you loop through it
        // for rendering initial cards instead of just hardcoding 2.
        alternativeCount = document.querySelectorAll('.alternative-card').length;
        
        function updateAlternativeNumbers() {
            const cards = document.querySelectorAll('.alternative-card');
            cards.forEach((card, index) => {
                const title = card.querySelector('h4');
                title.textContent = `Alternativa ${index + 1}`;
                // Update the 'name' attributes for inputs and selects to maintain correct array indexing
                card.querySelectorAll('input, select, textarea').forEach(input => {
                    const originalName = input.name;
                    if (originalName && originalName.startsWith('alternatives[')) {
                        input.name = `alternatives[${index}]` + originalName.substring(originalName.indexOf(']'));
                        input.id = originalName.substring(0, originalName.indexOf('_')) + `_${index}`; // Update ID for labels
                    }
                });
                card.setAttribute('data-index', index); // Also update data-index
            });
            // After removing, ensure alternativeCount is accurate for adding new ones
            alternativeCount = cards.length;
        }
    </script>
    @endpush
</x-app-layout>
