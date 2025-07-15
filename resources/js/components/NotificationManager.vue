<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

interface NotificationData {
  message: any;
  receiver_id: number;
  conversation_id: number;
  sender_name: string;
  conversation_name: string;
}

interface MessageSentEvent {
  message: any;
  receiver_id: number;
  conversation_id: number;
  sender_name: string;
  conversation_name: string;
}

const props = defineProps({
  currentUserId: {
    type: Number,
    required: true,
  },
  openConversationId: {
    type: Number,
    required: false,
  },
});

const emit = defineEmits(['increment-unread', 'new-message']);

const unreadCount = ref(0);
const notifications = ref<any[]>([]);

// Demander la permission pour les notifications push
async function requestNotificationPermission() {
  if ('Notification' in window) {
    if (Notification.permission === 'default') {
      const permission = await Notification.requestPermission();
      console.log('Permission notifications:', permission);
    }
  }
}

// Afficher une notification push
function showPushNotification(event: MessageSentEvent) {
  if (!('Notification' in window) || Notification.permission !== 'granted') {
    return;
  }

  // Ne pas afficher de notification si l'utilisateur est le destinataire
  if (event.receiver_id === props.currentUserId) {
    const notification = new Notification(event.sender_name, {
      body: event.message.content,
      icon: '/favicon.ico',
      tag: `message-${event.conversation_id}`,
      requireInteraction: false,
      silent: false,
    });

    // Gérer le clic sur la notification
    notification.onclick = function() {
      window.focus();
      // Émettre un événement pour ouvrir la conversation
      emit('new-message', {
        conversationId: event.conversation_id,
        message: event.message
      });
      notification.close();
    };

    // Fermer automatiquement après 5 secondes
    setTimeout(() => {
      notification.close();
    }, 5000);
  }
}

// Écouter les événements de nouveaux messages
function setupEchoListeners() {
  if (!(window as any).Echo) {
    console.warn('Echo non disponible');
    return;
  }

  console.log('📡 NotificationManager: Connexion au canal user.' + props.currentUserId);
  
  // Écouter les messages envoyés sur le canal utilisateur
  (window as any).Echo.channel(`user.${props.currentUserId}`)
    .listen('MessageSentEvent', (event: MessageSentEvent) => {
      // Afficher la notification push toujours
      showPushNotification(event);

      // Incrémenter le compteur seulement si la conversation n'est pas ouverte
      if (!(props.openConversationId && event.conversation_id === props.openConversationId)) {
        emit('increment-unread', {
          conversationId: event.conversation_id,
          message: event.message
        });
      } else {
        // Optionnel : marquer comme lu côté serveur
        markConversationAsRead(event.conversation_id);
      }
    })
    .listen('conversation.updated', (event: any) => {
      console.log('🔄 Conversation mise à jour reçue sur user.' + props.currentUserId + ':', event);
      
      // Émettre l'événement vers le parent pour mise à jour de la sidebar
      emit('new-message', {
        conversationId: event.conversation.id,
        message: event.conversation.last_message,
        action: event.action
      });
    });
}

// Charger le nombre de notifications non lues
async function loadUnreadCount() {
  try {
    const response = await fetch('/notifications/unread-count', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      const data = await response.json();
      unreadCount.value = data.unread_count;
    }
  } catch (error) {
    console.error('Erreur lors du chargement du compteur de notifications:', error);
  }
}

// Marquer une conversation comme lue
async function markConversationAsRead(conversationId: number) {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) return;

    const response = await fetch(`/notifications/conversation/${conversationId}/read`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      const data = await response.json();
      unreadCount.value = data.unread_count;
    }
  } catch (error) {
    console.error('Erreur lors du marquage comme lu:', error);
  }
}

// Marquer toutes les notifications comme lues
async function markAllAsRead() {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) return;

    const response = await fetch('/notifications/mark-all-read', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      unreadCount.value = 0;
    }
  } catch (error) {
    console.error('Erreur lors du marquage de toutes les notifications:', error);
  }
}

onMounted(async () => {
  await requestNotificationPermission();
  await loadUnreadCount();
  setupEchoListeners();
});

onUnmounted(() => {
  if ((window as any).Echo) {
    (window as any).Echo.leave(`user.${props.currentUserId}`);
  }
});

// Exposer les méthodes pour utilisation externe
defineExpose({
  markConversationAsRead,
  markAllAsRead,
  loadUnreadCount,
  unreadCount,
});
</script>

<template>
  <!-- Ce composant ne rend rien visuellement, il gère seulement les notifications -->
</template> 