<template>
  <div class="chat-widget">
    <!-- Encabezado del chat con un diseño renovado -->
    <div class="chat-header">
      <div class="flex items-center space-x-3">
        <div class="bot-avatar">
          <!-- Icono de robot moderno y amigable -->
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C13.1 2 14 2.9 14 4V5H10V4C10 2.9 10.9 2 12 2M12 8C16.42 8 20 11.58 20 16C20 20.42 16.42 24 12 24C7.58 24 4 20.42 4 16C4 11.58 7.58 8 12 8M12 10C8.69 10 6 12.69 6 16C6 19.31 8.69 22 12 22C15.31 22 18 19.31 18 16C18 12.69 15.31 10 12 10M9 14C9.55 14 10 14.45 10 15C10 15.55 9.55 16 9 16C8.45 16 8 15.55 8 15C8 14.45 8.45 14 9 14M15 14C15.55 14 16 14.45 16 15C16 15.55 15.55 16 15 16C14.45 16 14 15.55 14 15C14 14.45 14.45 14 15 14M12 17C13.1 17 14 17.9 14 19H10C10 17.9 10.9 17 12 17Z"/>
          </svg>
        </div>
        <div>
          <h3 class="chat-title">Broker Virtual</h3>
        </div>
      </div>
      <div class="status-dot"></div>
    </div>
    
    <!-- Área de mensajes -->
    <div ref="messageContainer" class="h-96 overflow-y-auto border border-gray-300 dark:border-gray-700 p-3 rounded-md mb-4 bg-gray-50 dark:bg-gray-700 messages-area">
      <div v-for="(message, index) in messages" :key="index"
        :class="['message-wrapper', message.role === 'user' ? 'user-message' : 'bot-message']">
        
        <!-- Contenedor interno para las burbujas de mensaje -->
        <div class="inline-block my-1 relative">
          <!-- Usamos <template> para agrupar las condiciones y asegurar la adyacencia -->
          <template v-if="message.meta_data && message.meta_data.type === 'quote_alternatives'">
            <!-- Si es una tarjeta de alternativas de cotización -->
            <div class="message-bubble bot-bubble text-left p-3 rounded-md shadow-sm bg-green-100 dark:bg-green-700 border border-green-300 dark:border-green-600 text-green-800 dark:text-green-100"
                 style="max-width: 80%;">
              <h3 class="font-semibold text-green-700 dark:text-green-200 mb-2">¡Tu cotización está lista!</h3>
              <p class="text-green-800 dark:text-green-100 mb-3" v-html="message.content"></p> 
              
              <div v-for="(alt, altIndex) in message.meta_data.data" :key="altIndex" class="mb-2 p-2 bg-green-50 dark:bg-green-600 rounded-md shadow-sm">
                <p><strong>Opción {{ altIndex + 1 }}:</strong></p>
                <p>Compañía: {{ alt.company }}</p>
                <p>Cobertura: {{ alt.coverage }}</p>
                <p>Precio Mensual: ${{ alt.price }}</p>
                <p v-if="alt.observations">Observaciones: {{ alt.observations }}</p>

                <!-- Mostrar adjuntos anidados si existen (dentro de la tarjeta de cotización) -->
                <div v-if="alt.attachments && alt.attachments.length > 0" class="mt-2 text-sm">
                  <p class="font-semibold text-green-700 dark:text-green-200">Documentos de Alternativa:</p>
                  <ul class="list-disc ml-4">
                    <li v-for="(attachment, attIndex) in alt.attachments" :key="attIndex">
                      <a :href="attachment.file_url" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                        <svg class="inline-block w-4 h-4 mr-1 -mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V7.414L10.586 4H6zM10 10a1 1 0 011 1v3a1 1 0 11-2 0v-3a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                        {{ attachment.file_name }}
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <p class="text-green-800 dark:text-green-100 mt-3">Si tenés alguna pregunta o querés proceder con alguna de estas opciones, no dudes en consultarnos.</p>
            </div>
          </template>

          <template v-else-if="message.meta_data && message.meta_data.type === 'single_attachment'">
            <!-- Si es un link de adjunto individual (con estilo especial) -->
            <div class="message-bubble bot-bubble text-left p-3 rounded-lg shadow-md bg-blue-100 dark:bg-blue-700 border border-blue-300 dark:border-blue-600 text-blue-800 dark:text-blue-100 flex items-center space-x-2"
                 style="max-width: 80%;">
              <svg class="w-6 h-6 flex-shrink-0 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V7.414L10.586 4H6zM10 10a1 1 0 011 1v3a1 1 0 11-2 0v-3a1 1 0 011-1z" clip-rule="evenodd"></path>
              </svg>
              <div>
                <p class="font-semibold text-blue-800 dark:text-blue-100" v-html="message.content"></p>
                <a :href="message.meta_data.data.file_url" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-sm break-all">
                  {{ message.meta_data.data.file_name }}
                </a>
              </div>
            </div>
          </template>

          <template v-else>
            <!-- Si es un mensaje de texto normal -->
            <div class="message-bubble" 
                 :class="[
                    message.role === 'user' ? 'user-bubble' : 'bot-bubble',
                    'inline-block px-4 py-2 rounded-lg relative pr-10' // Original Tailwind classes for normal bubbles
                  ]">
              <div class="message-text" v-html="message.content"></div>
              <!-- Added time bubble -->
              <span :class="[
                'absolute text-xs mt-1',
                message.role === 'user' ? 'bottom-1 right-2 text-blue-200' : 'bottom-1 right-2 text-gray-500 dark:text-gray-300'
              ]">
                <!-- Esto es un marcador de posición, deberías usar la hora real del mensaje si está disponible -->
                {{ new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
              </span>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Área de entrada de texto -->
    <div class="flex">
      <input 
        type="text" 
        v-model="input" 
        @keyup.enter="sendMessage" 
        placeholder="Escribe tu mensaje..." 
        class="flex-1 p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <button 
        @click="sendMessage" 
        class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        Enviar
      </button>
    </div>
  </div>
</template>



<script setup>
import { ref, nextTick, onMounted, onUnmounted, onBeforeUnmount } from 'vue'
import axios from 'axios'

const messages = ref([])
const input = ref('')
const loading = ref(false)
const messagesContainer = ref(null)
const sessionId = ref(localStorage.getItem('session_id') || generateSessionId())

function generateSessionId() {
  const id = Date.now().toString(36) + Math.random().toString(36).substring(2)
  localStorage.setItem('session_id', id)
  return id
}

function appendMessage(from, text) {
  console.log(from,text);
  messages.value.push({ from, text, timestamp: new Date() })
  scrollToBottom()
}

async function scrollToBottom() {
  await nextTick()
  const container = messagesContainer.value
  if (container) {
    container.scrollTop = container.scrollHeight
  }
}

async function handleInput() {
  const text = input.value.trim()
  if (!text || loading.value) return
  
  appendMessage('user', text)
  input.value = ''
  loading.value = true

  try {
    const res = await axios.post('/api/chat/send', {
      session_id: sessionId.value,
      message: text,
    })
    appendMessage('bot', res.data.reply.message)
    console.log('aqui en vue')
    console.log(res)
  } catch (e) {
    appendMessage('bot', 'Hubo un error al comunicarse con el asistente. Intentá más tarde.')
    console.error(e)
  } finally {
    loading.value = false
  }
}

// --- NUEVA FUNCIÓN: Obtener mensajes del backend ---
const fetchMessages = async () => {
  try {
    const response = await axios.get('/api/messages', { // Nuevo endpoint para obtener mensajes
      params: {
        session_id: sessionId.value
      }
    });
    if (response.data && response.data.messages) {
      //messages.value = response.data.messages; // Rellenar con mensajes de la DB
      console.log('Mensajes anteriores cargados:', messages.value);
      console.log('Response: ', response.data.messages);

      response.data.messages.forEach(message => {
        appendMessage(message.role, message.content)
      });
    }
  } catch (error) {
    console.error('Error al cargar mensajes anteriores:', error);
    // Si no hay mensajes o falla, aún mostramos el mensaje inicial del asistente
    if (messages.value.length === 0) {
      messages.value.push({ role: 'assistant', content: '¡Hola! ¿En qué puedo ayudarte hoy?' });
    }
  }
};

onMounted(() => {
  console.log('Componente montado. Session ID:', sessionId.value);
  initializeEcho();

  appendMessage('bot', '¡Hola! Soy tu Broker Virtual. ¿En qué puedo ayudarte hoy?');
  fetchMessages(); // Llamar a la función para obtener mensajes
  scrollToBottom(); // Desplazar al final después de cargar los mensajes iniciales
  // async () => {
  //   await fetchMessages(); // Llamar a la función para obtener mensajes
  //   scrollToBottom(); // Desplazar al final después de cargar los mensajes iniciales
  // };
  // Mensaje inicial del bot (esto ya lo tienes)
  
});

onBeforeUnmount(() => {
  // Desuscribirse del canal cuando el componente se desmonte para evitar fugas de memoria
  if (window.Echo && sessionId.value) {
    window.Echo.leaveChannel(`user-quotes.${sessionId.value}`);
    console.log('Se ha dejado el canal de quotes:', `user-quotes.${sessionId.value}`);
  }
});

// Función para inicializar Laravel Echo y escuchar el evento
const initializeEcho = () => {
  if (typeof window.Echo === 'undefined') {
    console.error('Laravel Echo no está inicializado. Asegúrate de que resources/js/bootstrap.js esté correctamente configurado y cargado.');
    return;
  }

  console.log('Intentando escuchar en el canal:', `user-quotes.${sessionId.value}`);
  
  // Suscribirse al canal privado del usuario
  window.Echo.channel(`user-quotes.${sessionId.value}`) // Escucha el canal 'user.quotes.$sessionId'
    .listen('.alternatives.updated', (e) => { // Escucha el evento 'alternatives.updated' (broadcastAs)
      console.log('Evento "alternatives.updated" recibido:', e);
      // Actualizar el estado del componente con las alternativas recibidas
      //quotes.value = e.alternatives;
      //quotesAvailable.value = true;
      console.log(e);
      // Opcional: Puedes añadir un mensaje de chat para informar al usuario
      appendMessage('bot', e.message);

    })
    .error((error) => {
      console.error('Error al escuchar en el canal user-quotes:', error);
      // Manejar errores de conexión o autenticación del canal
    });
};

</script>

<style> /* Atributo 'scoped' eliminado para asegurar la aplicación de estilos */
/* Nueva paleta de colores para un diseño moderno y minimalista con glows vibrantes */
:root {
  --main-bg-color: #f0f4f8; /* Fondo principal muy claro */
  --widget-bg-color: #ffffff; /* Fondo del widget blanco puro */
  --header-bg-color: linear-gradient(to right, #6a74c4, #8b96e5); /* Gradiente de morado suave a azul */
  --header-text-color: #ffffff;
  --bot-avatar-bg-color: #6a74c4; /* Fondo del avatar del bot */
  --bot-avatar-icon-color: #ffffff;
  --status-dot-color: #34d399; /* Verde para el estado activo */
  --messages-area-bg-color: #e9eff6; /* Fondo del área de mensajes (ligeramente más oscuro que el fondo principal) */

  --bot-bubble-bg-color: #ffffff; /* Burbuja del bot: blanco */
  --bot-bubble-border-color: #c8d2df; /* Borde sutil gris-azulado para el bot */
  --user-bubble-bg-color: #4c6ef5; /* Burbuja del usuario: azul claro */
  --user-bubble-text-color: #ffffff; /* Texto blanco para la burbuja del usuario */

  --input-bg-color: #edf2f7; /* Fondo del input muy claro */
  --input-border-color: #d1dce5; /* Borde sutil para el input */
  --input-focus-border-color: #6a74c4; /* Borde de enfoque del input */
  --send-button-bg-color: #4c6ef5; /* Color del botón de enviar */
  --send-button-hover-color: #3a59d9; /* Color de hover del botón de enviar */
  --text-dark-color: #2d3748; /* Color de texto oscuro general */
  --text-light-color: #4a5568; /* Color de texto claro para burbuja del bot */

  /* Sombras y glows (mantienen la vibrancia deseada) */
  --widget-shadow: 0 8px 16px rgba(0, 0, 0, 0.08); /* Sombra suave para el widget */
  --bubble-shadow-normal: 0 3px 6px rgba(0, 0, 0, 0.06); /* Sombra base sutil para burbujas */
  --bubble-shadow-hover: 0 6px 12px rgba(0, 0, 0, 0.1); /* Sombra sutil de hover para burbujas */
  --input-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); /* Sombra muy sutil para el input */

  /* Colores de glow vibrantes */
  --bot-glow-color-normal: rgba(180, 80, 200, 0.4); /* Morado vibrante para glow del bot */
  --bot-glow-color-hover: rgba(180, 80, 200, 0.6); 
  --user-glow-color-normal: rgba(76, 110, 245, 0.4); /* Azul vibrante para glow del usuario */
  --user-glow-color-hover: rgba(76, 110, 245, 0.6);
}

body {
  background-color: var(--main-bg-color); /* Aplica el fondo principal al body */
}

.chat-widget {
  max-width: 400px;
  margin: 40px auto; /* Centra el widget y le da espacio */
  border-radius: 16px; /* Bordes suavemente redondeados */
  background: var(--widget-bg-color);
  border: 1px solid var(--input-border-color); /* Borde sutil general */
  overflow: hidden;
  font-family: 'Inter', sans-serif;
  box-shadow: var(--widget-shadow); /* Sombra general suave */
  transition: all 0.3s ease; /* Transición general para suavidad */
}

.chat-header {
  background: var(--header-bg-color); /* Gradiente de encabezado */
  padding: 16px 20px; /* Padding ajustado */
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top-left-radius: 15px; /* Coincide con el widget */
  border-top-right-radius: 15px; /* Coincide con el widget */
}

.bot-avatar {
  width: 36px; /* Tamaño del avatar */
  height: 36px;
  border-radius: 50%; /* Completamente redondo */
  background: var(--bot-avatar-bg-color);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--bot-avatar-icon-color);
  box-shadow: none; /* Sin sombra en el avatar */
}

.chat-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--header-text-color);
  margin: 0;
  letter-spacing: -0.01em;
}

.status-dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: var(--status-dot-color);
  box-shadow: 0 0 0 2px rgba(52, 211, 153, 0.2); /* Glow sutil para el estado */
}

.messages-area {
  height: 400px; /* Altura fija para el área de mensajes */
  overflow-y: auto;
  padding: 16px 20px;
  background: var(--messages-area-bg-color); /* Fondo del área de mensajes */
}

.messages-area::-webkit-scrollbar {
  width: 4px; /* Scrollbar más delgado */
}

.messages-area::-webkit-scrollbar-track {
  background: var(--messages-area-bg-color);
}

.messages-area::-webkit-scrollbar-thumb {
  background: #cdd6e0; /* Gris sutil para el scrollbar */
  border-radius: 2px;
}

.message-wrapper {
  margin-bottom: 10px; /* Espacio entre mensajes */
  display: flex;
}

.bot-message {
  justify-content: flex-start;
}

.user-message {
  justify-content: flex-end;
}

.message-bubble {
  max-width: 80%;
  padding: 10px 15px; /* Padding interno de la burbuja */
  padding-right: 30px;
  border-radius: 20px; /* Forma de píldora */
  word-wrap: break-word;
  transform: translateY(-1px); /* Elevación sutil por defecto */
  transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
}

.message-bubble:hover {
  transform: translateY(-2.5px); /* Se levanta un poco más al hover */
}

.bot-bubble {
  background: var(--bot-bubble-bg-color);
  color: var(--text-light-color); /* Texto claro para la burbuja del bot */
  border: 1px solid var(--bot-bubble-border-color); /* Borde sutil del bot */
  border-bottom-left-radius: 6px; /* Esquina inferior izquierda menos redondeada */
  /* Sombra base sutil + glow vibrante del bot */
  box-shadow: var(--bubble-shadow-normal), 0 0 16px var(--bot-glow-color-normal);
}

.bot-bubble:hover {
  /* Sombra de hover sutil + glow más intenso del bot */
  box-shadow: var(--bubble-shadow-hover), 0 0 28px var(--bot-glow-color-hover);
}

.user-bubble {
  background: var(--user-bubble-bg-color);
  color: var(--user-bubble-text-color);
  border: 1px solid var(--user-bubble-bg-color); /* Borde sólido del mismo color */
  border-bottom-right-radius: 6px; /* Esquina inferior derecha menos redondeada */
  /* Sombra base sutil + glow vibrante del usuario */
  box-shadow: var(--bubble-shadow-normal), 0 0 16px var(--user-glow-color-normal);
}

.user-bubble:hover {
  /* Sombra de hover sutil + glow más intenso del usuario */
  box-shadow: var(--bubble-shadow-hover), 0 0 28px var(--user-glow-color-hover);
}

.message-text {
  font-size: 14.5px;
  line-height: 1.5;
  padding-bottom: 3px;
  /* margin: 0; */
}

.typing-indicator {
  display: flex;
  align-items: center;
  gap: 3px;
  padding: 4px 0;
}

.typing-dot {
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: #aab8c4; /* Gris suave para los puntos de escritura */
  animation: typing 1.4s infinite ease-in-out;
}

.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }
.typing-dot:nth-child(3) { animation-delay: 0; }

@keyframes typing {
  0%, 80%, 100% {
    opacity: 0.3;
    transform: scale(0.8);
  }
  40% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Transiciones para el indicador de escritura */
.typing-enter-active {
  transition: all 0.3s ease-out;
  transition-delay: 0.5s;
}

.typing-leave-active {
  transition: none;
}

.typing-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.typing-leave-to {
  opacity: 0;
}

.typing-enter-to, .typing-leave-from {
  opacity: 1;
  transform: translateY(0);
}

.input-container {
  padding: 14px 20px 18px; /* Padding ajustado */
  background: var(--widget-bg-color);
  border-top: 1px solid var(--input-border-color); /* Borde superior sutil */
}

.input-wrapper {
  display: flex;
  align-items: center;
  background: var(--input-bg-color);
  border-radius: 18px; /* Bordes redondeados */
  padding: 5px; /* Padding interno del input */
  border: 1px solid var(--input-border-color);
  transition: all 0.2s ease;
  box-shadow: var(--input-shadow); /* Sombra sutil del input */
}

.input-wrapper:focus-within {
  border-color: var(--input-focus-border-color);
  background: var(--widget-bg-color);
  box-shadow: 0 0 0 3px rgba(106, 116, 196, 0.2); /* Glow sutil al enfocar */
}

.message-input {
  flex: 1;
  border: none;
  outline: none;
  padding: 9px 14px;
  font-size: 14.5px;
  background: transparent;
  color: var(--text-dark-color);
}

.message-input::placeholder {
  color: #a3a3a3; /* Gris suave para el placeholder */
}

.message-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.send-button {
  width: 40px; /* Tamaño del botón de enviar */
  height: 40px;
  border-radius: 14px; /* Bordes redondeados */
  background: var(--send-button-bg-color);
  border: none;
  color: var(--user-bubble-text-color);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 3px 6px rgba(76, 110, 245, 0.25); /* Sombra del botón */
}

.send-button svg {
  transform: rotate(45deg); /* Rotación del icono de enviar */
}

.send-button:hover:not(:disabled) {
  background: var(--send-button-hover-color);
  box-shadow: 0 5px 10px rgba(76, 110, 245, 0.35);
}

.send-button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Diseño Responsivo */
@media (max-width: 480px) {
  .chat-widget {
    max-width: 100%;
    margin: 0;
    border-radius: 0;
    height: 100vh;
    border: none;
    box-shadow: none;
  }
  
  .messages-area {
    height: calc(100vh - 110px); /* Ajuste para el nuevo padding del header/input */
    padding: 14px 18px;
  }

  .chat-header, .input-container {
    padding: 12px 18px;
  }
}
</style>
