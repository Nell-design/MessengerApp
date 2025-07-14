<script setup lang="ts">
import Echo from 'laravel-echo';
import { defineProps, ref, watch, nextTick, onMounted, onUnmounted } from 'vue';

// Types pour les événements Echo
interface MessageSentEvent {
  message: any;
  receiver_id: number;
  conversation_id: number;
  sender_name: string;
  conversation_name: string;
}

interface MessageDeletedEvent {
  message_id: number;
}

interface UserTypingEvent {
  user_id: number;
  is_typing: boolean;
}

interface MessageReceivedEvent {
  message_id: number;
  conversation_id: number;
  receiver_id: number;
  sender_id: number;
  is_read: boolean;
}

const props = defineProps({
  conversation: {
    type: Object,
    required: true,
  },
  currentUserId: {
    type: Number,
    required: true,
  },
  onOpenSidebar: {
    type: Function,
    required: false,
  },
  onMessageSent: {
    type: Function,
    required: false,
  },
});

interface Message {
  id: number;
  content: string;
  sender_id: number;
  receiver_id?: number;
  created_at: string;
  sender_avatar?: string;
  receiver_avatar?: string;
  read_at?: string | null;
}

const messages = ref<Message[]>([]);
const newMessage = ref('');
const sending = ref(false);
const loadingMessages = ref(false);
const chatContainer = ref<HTMLElement | null>(null);
const isTyping = ref(false);
const typingTimeout = ref<ReturnType<typeof setTimeout> | null>(null);
const showDeleteMenu = ref<number | null>(null); // ID du message pour lequel afficher le menu
const remoteTyping = ref(false);

async function fetchMessages(conversationId: number) {
  try {
    loadingMessages.value = true;

    const res = await fetch(`/conversations/${conversationId}/messages`, {
      method: 'GET',
      headers: { Accept: 'application/json' },
    });

    if (!res.ok) {
      const err = await res.json();
      console.error('❌ Erreurs de validation Laravel :', err.errors || err.message);
      throw new Error(`Erreur ${res.status}: ${res.statusText}`);
    }

    const data = await res.json();

    // Si data est un objet avec une clé messages :
    const fetchedMessages = Array.isArray(data.messages) ? data.messages : data;

    messages.value.splice(0);
    messages.value.push(...fetchedMessages);
    // console.log("✅ Messages récupérés avec succès");
  } catch (error) {
    console.error('❌ Erreur de récupération des messages :', error);
  } finally {
    loadingMessages.value = false;
  }
}

watch(
  () => props.conversation?.id,
  (newId, oldId) => {
    if (newId) {
      fetchMessages(newId);
      markConversationAsRead(newId);
    }
    // Réinitialise l'état de frappe distant lors du changement de conversation
    remoteTyping.value = false;
  },
  { immediate: true }
);

watch(messages, async () => {
  await nextTick();
  if (chatContainer.value) {
    chatContainer.value.scrollTop = chatContainer.value.scrollHeight;

    // Marquer les messages comme lus quand on fait défiler vers le bas
    const isScrolledToBottom = chatContainer.value.scrollTop + chatContainer.value.clientHeight >= chatContainer.value.scrollHeight - 10;
    if (isScrolledToBottom && props.conversation?.id) {
      await markConversationAsRead(props.conversation.id);
    }
  }
});

onMounted(() => {
  let echoChannel: any = null;
  let userChannel: any = null;

  // Récupérer l'ID de la conversation
  const conversationId = props.conversation?.id;
  const currentUserId = props.currentUserId;

  console.log('🔍 ChatWindow: onMounted - Début de l\'initialisation des listeners');
  console.log('🔍 ChatWindow: Conversation ID:', conversationId);
  console.log('🔍 ChatWindow: Current User ID:', currentUserId);
  console.log('🔍 ChatWindow: Echo disponible:', !!(window as any).Echo);

  if (conversationId) {
    console.log('📡 Initialisation des listeners pour la conversation:', conversationId);

    // Correction : écouter sur le canal conversation.{conversationId} (et non pas conversation)
    echoChannel = (window as any).Echo.channel(`conversation.${conversationId}`)
      .listen('MessageSentEvent', (event: any) => {
        if (currentUserId === event.receiver_id) {
          console.log("📨 Message reçu pour l'utilisateur actuel sur canal conversation dans ChatWindow");

          // Si le message est pour la conversation actuellement ouverte
          if (event.message && event.message.conversation_id === conversationId) {
            console.log('📨 MessageSentEvent reçu pour la conversation active:', conversationId);

            // Évite les doublons si le message existe déjà (ex: envoyé localement)
            const messageExists = messages.value.some(m => m.id === event.message.id);
            console.log('🔍 ChatWindow: Message existe déjà?', messageExists);

            if (!messageExists) {
              console.log('✅ ChatWindow: Ajout du message à la liste:', event.message);
              messages.value.push(event.message);

              // Faire défiler vers le bas pour voir le nouveau message
              nextTick(() => {
                if (chatContainer.value) {
                  chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
                }
              });
            } else {
              console.log('⚠️ ChatWindow: Message déjà présent, ignoré:', event.message.id);
            }
          } else {
            console.log('⚠️ ChatWindow: Message ignoré - mauvaise conversation:', {
              message_conversation_id: event.message?.conversation_id,
              current_conversation_id: conversationId
            });
          }
        }
      })
      .listen('MessageDeletedEvent', (event: MessageDeletedEvent) => {
        console.log('🗑️ MessageDeletedEvent reçu sur conversation:', event);
        messages.value = messages.value.filter(m => m.id !== event.message_id);
      })
      .listen('MessageDeletedForEveryoneEvent', (event: MessageDeletedEvent) => {
        console.log('🗑️ MessageDeletedForEveryoneEvent reçu sur conversation:', event);
        messages.value = messages.value.filter(m => m.id !== event.message_id);
      })
      .listen('UserTypingEvent', (event: UserTypingEvent) => {
        console.log('⌨️ UserTypingEvent reçu sur conversation:', event);
        if (event.user_id !== currentUserId) {
          remoteTyping.value = !!event.is_typing;
          if (event.is_typing) {
            // On arrête l'indicateur après 3s si pas de nouveau signal
            if (typingTimeout.value) clearTimeout(typingTimeout.value);
            typingTimeout.value = setTimeout(() => {
              remoteTyping.value = false;
            }, 3000);
          }
        }
      })
      .listen('MessageReceivedEvent', (event: MessageReceivedEvent) => {
        console.log('✅ MessageReceivedEvent reçu sur conversation:', event);
        // Met à jour le statut de lecture du message dans la liste
        const messageIndex = messages.value.findIndex(m => m.id === event.message_id);
        if (messageIndex !== -1) {
          messages.value[messageIndex] = {
            ...messages.value[messageIndex],
            read_at: new Date().toISOString()
          };
        }
      });

    console.log('✅ ChatWindow: Listeners de conversation configurés pour:', conversationId);
  }

  // Écouter sur le canal utilisateur pour recevoir les messages même si on n'est pas dans la conversation
  console.log('📡 Initialisation du listener utilisateur pour:', currentUserId);
  userChannel = (window as any).Echo.channel(`user.${currentUserId}`)
    .listen('MessageSentEvent', (event: MessageSentEvent) => {
      console.log('📨 MessageSentEvent reçu sur canal utilisateur:', event);

      // Si le message est pour la conversation actuellement ouverte
      if (event.message && event.message.conversation_id === conversationId) {
        // Évite les doublons si le message existe déjà
        const messageExists = messages.value.some(m => m.id === event.message.id);
        console.log('🔍 ChatWindow: Message existe déjà dans la conversation active?', messageExists);

        if (!messageExists) {
          console.log('✅ Ajout du message reçu à la conversation active:', event.message.id);
          messages.value.push(event.message);

          // Faire défiler vers le bas pour voir le nouveau message
          nextTick(() => {
            if (chatContainer.value) {
              chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
            }
          });
        } else {
          console.log('⚠️ ChatWindow: Message déjà présent dans la conversation active, ignoré:', event.message.id);
        }
      } else if (event.message) {
        // Si c'est un message pour une autre conversation, on peut déclencher une notification
        console.log('📱 Message reçu pour une autre conversation:', event.message.conversation_id);
        // Ici on pourrait déclencher une notification ou mettre à jour la sidebar
      }
    })
    .listen('conversation.updated', (event: any) => {
      console.log('🔄 Conversation mise à jour reçue dans ChatWindow:', event);
      // Si la conversation mise à jour est celle actuellement ouverte
      if (event.conversation && event.conversation.id === conversationId) {
        console.log('✅ Mise à jour de la conversation active reçue');
        // On pourrait ici mettre à jour les informations de la conversation si nécessaire
      }
    });

  document.addEventListener('click', handleClickOutside);

  if (chatContainer.value) {
    chatContainer.value.addEventListener('scroll', handleScroll);
  }
});

onUnmounted(() => {
  if (props.conversation?.id) {
    (window as any).Echo.leave(`conversation.${props.conversation.id}`);
  }
  if (props.currentUserId) {
    (window as any).Echo.leave(`user.${props.currentUserId}`);
  }
  document.removeEventListener('click', handleClickOutside);

  if (chatContainer.value) {
    chatContainer.value.removeEventListener('scroll', handleScroll);
  }
  if (typingTimeout.value) clearTimeout(typingTimeout.value);
});

function handleClickOutside(e: Event) {
  if (!(e.target as Element).closest('.delete-menu') &&
      !(e.target as Element).closest('[data-delete-button]')) {
    showDeleteMenu.value = null;
  }
}




async function sendMessage() {

  const conversationId = props.conversation?.id;

  if (!conversationId || !newMessage.value.trim()) {
    // console.warn("Conversation ou message vide");
    return;
  }
  if (sending.value) return;

  try {
    sending.value = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) throw new Error('CSRF token introuvable');

    const res = await fetch('/messages', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
      body: JSON.stringify({
        conversation_id: conversationId,
        content: newMessage.value.trim(),
      }),
    });

    if (!res.ok) {
      const errText = await res.text();
      throw new Error(`Erreur API: ${errText}`);
    }

    const savedMessage = await res.json();
    // Évite les doublons
    if (!messages.value.some(m => m.id === savedMessage.id)) {
    messages.value.push(savedMessage);
    }
    newMessage.value = '';
    if (props.onMessageSent) props.onMessageSent(conversationId, savedMessage);
  } catch (error) {
    console.error('❌ Erreur lors de l\'envoi du message :', error);
  } finally {
    sending.value = false;
  }
}

async function deleteMessage(messageId: number, forEveryone: boolean = false) {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) throw new Error('CSRF token introuvable');

    const url = forEveryone
      ? `/messages/${messageId}?for_everyone=true`
      : `/messages/${messageId}`;

    const res = await fetch(url, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
    });

    if (!res.ok) {
      const errText = await res.text();
      throw new Error(`Erreur API: ${errText}`);
    }

    messages.value = messages.value.filter(m => m.id !== messageId);
    showDeleteMenu.value = null;
  } catch (error) {
    console.error('❌ Erreur lors de la suppression du message :', error);
  }
}

function toggleDeleteMenu(messageId: number) {
  showDeleteMenu.value = showDeleteMenu.value === messageId ? null : messageId;
}

async function sendTypingStatus(isTypingStatus: boolean) {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) return;

    await fetch('/messages/typing', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
      body: JSON.stringify({
        conversation_id: props.conversation.id,
        is_typing: isTypingStatus,
      }),
    });
  } catch (error) {
    // Silencieux
  }
}

function handleTyping() {
  if (!isTyping.value) {
    isTyping.value = true;
    sendTypingStatus(true);
  }

  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }

  typingTimeout.value = setTimeout(() => {
    isTyping.value = false;
    sendTypingStatus(false);
  }, 2000);
}

async function markMessagesAsRead() {
  if (!props.conversation?.id || !messages.value.length) return;

  try {
    const unreadMessages = messages.value.filter(msg =>
      msg.sender_id !== props.currentUserId &&
      (!('read_at' in msg) || !msg.read_at)
    );

    if (unreadMessages.length === 0) return;

    console.log('📖 Tentative de marquage de', unreadMessages.length, 'messages comme lus');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
      console.warn('❌ CSRF token introuvable');
      return;
    }

    // Marquer les messages comme lus en parallèle
    const promises = unreadMessages.map(async (message) => {
      try {
        console.log('📖 Marquage du message', message.id, 'comme lu');

        const response = await fetch(`/messages/${message.id}/read`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
        });

        if (response.ok) {
          // Mettre à jour le message localement
          const messageIndex = messages.value.findIndex(m => m.id === message.id);
          if (messageIndex !== -1) {
            messages.value[messageIndex] = {
              ...messages.value[messageIndex],
              read_at: new Date().toISOString()
            };
          }
          console.log('✅ Message', message.id, 'marqué comme lu');
          return { success: true, messageId: message.id };
        } else {
          const errorText = await response.text();
          console.warn('⚠️ Erreur lors du marquage du message', message.id, ':', response.status, errorText);
          return { success: false, messageId: message.id, error: errorText };
        }
      } catch (error) {
        console.error('❌ Erreur lors du marquage du message', message.id, ':', error);
        return { success: false, messageId: message.id, error: error };
      }
    });

    const results = await Promise.all(promises);
    const successCount = results.filter(r => r.success).length;
    const errorCount = results.filter(r => !r.success).length;

    console.log(`📖 Résultat du marquage: ${successCount} succès, ${errorCount} erreurs`);

  } catch (error) {
    console.error('❌ Erreur lors du marquage des messages comme lus:', error);
  }
}

async function markConversationAsRead(conversationId: number) {
  try {
    console.log('📖 Marquage de tous les messages de la conversation', conversationId, 'comme lus');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
      console.warn('❌ CSRF token introuvable');
      return;
    }

    // Utiliser la nouvelle route pour marquer tous les messages comme lus
    const response = await fetch(`/conversations/${conversationId}/messages/read`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      console.log('✅ Tous les messages de la conversation', conversationId, 'marqués comme lus');

      // Mettre à jour localement tous les messages non lus
      messages.value.forEach((message, index) => {
        if (message.sender_id !== props.currentUserId && (!('read_at' in message) || !message.read_at)) {
          messages.value[index] = {
            ...message,
            read_at: new Date().toISOString()
          };
        }
      });
    } else {
      const errorText = await response.text();
      console.warn('⚠️ Erreur lors du marquage de la conversation comme lue:', response.status, errorText);
    }
  } catch (error) {
    console.error('❌ Erreur lors du marquage comme lu:', error);
  }
}

function handleScroll() {
  if (!chatContainer.value) return;

  const isScrolledToBottom = chatContainer.value.scrollTop + chatContainer.value.clientHeight >= chatContainer.value.scrollHeight - 10;
  if (isScrolledToBottom && props.conversation?.id) {
    markConversationAsRead(props.conversation.id);
  }
}
</script>

<template>
  <div v-if="conversation" class="flex-1 flex flex-col bg-white rounded-r-xl shadow h-full">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b">
      <div class="flex items-center gap-3">
        <button
          v-if="onOpenSidebar"
          class="sm:hidden mr-2 bg-transparent text-white p-2 rounded"
          @click="onOpenSidebar()"
          type="button"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="24" viewBox="0 0 12 24">
            <path
              fill="#0d0c0c"
              fill-rule="evenodd"
              d="m3.343 12l7.071 7.071L9 20.485l-7.778-7.778a1 1 0 0 1 0-1.414L9 3.515l1.414 1.414z"
            />
          </svg>
        </button>
        <img :src="conversation.avatar || '/default-avatar.png'" class="w-10 h-10 rounded-full object-cover" />
        <div>
          <div class="font-semibold">{{ conversation.name }}</div>
          <div class="text-xs text-gray-400">
            <span v-if="remoteTyping">En train d'écrire...</span>
            <span v-else>En ligne</span>
          </div>
        </div>
      </div>
      <div class="flex gap-3 text-gray-400">
        <!-- Icônes -->
        <button type="button" tabindex="-1">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path
              fill="currentColor"
              d="m16.556 12.906l-.455.453s-1.083 1.076-4.038-1.862s-1.872-4.014-1.872-4.014l.286-.286c.707-.702.774-1.83.157-2.654L9.374 2.86C8.61 1.84 7.135 1.705 6.26 2.575l-1.57 1.56c-.433.432-.723.99-.688 1.61c.09 1.587.808 5 4.812 8.982c4.247 4.222 8.232 4.39 9.861 4.238c.516-.048.964-.31 1.325-.67l1.42-1.412c.96-.953.69-2.588-.538-3.255l-1.91-1.039c-.806-.437-1.787-.309-2.417.317"
            />
          </svg>
        </button>
        <button type="button" tabindex="-1">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path
              fill="currentColor"
              d="M5 5.5a2.75 2.75 0 0 0-2.75 2.75v7.5A2.75 2.75 0 0 0 5 18.5h8.5a2.75 2.75 0 0 0 2.75-2.75v-1.594l3.419 3.045c.805.717 2.081.145 2.081-.934V7.365c0-1.08-1.276-1.651-2.081-.934L16.25 9.476V8.25A2.75 2.75 0 0 0 13.5 5.5z"
            />
          </svg>
        </button>
        <button type="button" tabindex="-1">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path
              fill="currentColor"
              d="M9 15.25a1.25 1.25 0 1 1 2.5 0a1.25 1.25 0 0 1-2.5 0m0-5a1.25 1.25 0 1 1 2.5 0a1.25 1.25 0 0 1-2.5 0m0-5a1.249 1.249 0 1 1 2.5 0a1.25 1.25 0 1 1-2.5 0"
            />
          </svg>
        </button>
      </div>
    </div>

    <!-- Zone des messages -->
    <div ref="chatContainer" class="flex-1 px-8 py-6 overflow-y-auto flex flex-col gap-2">
      <div class="flex flex-col items-center">
        <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full mb-2">Aujourd'hui</span>
      </div>

      <div v-if="loadingMessages" class="text-center text-sm text-gray-400">Chargement des messages...</div>

      <div
        v-for="msg in messages"
        :key="msg.id"
        class="flex items-end gap-2"
        :class="msg.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
      >
        <!-- Avatar à gauche pour les messages reçus -->
        <img
          v-if="msg.sender_id !== currentUserId"
          :src="msg.sender_avatar || '/default-avatar.png'"
          class="w-8 h-8 rounded-full object-cover mb-1"
        />
        <!-- Bulle de message -->
        <div
          :class="[
            'max-w-xs px-4 py-2 rounded-2xl mb-1 shadow transition-colors relative group',
            msg.sender_id === currentUserId
              ? 'bg-violet-600 text-white rounded-br-none hover:bg-violet-700'
              : 'bg-gray-100 text-gray-800 rounded-bl-none border hover:bg-gray-200'
          ]"
        >
          <div>{{ msg.content }}</div>
          <div class="text-xs text-gray-400 mt-1 text-right">
            {{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
            <span v-if="msg.sender_id === currentUserId && msg.read_at" class="ml-1 text-violet-500" title="Lu">
              ✓
            </span>
          </div>

          <!-- Menu de suppression pour l'expéditeur -->
          <div v-if="msg.sender_id === currentUserId" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            <button
              @click.stop="toggleDeleteMenu(msg.id)"
              class="text-xs bg-white/90 hover:bg-white text-gray-600 hover:text-gray-800 rounded-full p-1.5 shadow-sm border border-gray-200"
              title="Options"
              data-delete-button
            >
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
              </svg>
            </button>

            <!-- Menu déroulant -->
            <div v-if="showDeleteMenu === msg.id" class="delete-menu absolute right-0 top-8 bg-white border border-gray-200 rounded-lg shadow-lg z-20 min-w-40">
              <button
                @click.stop="deleteMessage(msg.id, false)"
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-100"
              >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Supprimer pour moi
              </button>
              <button
                @click.stop="deleteMessage(msg.id, true)"
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
              >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                Supprimer pour tout le monde
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- Indicateur de frappe local (optionnel) -->
      <div v-if="isTyping && !remoteTyping" class="text-xs text-gray-400 mt-2 self-end">Vous écrivez...</div>
    </div>

    <!-- Formulaire d’envoi -->
    <div class="p-4 border-t flex items-center gap-2">
      <button class="text-gray-400 hover:text-gray-600" type="button" tabindex="-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24">
          <path
            fill="currentColor"
            d="M14.36 14.23a3.76 3.76 0 0 1-4.72 0a1 1 0 0 0-1.28 1.54a5.68 5.68 0 0 0 7.28 0a1 1 0 1 0-1.28-1.54M9 11a1 1 0 1 0-1-1a1 1 0 0 0 1 1m6-2a1 1 0 1 0 1 1a1 1 0 0 0-1-1m-3-7a10 10 0 1 0 10 10A10 10 0 0 0 12 2m0 18a8 8 0 1 1 8-8a8 8 0 0 1-8 8"
          />
        </svg>
      </button>
      <input
        type="text"
        placeholder="Écrire un message..."
        class="flex-1 px-4 py-2 rounded-full border bg-gray-100 focus:outline-none"
        v-model="newMessage"
        @keyup.enter="sendMessage"
        @input="handleTyping"
        :disabled="sending"
        autocomplete="off"
      />
      <button
        @click="sendMessage"
        class="bg-violet-600 text-white p-2 rounded-full hover:bg-violet-700"
        type="button"
        :disabled="sending || !newMessage.trim()"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M22 2L11 13"></path>
          <path d="M22 2l-7 20-4-9-9-4 20-7z"></path>
        </svg>
      </button>
    </div>
  </div>

  <div v-else class="flex-1 flex items-center justify-center text-gray-400 font-semibold">
    Sélectionnez une conversation
  </div>
</template>
