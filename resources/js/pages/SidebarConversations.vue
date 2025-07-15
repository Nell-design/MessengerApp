<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { defineEmits, onMounted, onUnmounted, ref, computed, watch } from 'vue';
import NotificationManager from '../components/NotificationManager.vue';
import { usePage } from '@inertiajs/vue3';
import UserInfo from '@/components/UserInfo.vue';

const emit = defineEmits(['select-conversation']);

// Récupérer l'utilisateur connecté
const page = usePage();
const user = page.props.auth.user;
const userId = user.id;

console.log('🔍 SidebarConversations: ID utilisateur connecté:', userId);

// Déconnexion
function logout() {
    router.post(route('logout'));
}

// Types
type Conversation = {
    id: number;
    name: string;
    avatar?: string;
    unread_count?: number;
    last_message?: {
        content: string;
        sender_id: number;
        sender_name?: string;
        created_at: string;
        is_read?: boolean;
    };
    message?: string; // legacy, peut être supprimé plus tard
    time: string;
};

type User = {
    id: number;
    name: string;
    avatar?: string;
};

// Liste des conversations (initialement vide ou préremplie selon les besoins)
const conversations = ref<Conversation[]>([]);

// Modal utilisateurs
const showUserModal = ref(false);
const allUsers = ref<User[]>([]);

// Référence au gestionnaire de notifications
const notificationManager = ref<InstanceType<typeof NotificationManager> | null>(null);

// CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (!csrfToken) {
    console.error('❌ CSRF token introuvable. Vérifie que la balise <meta name="csrf-token"> est présente.');
}

const fetchUsers = async () => {
    try {
        console.log("➡️ Requête GET /users/all...");
        const res = await fetch('/users/all', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) {
            const errorText = await res.text();
            console.error(`❌ Erreur ${res.status}: ${errorText}`);
            return;
        }

        const users = await res.json();
        console.log("✅ Utilisateurs récupérés :", users);
        allUsers.value = users;

    } catch (error) {
        console.error("❌ Erreur lors de la récupération des utilisateurs :", error);
    }
};

async function addConversation(user: User) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.error('❌ CSRF token introuvable.');
            return;
        }

        console.log('📤 Envoi POST /conversations/start pour user :', user);

        const res = await fetch('/conversations/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ user_id: user.id }),
        });

        if (!res.ok) {
            const errText = await res.text();
            console.error(`❌ Erreur API ${res.status}:`, errText);
            return;
        }

        const data = await res.json();
        console.log('✅ Conversation créée :', data);

        const exists = conversations.value.find(c => c.id === data.id);
        if (!exists) {
            conversations.value.push({
                id: data.id,
                name: data.name,
                avatar: data.avatar || '/default-avatar.png',
                message: data.message,
                time: data.time,
            });
            console.log('🧩 Conversations après ajout :', conversations.value);
        }

        search.value = '';
        showUserModal.value = false;
        emit('select-conversation', data);

    } catch (error) {
        console.error('❌ Erreur lors de la création de la conversation :', error);
    }
}

// Nouvelle fonction pour naviguer vers une conversation
function goToConversation(conversationId: number) {
    if (typeof conversationId !== 'number' || isNaN(conversationId)) {
        console.error('❌ conversationId invalide:', conversationId);
        return;
    }
    const conv = conversations.value.find(c => c.id === conversationId);
    if (!conv) {
        console.warn('⚠️ Conversation non trouvée pour id:', conversationId);
        return;
    }
    console.log('➡️ Sélection de la conversation:', conv);

    // Marquer la conversation comme lue lors de la sélection
    if (notificationManager.value) {
        notificationManager.value.markConversationAsRead(conversationId);
    }

    // Réinitialiser le compteur de messages non lus pour cette conversation
    updateUnreadCount(conversationId, false);

    emit('select-conversation', conv);
}

async function fetchConversations() {
    try {
        console.log("➡️ Requête GET /conversations...");
        const res = await fetch('/conversations', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) {
            const errorText = await res.text();
            console.error(`❌ Erreur ${res.status}: ${errorText}`);
            return;
        }
        const data = await res.json();
        console.log("✅ Conversations récupérées :", data);
        conversations.value = data.map((conv: any) => ({
            id: conv.id,
            name: conv.name,
            avatar: conv.avatar || '/default-avatar.png',
            message: conv.message,
            time: conv.time,
            unread_count: conv.unread_count || 0,
            last_message: conv.last_message,
        }));
        console.log("🧩 Conversations après récupération :", conversations.value);
    } catch (error) {
        console.error("❌ Erreur lors de la récupération des conversations :", error);
    }
}

// Détection mobile
const isMobile = ref(window.innerWidth < 640);

function handleResize() {
    isMobile.value = window.innerWidth < 640;
}

// Gestionnaire pour les nouveaux messages reçus
function handleNewMessage(data: { conversationId: number; message: any }) {
    console.log('🔔 Nouveau message reçu dans la sidebar:', data);

    // Mettre à jour le dernier message de la conversation
    updateLastMessage(data.conversationId, data.message);

    // Incrémenter le compteur de messages non lus
    updateUnreadCount(data.conversationId, true);

    // Déplacer la conversation en haut de la liste
    moveConversationToTop(data.conversationId);
}

// Gestionnaire pour l'incrémentation du compteur
function handleIncrementUnread(data: { conversationId: number; message: any }) {
    console.log('📈 Incrémentation du compteur pour la conversation:', data.conversationId);
    updateUnreadCount(data.conversationId, true);
    updateLastMessage(data.conversationId, data.message);
    moveConversationToTop(data.conversationId);
}

// Fonction pour déplacer une conversation en haut de la liste
function moveConversationToTop(conversationId: number) {
    const index = conversations.value.findIndex(c => c.id === conversationId);
    if (index > 0) {
        const conversation = conversations.value.splice(index, 1)[0];
        conversations.value.unshift(conversation);
    }
}

// Fonction pour mettre à jour le dernier message d'une conversation
function updateLastMessage(conversationId: number, message: any) {
    const idx = conversations.value.findIndex(c => c.id === conversationId);
    if (idx > -1) {
        conversations.value[idx].last_message = {
            content: message.content,
            sender_id: message.sender_id,
            sender_name: message.sender?.name || 'Utilisateur',
            created_at: message.created_at,
            is_read: false,
        };
        conversations.value[idx].time = new Date(message.created_at).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

onMounted(() => {
    console.log("🔍 SidebarConversations: onMounted - ID utilisateur connecté:", props.currentUserId);

    fetchUsers();
    fetchConversations();
    window.addEventListener('resize', handleResize);
    isMobile.value = window.innerWidth < 640;

    // Écouter les mises à jour de conversations en temps réel
    setupConversationListeners();

    // Écouter sur le canal général des conversations
    (window as any).Echo.channel(`conversation`)
        .listen('MessageSentEvent', (event: any) => {
            if (props.currentUserId === event.receiver_id) {
                console.log("📨 Message reçu pour l'utilisateur actuel sur canal conversation");

                // Récupérer la conversation et mettre à jour le dernier message
                if (event.message && event.conversation_id) {
                    const conversationId = event.conversation_id;
                    const message = event.message;

                    console.log('📨 Mise à jour de la conversation:', conversationId, 'avec le message:', message);

                    // Trouver la conversation dans la liste
                    const index = conversations.value.findIndex(c => c.id === conversationId);

                    if (index !== -1) {
                        // Mettre à jour le dernier message
                        conversations.value[index].last_message = {
                            content: message.content,
                            sender_id: message.sender_id,
                            sender_name: message.sender?.name || 'Utilisateur',
                            created_at: message.created_at,
                            is_read: false,
                        };

                        // Mettre à jour l'heure du dernier message
                        conversations.value[index].time = new Date(message.created_at).toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        // Mettre à jour le message legacy pour compatibilité
                        conversations.value[index].message = message.content;

                        // Incrémenter le compteur de messages non lus si le message n'est pas de l'utilisateur actuel
                        if (message.sender_id !== props.currentUserId) {
                            conversations.value[index].unread_count = (conversations.value[index].unread_count || 0) + 1;
                        }

                        // Déplacer la conversation en haut de la liste
                        if (index > 0) {
                            const conversation = conversations.value.splice(index, 1)[0];
                            conversations.value.unshift(conversation);
                            console.log('📈 Conversation déplacée en haut après nouveau message:', conversationId);
                        }

                        console.log('✅ Conversation mise à jour avec nouveau message:', conversationId);
                    } else {
                        // Si la conversation n'existe pas dans la liste, la récupérer depuis le serveur
                        console.log('🔄 Conversation non trouvée dans la liste, récupération depuis le serveur...');
                        fetchConversations();
                    }
                }
            }
        });
});


onUnmounted(() => {
    window.removeEventListener('resize', handleResize);

    // Déconnexion des listeners Echo
    if ((window as any).Echo) {
        (window as any).Echo.leave(`user.${props.currentUserId}`);
    }
});

// Fonction pour configurer les listeners de conversations
function setupConversationListeners() {
    if (!(window as any).Echo) {
        console.warn('Echo non disponible pour les listeners de conversations');
        return;
    }

    // console.log('📡 SidebarConversations: Connexion au canal user.' + props.conversationId);

    (window as any).Echo.channel(`user.${props.currentUserId}`)
        .listen('conversation.updated', (event: any) => {
            console.log('🔄 Conversation mise à jour reçue dans SidebarConversations:', event);

            if (event.action === 'created') {
                // Nouvelle conversation créée
                const newConversation = event.conversation;
                const exists = conversations.value.find(c => c.id === newConversation.id);

                if (!exists) {
                    conversations.value.unshift({
                        id: newConversation.id,
                        name: newConversation.name,
                        avatar: newConversation.avatar,
                        message: newConversation.last_message?.content || '',
                        time: newConversation.time,
                        unread_count: newConversation.unread_count || 0,
                        last_message: newConversation.last_message,
                    });
                    console.log('✅ Nouvelle conversation ajoutée à la liste:', newConversation);
                }
            } else if (event.action === 'updated') {
                // Conversation mise à jour (nouveau message, etc.)
                const updatedConversation = event.conversation;
                const index = conversations.value.findIndex(c => c.id === updatedConversation.id);

                if (index !== -1) {
                    // Mettre à jour la conversation
                    conversations.value[index] = {
                        ...conversations.value[index],
                        name: updatedConversation.name,
                        avatar: updatedConversation.avatar,
                        message: updatedConversation.last_message?.content || '',
                        time: updatedConversation.time,
                        unread_count: updatedConversation.unread_count || 0,
                        last_message: updatedConversation.last_message,
                    };

                    // Déplacer la conversation en haut de la liste si elle n'est pas déjà en première position
                    if (index > 0) {
                        const conversation = conversations.value.splice(index, 1)[0];
                        conversations.value.unshift(conversation);
                        console.log('📈 Conversation déplacée en haut de la liste:', updatedConversation.id);
                    }

                    console.log('✅ Conversation mise à jour dans la liste:', updatedConversation);
                } else {
                    // Si la conversation n'existe pas dans la liste, l'ajouter
                    conversations.value.unshift({
                        id: updatedConversation.id,
                        name: updatedConversation.name,
                        avatar: updatedConversation.avatar,
                        message: updatedConversation.last_message?.content || '',
                        time: updatedConversation.time,
                        unread_count: updatedConversation.unread_count || 0,
                        last_message: updatedConversation.last_message,
                    });
                    console.log('✅ Conversation ajoutée à la liste (mise à jour):', updatedConversation);
                }
            }
        })
        .listen('MessageSentEvent', (event: any) => {
            console.log('📨 MessageSentEvent reçu dans SidebarConversations:', event);

            // Mettre à jour la conversation avec le nouveau message
            if (event.message && event.conversation_id) {
                const conversationId = event.conversation_id;
                const message = event.message;

                // Trouver la conversation dans la liste
                const index = conversations.value.findIndex(c => c.id === conversationId);

                if (index !== -1) {
                    // Mettre à jour le dernier message
                    conversations.value[index].last_message = {
                        content: message.content,
                        sender_id: message.sender_id,
                        sender_name: message.sender?.name || 'Utilisateur',
                        created_at: message.created_at,
                        is_read: false,
                    };
                    conversations.value[index].time = new Date(message.created_at).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    // Incrémenter le compteur de messages non lus si le message n'est pas de l'utilisateur actuel
                    if (message.sender_id !== props.currentUserId) {
                        conversations.value[index].unread_count = (conversations.value[index].unread_count || 0) + 1;
                    }

                    // Déplacer la conversation en haut de la liste
                    if (index > 0) {
                        const conversation = conversations.value.splice(index, 1)[0];
                        conversations.value.unshift(conversation);
                        console.log('📈 Conversation déplacée en haut après nouveau message:', conversationId);
                    }

                    console.log('✅ Conversation mise à jour avec nouveau message:', conversationId);
                }
            }
        });
}

// Recherche
const search = ref('');
const filteredConversations = computed(() => {
    if (!search.value.trim()) return conversations.value;
    return conversations.value.filter(conv =>
        conv.name.toLowerCase().includes(search.value.toLowerCase()) ||
        (conv.last_message?.content || '').toLowerCase().includes(search.value.toLowerCase())
    );
});

watch(filteredConversations, (val) => {
    console.log('📌 Conversations affichées après filtre :', val);
});

const props = defineProps({
    selectedConversationId: {
        type: Number,
        required: false,
    },
    currentUserId: {
        type: Number,
        required: true,
    },
    conversationId: {
        type: Number,
        required: false,
    },
});

// Méthode pour mettre à jour le compteur de messages non lus
function updateUnreadCount(conversationId: number, increment: boolean = true) {
    const idx = conversations.value.findIndex(c => c.id === conversationId);
    if (idx > -1) {
        if (increment) {
            conversations.value[idx].unread_count = (conversations.value[idx].unread_count || 0) + 1;
        } else {
            conversations.value[idx].unread_count = 0;
        }
    }
}

// Expose la méthode pour le parent (Dashboard.vue)
defineExpose({
  refreshConversations: fetchConversations
});

</script>

<template>
    <div :class="[
        'flex min-h-0 flex-col transition-all duration-200',
        isMobile ? 'fixed inset-0 z-30 h-full w-full rounded-none bg-white' : 'h-full w-80 rounded-l-xl bg-white shadow',
        $attrs.class
    ]" :style="isMobile ? { left: 0, top: 0 } : {}">
        <!-- Gestionnaire de notifications (invisible) -->
        <NotificationManager ref="notificationManager" :current-user-id="currentUserId"
            @increment-unread="handleIncrementUnread" @new-message="handleNewMessage" />

        <div class="flex items-center gap-3 px-6 py-4 border-b">
            <!-- Utilisation de UserInfo pour l'utilisateur connecté (affiche initiales si pas d'avatar) -->
            <UserInfo :user="user" />
        </div>

        <div class="flex items-center justify-between rounded-tl-xl bg-violet-600 px-6 py-4 text-white">
            <span class="text-lg font-bold">Messages</span>
            <button class="rounded-full bg-violet-500 p-2 hover:bg-violet-700"
                @click="showUserModal = true; fetchUsers()">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>

        <div class="px-4 py-2">
            <input type="text" placeholder="Rechercher..."
                class="w-full rounded bg-gray-100 px-3 py-2 focus:outline-none" v-model="search" />
        </div>

        <div class="w-full flex-1 overflow-y-auto bg-white">
            <ul class="space-y-2 p-2">
                <li v-for="conv in filteredConversations" :key="conv.id" :id="'conv-' + conv.id"
                    class="flex cursor-pointer items-start px-3 py-3 font-semibold w-full transition-all duration-150 rounded-xl hover:bg-gray-50"
                    :class="[
                        conv.id === props.selectedConversationId ? 'bg-violet-50 border-l-4 border-violet-600 shadow-sm' : 'hover:bg-gray-50',
                        conv.id === props.selectedConversationId ? 'text-violet-800' : 'text-gray-800'
                    ]" @click="goToConversation(conv.id)">
                    <!-- Avatar de la conversation : initiales si pas d'avatar -->
                    <div class="relative mr-3 flex-shrink-0">
                        <template v-if="!conv.avatar || conv.avatar === '/default-avatar.png'">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center bg-blue-600 text-white font-bold text-lg object-cover">
                                {{ conv.name.substring(0, 2).toUpperCase() }}
                            </div>
                        </template>
                        <template v-else>
                            <img :src="conv.avatar" class="h-10 w-10 rounded-full object-cover border-2"
                                :class="conv.id === props.selectedConversationId ? 'border-violet-600' : 'border-gray-200'" />
                        </template>
                    </div>

                    <!-- Contenu principal -->
                    <div class="flex-1 min-w-0">
                        <!-- En-tête avec nom et heure -->
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-sm truncate">{{ conv.name }}</span>
                            <span class="text-xs text-gray-400 ml-2 flex-shrink-0">{{ conv.time }}</span>
                        </div>

                        <!-- Dernier message avec compteur -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-gray-500 truncate">
                                    <template v-if="conv.last_message">
                                        <span class="truncate">{{ conv.last_message.content }}</span>
                                    </template>
                                    <template v-else>
                                        <span class="italic text-gray-400">Aucun message</span>
                                    </template>
                                </div>
                            </div>

                            <!-- Badge de messages non lus à la fin de la ligne -->
                            <div v-if="conv.unread_count && conv.unread_count > 0" class="ml-2 flex-shrink-0">
                                <span
                                    class="bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-5 text-center inline-block font-medium">
                                    {{ conv.unread_count > 99 ? '99+' : conv.unread_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <div :class="[
            isMobile
                ? 'fixed bottom-0 left-0 z-40 w-full rounded-none border-t bg-white p-4'
                : 'z-10 mt-auto w-full rounded-bl-xl border-t bg-white p-4'
        ]">
            <button @click="logout"
                class="logout-btn flex w-full items-center justify-center gap-2 rounded bg-red-70 px-4 py-2 font-semibold text-red-600 transition hover:bg-red-100">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
                </svg>
                Déconnexion
            </button>
        </div>
    </div>

    <!-- Modal utilisateur -->
    <div v-if="showUserModal" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center">
        <div class="bg-white w-full max-w-md rounded shadow-lg p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold">Ajouter un utilisateur</h2>
                <button @click="showUserModal = false" class="text-gray-600 hover:text-black">X</button>
            </div>
            <div class="max-h-72 overflow-y-auto">
                <ul>
                    <li v-for="user in allUsers" :key="user.id"
                        class="flex items-center gap-3 px-3 py-2 hover:bg-gray-100 cursor-pointer"
                        @click="addConversation(user)">
                        <template v-if="!user.avatar || user.avatar === '/default-avatar.png'">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center bg-blue-600 text-white font-bold text-lg object-cover">
                                {{ (user.name || '').substring(0, 2).toUpperCase() }}
                            </div>
                        </template>
                        <template v-else>
                            <img :src="user.avatar" class="h-10 w-10 rounded-full object-cover" />
                        </template>
                        <span>{{ user.name }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
