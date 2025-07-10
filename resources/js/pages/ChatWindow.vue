<script setup lang="ts">
const messages = [
    {
        id: 1,
        fromMe: false,
        text: "Salut ! Comment ça va ?",
        time: "12:30",
        avatar: true,
    },
    {
        id: 2,
        fromMe: false,
        text: "Très bien aussi ! Tu as fini le projet ?",
        time: "12:33",
        avatar: true,
    },
    {
        id: 3,
        fromMe: true,
        text: "Ça va bien merci ! Et toi ? 😊",
        time: "12:32",
        avatar: false,
    },
    {
        id: 4,
        fromMe: true,
        text: "Oui, je viens de terminer. Je t'envoie ça tout de suite.",
        time: "12:35",
        avatar: false,
    },
];
</script>

<template>
    <div class="flex-1 flex flex-col bg-white rounded-r-xl shadow h-full">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-200"></div>
                <div>
                    <div class="font-semibold">Marie Dupont</div>
                    <div class="text-xs text-gray-400">En ligne</div>
                </div>
            </div>
            <div class="flex gap-3 text-gray-400">
                <button><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 8h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h2"></path><path d="M15 3h-6a2 2 0 0 0-2 2v3h10V5a2 2 0 0 0-2-2z"></path></svg></button>
                <button><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 12h-4m0 0V8m0 4v4m-6-4h4m0 0V8m0 4v4"></path></svg></button>
                <button><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg></button>
            </div>
        </div>
        <div class="flex-1 px-8 py-6 overflow-y-auto flex flex-col gap-2">
            <div class="flex flex-col items-center">
                <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full mb-2">Aujourd'hui</span>
            </div>
            <div v-for="msg in messages" :key="msg.id" class="flex" :class="msg.fromMe ? 'justify-end' : 'justify-start'">
                <div v-if="!msg.fromMe" class="mr-2 w-8 h-8 rounded-full bg-gray-200"></div>
                <div :class="['px-4 py-2 rounded-lg mb-1', msg.fromMe ? 'bg-blue-50 text-right text-gray-800' : 'bg-white text-gray-800 border']">
                    <div>{{ msg.text }}</div>
                    <div class="text-xs text-gray-400 mt-1 text-right">{{ msg.time }}</div>
                </div>
            </div>
        </div>
        <div class="p-4 border-t flex items-center gap-2">
            <button class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path></svg>
            </button>
            <input type="text" placeholder="Écrire un message..." class="flex-1 px-4 py-2 rounded-full border bg-gray-100 focus:outline-none" />
            <button class="bg-violet-600 text-white p-2 rounded-full hover:bg-violet-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13"></path><path d="M22 2l-7 20-4-9-9-4 20-7z"></path></svg>
            </button>
        </div>
    </div>
</template>
