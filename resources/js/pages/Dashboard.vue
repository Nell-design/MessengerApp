<script setup lang="ts">
import ChatWindow from './ChatWindow.vue';
import SidebarConversations from './SidebarConversations.vue';


</script>

<template>
    <div class="flex h-[90vh] bg-gray-100 p-6 rounded-xl shadow">
        <SidebarConversations />
        <ChatWindow />
    </div>
</template>
