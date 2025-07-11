<script setup lang="ts">
import SidebarConversations from './SidebarConversations.vue';
import ChatWindow from './ChatWindow.vue';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const selected = ref(null);
const user = usePage().props.auth.user;
const sidebarRef = ref();

// Fonction à passer à ChatWindow pour notifier l'envoi d'un message
function onMessageSent(conversationId: string | number, message: any) {
  sidebarRef.value?.moveConversationToTop(conversationId);
  sidebarRef.value?.updateLastMessage(conversationId, message);
}
</script>

<template>
    <div class="flex h-screen bg-gray-100 p-2 sm:p-6 rounded-xl shadow relative">
        <SidebarConversations
            ref="sidebarRef"
            @select-conversation="selected = $event"
            :selectedConversationId="selected && selected !== null && typeof selected === 'object' && 'id' in selected ? (selected as any).id : undefined"
            :currentUserId="user.id"
        />
        <ChatWindow
            v-if="selected"
            :conversation="selected"
            :currentUserId="user.id"
            :onMessageSent="onMessageSent"
        />
    </div>
</template>
