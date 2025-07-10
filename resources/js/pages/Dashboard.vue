<script setup lang="ts">
import ChatWindow from './ChatWindow.vue';
import SidebarConversations from './SidebarConversations.vue';
import { ref } from 'vue';

const sidebarOpen = ref(false);

function openSidebar() {
    sidebarOpen.value = true;
}
</script>

<template>
    <div class="flex h-screen bg-gray-100 p-2 sm:p-6 rounded-xl shadow relative">
        <!-- Burger menu (mobile only) -->
        <!--
        <button
            class="sm:hidden absolute top-4 left-4 z-20 bg-violet-600 text-white p-2 rounded"
            @click="sidebarOpen = !sidebarOpen"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        -->

        <!-- Sidebar -->
        <SidebarConversations
            :class="[
                'transition-all duration-200',
                sidebarOpen ? 'block fixed inset-0 z-30 sm:static sm:block' : 'hidden sm:block'
            ]"
            @click.self="sidebarOpen = false"
            @select-conversation="sidebarOpen = false"
        />

        <!-- Overlay for mobile -->
        <div
            v-if="sidebarOpen"
            class="fixed top-0 left-0 w-full h-full bg-black bg-opacity-30 z-20 sm:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Chat window -->
        <div class="flex-1 flex flex-col">
            <ChatWindow :onOpenSidebar="openSidebar" />
        </div>
    </div>
</template>
