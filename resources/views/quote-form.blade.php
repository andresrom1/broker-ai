<x-public-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    
    <div class="min-h-screen flex">
        <!-- Columna izquierda: chat -->
        <div class="w-1/2 p-6">
          <chat-widget />
        </div>

        <!-- Columna derecha: espacio para imágenes, SEO, links, etc. -->
        <div class="hidden md:block w-1/2 p-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-r-2xl">
          <div class="h-full flex flex-col justify-center">
            <h2 class="text-2xl font-bold text-indigo-800 mb-6">¿Por qué elegir nuestro Broker Virtual?</h2>
        
            <div class="space-y-5 mb-8">
              <div class="flex items-start">
                <div class="bg-indigo-100 w-10 h-10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <i class="fas fa-bolt text-indigo-600"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-indigo-700">Cotización instantánea</h3>
                  <p class="text-gray-600">Obtené precios en tiempo real sin trámites engorrosos</p>
                </div>
              </div>
        
              <div class="flex items-start">
                <div class="bg-indigo-100 w-10 h-10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <i class="fas fa-headset text-indigo-600"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-indigo-700">Soporte humano 24/7</h3>
                  <p class="text-gray-600">Contás con asesoría profesional cuando lo necesites</p>
                </div>
              </div>
        
              <div class="flex items-start">
                <div class="bg-indigo-100 w-10 h-10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <i class="fas fa-chart-line text-indigo-600"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-indigo-700">Comparación inteligente</h3>
                  <p class="text-gray-600">Encontrá las mejores opciones del mercado en segundos</p>
                </div>
              </div>
            </div>
        
            <div class="bg-white p-5 rounded-xl shadow-sm border border-indigo-100">
              <div class="flex items-center mb-3">
                <div class="bg-green-100 w-8 h-8 rounded-full flex items-center justify-center mr-2">
                  <i class="fas fa-star text-green-600"></i>
                </div>
                <h3 class="font-bold text-gray-700">Clientes satisfechos</h3>
              </div>
              <p class="text-gray-600 mb-4">"Gracias al Broker Virtual ahorré un 30% en mi seguro de auto con la misma
                cobertura. ¡Increíble experiencia!"</p>
              <div class="text-sm text-gray-500 flex items-center">
                <div class="bg-gray-200 border-2 border-dashed rounded-xl w-10 h-10 mr-2"></div>
                <div>
                  <div class="font-medium">María López</div>
                  <div>Cliente desde 2021</div>
                </div>
              </div>
            </div>
        
            <div class="mt-6 flex justify-center space-x-4">
              <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition-colors">
                <i class="fas fa-file-alt mr-2"></i>Solicitar cotización
              </button>
              <button
                class="px-4 py-2 border border-indigo-600 text-indigo-600 rounded-lg text-sm hover:bg-indigo-50 transition-colors">
                <i class="fas fa-question-circle mr-2"></i>Preguntas frecuentes
              </button>
            </div>
          </div>
        </div>
 


    
        {{-- <!-- Columna derecha: espacio para imágenes, SEO, links, etc. -->
        <div class="w-1/2 p-6 bg-gray-50">
          <!-- Aquí va tu contenido estático o dinámico -->
          <h2 class="text-xl font-semibold mb-4">¿Por qué elegirnos?</h2>
          <p class="mb-4">
            Con nuestro Broker Virtual ahorrás tiempo: cotizá al instante sin trámites engorrosos.
          </p>
          <ul class="list-disc pl-5 space-y-2">
            <li>Disponibilidad 24/7</li>
            <li>Asesoría humana post-venta</li>
            <li>Compará las mejores opciones en segundos</li>
          </ul>
          <!-- Podés agregar aquí imágenes, badges, links a FAQs, etc. -->
        </div> --}}
    </div>
</x-public-layout>