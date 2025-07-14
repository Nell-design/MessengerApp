<script setup lang="ts">
import SidebarConversations from './SidebarConversations.vue';
import ChatWindow from './ChatWindow.vue';
import NoMessage from './NoMessage.vue';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const selected = ref(null);
const user = usePage().props.auth.user;
const sidebarRef = ref();

// Redirection immédiate si l'utilisateur n'est plus connecté (sécurité côté client)
watch(
  () => user,
  (newUser) => {
    if (!newUser) {
      router.visit('/', { replace: true });
    }
  },
  { immediate: true }
);

// Fonction à passer à ChatWindow pour notifier l'envoi d'un message
function onMessageSent(conversationId: string | number, message: any) {
  sidebarRef.value?.moveConversationToTop(conversationId);
  sidebarRef.value?.updateLastMessage(conversationId, message);
}

// Fonction pour désélectionner la conversation (utile pour le retour mobile)
function openSidebar() {
  selected.value = null;
}

// Détection du mode mobile (largeur < 640px)
const isMobile = ref(window.innerWidth < 640);

function handleResize() {
  isMobile.value = window.innerWidth < 640;
}

onMounted(() => window.addEventListener('resize', handleResize));
onUnmounted(() => window.removeEventListener('resize', handleResize));

// Affichage conditionnel :
// - Sur desktop : sidebar + chat/no-message côte à côte
// - Sur mobile :
//    - Si aucune conversation sélectionnée : sidebar seule
//    - Si une conversation sélectionnée : chat/no-message seul
const showSidebar = computed(() => !isMobile.value || !selected.value);
const showChatArea = computed(() => !isMobile.value || selected.value);
</script>

<template>
    <div class="flex h-screen bg-gray-100 p-2 sm:p-6 rounded-xl shadow relative">
        <!-- Sidebar visible sur desktop OU sur mobile si aucune conversation sélectionnée -->
        <SidebarConversations
            v-if="showSidebar"
            ref="sidebarRef"
            @select-conversation="selected = $event"
            :selectedConversationId="selected && selected !== null && typeof selected === 'object' && 'id' in selected ? (selected as any).id : undefined"
            :currentUserId="user.id"
        />
        <!-- Zone principale (chat ou message d'absence) visible sur desktop OU sur mobile si une conversation sélectionnée -->
        <div v-if="showChatArea" class="flex-1 flex flex-col items-center justify-center h-full">
            <!-- Affiche ChatWindow si une conversation est sélectionnée, sinon NoMessage -->
            <ChatWindow
                v-if="selected"
                :conversation="selected"
                :currentUserId="user.id"
                :onMessageSent="onMessageSent"
                :onOpenSidebar="openSidebar"
            />
            <NoMessage v-else class="flex-1 flex flex-col items-center justify-center h-full" />
        </div>
    </div>

</template>
