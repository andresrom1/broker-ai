<template>
  <div class="chat-widget">
    <!-- Encabezado del chat con un diseño renovado -->
    <div class="chat-header">
      <div class="flex items-center space-x-3">
        <div class="bot-avatar">
          <!-- Icono de robot moderno y amigable -->
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path
              d="M12 2C13.1 2 14 2.9 14 4V5H10V4C10 2.9 10.9 2 12 2M12 8C16.42 8 20 11.58 20 16C20 20.42 16.42 24 12 24C7.58 24 4 20.42 4 16C4 11.58 7.58 8 12 8M12 10C8.69 10 6 12.69 6 16C6 19.31 8.69 22 12 22C15.31 22 18 19.31 18 16C18 12.69 15.31 10 12 10M9 14C9.55 14 10 14.45 10 15C10 15.55 9.55 16 9 16C8.45 16 8 15.55 8 15C8 14.45 8.45 14 9 14M15 14C15.55 14 16 14.45 16 15C16 15.55 15.55 16 15 16C14.45 16 14 15.55 14 15C14 14.45 14.45 14 15 14M12 17C13.1 17 14 17.9 14 19H10C10 17.9 10.9 17 12 17Z" />
          </svg>
        </div>
        <div>
          <h3 class="chat-title">Broker Virtual</h3>
        </div>
      </div>
      <div class="status-dot"></div>
    </div>

    <!-- Botón para habilitar notificaciones (puedes estilizarlo mejor) -->
    <div v-if="notificationPermission === 'default' && !subscriptionAttempted" class="mb-4 text-center">
      <button @click="requestNotificationPermission"
        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-md shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
        Habilitar Notificaciones
      </button>
      <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Recibe alertas importantes sobre tu cotización.</p>
    </div>
    <div v-else-if="notificationPermission === 'denied'"
      class="mb-4 text-center text-red-600 dark:text-red-400 text-sm">
      Las notificaciones están bloqueadas. Por favor, habilítalas en la configuración de tu navegador.
    </div>

    <!-- Área de mensajes -->
    <div ref="messagesContainer" class="messages-area">
      <div v-for="(msg, index) in messages" :key="index"
        :class="['message-wrapper', msg.from === 'user' ? 'user-message' : 'bot-message' ]">

        <!-- Mensaje comun -->
        <div v-if="msg?.meta_data && msg.meta_data?.type === 'assistant_response'" class="message-bubble"
          :class="[msg.from === 'user' ? 'user-bubble' : 'bot-bubble']">
          <div class="message-text">{{ msg.text }}</div>
          <!-- Added time bubble -->
          <div :class="[
            'absolute text-xxxs',
            msg.from === 'user' ? 'bottom-1 right-2 text-blue-100' : 'bottom-1 right-2 text-gray-500']">
            12:33 p.m.
          </div>
        </div>

        <!-- Tarjeta de cotizacion -->
        <!-- <div v-else-if="msg.meta_data && msg.meta_data.type === 'quote_alternative'" class="message-bubble" :class="[msg.from === 'user' ? 'user-bubble' : 'bot-bubble']">
          <div class="message-text">{{ msg.text }}</div>
          <div :class="[
            'absolute text-xxxs',
            msg.from === 'user' ? 'bottom-1 right-2 text-blue-100' : 'bottom-1 right-2 text-gray-500']">
            12:33 p.m.
          </div>
        </div> -->

        <!-- Tarjeta de Cotización Alternativa -->
        <div v-else-if="msg.meta_data && msg.meta_data.type === 'quote_alternative'"
          class="bg-white rounded-xl shadow-lg border-l-4 border-orange-400 overflow-hidden hover:shadow-xl transition-all duration-300">

          <!-- Header con icono y etiqueta -->
          <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-4 py-3 border-b border-orange-100">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <div class="bg-orange-100 p-2 rounded-lg">
                  <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                    </path>
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-gray-800">Cotización Alternativa</h3>
                  <p class="text-xs text-orange-600">Opción recomendada</p>
                </div>
              </div>
              <div class="bg-orange-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                ALT
              </div>
            </div>
          </div>

          <!-- Contenido principal -->
          <div class="p-4">
            <div class="bg-gray-50 rounded-lg p-3 mb-3">
              <p class="text-sm text-gray-700 leading-relaxed">
                {{ msg.text }}
              </p>
            </div>

            <!-- Información adicional -->
            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
              <div class="flex items-center space-x-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                    clip-rule="evenodd"></path>
                </svg>
                <span>Válida por 30 días</span>
              </div>
              <div class="flex items-center space-x-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Verificada</span>
              </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex space-x-2">
              <button
                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium py-2 px-3 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Aceptar</span>
              </button>
              <button
                class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium py-2 px-3 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                  </path>
                </svg>
                <span>Detalles</span>
              </button>
            </div>
          </div>

          <!-- Footer con timestamp -->
          <div class="bg-orange-50 px-4 py-2 border-t border-orange-100">
            <div class="text-right">
              <span class="text-xs text-orange-600 font-medium">12:33 p.m.</span>
            </div>
          </div>
        </div>


        <!-- Tarjeta de Link Elegante -->
        <div v-else-if="msg.meta_data && msg.meta_data.type === 'quote_link'"
          class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <!-- Header con icono -->
          <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
            <div class="flex items-center space-x-2">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
              </svg>
              <span class="text-white font-medium text-sm">Enlace de Descarga</span>
            </div>
          </div>

          <!-- Contenido -->
          <div class="p-4">
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-600 mb-1">Archivo disponible</p>
                <p class="text-xs text-gray-500 truncate font-mono bg-gray-50 px-2 py-1 rounded">
                  {{ msg.meta_data.link }}
                </p>
              </div>
            </div>

            <!-- Botón de acción -->
            <div class="mt-4 flex space-x-2">
              <button
                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Descargar</span>
              </button>
              <button class="bg-gray-100 hover:bg-gray-200 text-gray-600 p-2 rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Footer con tiempo -->
          <div class="bg-gray-50 px-4 py-2 border-t border-gray-100">
            <div class="text-right">
              <span class="text-xxxs text-gray-500">12:33 p.m.</span>
            </div>
          </div>
        </div>

      </div>


      <!-- Indicador de escritura sutil -->
      <Transition name="typing" appear>
        <div v-if="loading" class="message-wrapper bot-message">
          <div class="message-bubble bot-bubble">
            <div class="typing-indicator">
              <div class="typing-dot"></div>
              <div class="typing-dot"></div>
              <div class="typing-dot"></div>
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Contenedor de entrada de mensaje -->
    <div class="input-container">
      <div class="input-wrapper">
        <input v-model="input" @keydown.enter="handleInput" type="text" placeholder="Escribe tu mensaje..."
          class="message-input" :disabled="loading" />
        <button @click="handleInput" class="send-button" :disabled="loading || !input.trim()">
          <!-- Icono de enviar -->
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
          </svg>
        </button>
      </div>
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
const notificationPermission = ref(Notification.permission); // 'default', 'granted', 'denied'
const subscriptionAttempted = ref(false); // Para mostrar el botón solo una vez



function generateSessionId() {
  const id = Date.now().toString(36) + Math.random().toString(36).substring(2)
  localStorage.setItem('session_id', id)
  return id
}

function appendMessage(from, text, meta_data) {
  messages.value.push({ from, text, meta_data, timestamp: new Date() })  
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
  
  appendMessage('user', text, {'type': 'assistant_response'})
  input.value = ''
  loading.value = true

  try {
    const res = await axios.post('/api/chat/send', {
      session_id: sessionId.value,
      message: text,
      //meta_data: {type: 'assistant_response'}
    })
    console.log(res.data.reply.message)
    console.log(res.data.reply)
    appendMessage('bot', res.data.reply.message, res.data.reply.meta_data)
    console.log('aqui en vue')
    console.log(res)
  } catch (e) {
    appendMessage('bot', 'Hubo un error al comunicarse con el asistente. Intentá más tarde.')
    console.error(e)
  } finally {
    loading.value = false
  }
}

const fetchMessages = () => {
  axios.get('/api/messages', {
    params: {
      session_id: sessionId.value
    }
  })
  .then(response => {
    if (response.data && response.data.messages) {
          response.data.messages.forEach(message => {
        appendMessage(message.role, message.content, message.meta_data);
      });
    }
  })
  .catch(error => {
    console.error('Error al cargar mensajes anteriores:', error);
    // Si no hay mensajes o falla, aún mostramos el mensaje inicial del asistente
    if (messages.value.length === 0) {
      messages.value.push({ 
        role: 'assistant', 
        content: '¡Hola! ¿En qué puedo ayudarte hoy?',
        meta_data: { type: 'system_message' }
      });
    }
  });
};


onMounted(() => {
  console.log('Componente montado. Session ID:', sessionId.value);
  initializeEcho();

  appendMessage('bot', '¡Hola! Soy tu Broker Virtual. ¿En qué puedo ayudarte hoy?',{ 'type': 'assistant_response'});
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
      //console.log(e[0].message);
      // console.log(e[0].meta_data)
      // Opcional: Puedes añadir un mensaje de chat para informar al usuario
      appendMessage('bot', e[0].message,e[0].meta_data);

    })
    .error((error) => {
      console.error('Error al escuchar en el canal user-quotes:', error);
      // Manejar errores de conexión o autenticación del canal
    });
};

// --- LÓGICA DE NOTIFICACIONES PUSH ---
const publicVapidKey = 'BEX6Qgwyaar8dbkYbe_Zh2lBCf9or1YO8qnpotdCfIL65MZN7WDKtWTCyJ3UgSHFnFo_iLj1wM2QdixMVAfazb8'; // <--- ¡IMPORTANTE! Reemplaza con tu clave VAPID pública

// Función para convertir la clave VAPID de base64 a un Uint8Array (formato requerido por la API)
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

const requestNotificationPermission = async () => {
  subscriptionAttempted.value = true; 
  console.log('Solicitando permiso de notificación...');
  const permission = await Notification.requestPermission();
  notificationPermission.value = permission; 

  if (permission === 'granted') {
    console.log('Permiso de notificación concedido.');
    await subscribeUserToPush();
  } else {
    console.warn('Permiso de notificación denegado o no concedido.');
  }
};

const subscribeUserToPush = async () => {
  try {
    const registration = await window.registerServiceWorker(); 

    if (!registration) {
      console.error('No se pudo obtener el registro del Service Worker.');
      return;
    }

    const subscribeOptions = {
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(publicVapidKey)
    };

    const pushSubscription = await registration.pushManager.subscribe(subscribeOptions);
    console.log('Suscripción Push creada:', pushSubscription);

    await sendSubscriptionToBackend(pushSubscription);

  } catch (error) {
    console.error('Fallo la suscripción Push:', error);
  }
};

const sendSubscriptionToBackend = async (subscription) => {
  try {
    const response = await axios.post('/api/subscribe', {
      session_id: sessionId.value,
      subscription: subscription
    });
    console.log('Suscripción enviada al backend:', response.data);
  } catch (error) {
    console.error('Error al enviar la suscripción al backend:', error);
  }
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