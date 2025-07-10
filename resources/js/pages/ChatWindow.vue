<script setup lang="ts">
import { defineProps } from 'vue';

const props = defineProps({
    onOpenSidebar: {
        type: Function,
        required: false,
    },
});

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
                <!-- Mobile burger button in chat header -->
                <button
                    v-if="props.onOpenSidebar"
                    class="sm:hidden mr-2 bg-transparent text-white p-2 rounded"
                    @click="props.onOpenSidebar()"
                >
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="24" viewBox="0 0 12 24"><path fill="#0d0c0c" fill-rule="evenodd" d="m3.343 12l7.071 7.071L9 20.485l-7.778-7.778a1 1 0 0 1 0-1.414L9 3.515l1.414 1.414z"/></svg>
                </button>
                <div class="w-10 h-10 rounded-full bg-gray-200"></div>
                <div>
                    <div class="font-semibold">Marie Dupont</div>
                    <div class="text-xs text-gray-400">En ligne</div>
                </div>
            </div>
            <div class="flex gap-3 text-gray-400">
                <button><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="m16.556 12.906l-.455.453s-1.083 1.076-4.038-1.862s-1.872-4.014-1.872-4.014l.286-.286c.707-.702.774-1.83.157-2.654L9.374 2.86C8.61 1.84 7.135 1.705 6.26 2.575l-1.57 1.56c-.433.432-.723.99-.688 1.61c.09 1.587.808 5 4.812 8.982c4.247 4.222 8.232 4.39 9.861 4.238c.516-.048.964-.31 1.325-.67l1.42-1.412c.96-.953.69-2.588-.538-3.255l-1.91-1.039c-.806-.437-1.787-.309-2.417.317"/></svg></button>
                <button><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M5 5.5a2.75 2.75 0 0 0-2.75 2.75v7.5A2.75 2.75 0 0 0 5 18.5h8.5a2.75 2.75 0 0 0 2.75-2.75v-1.594l3.419 3.045c.805.717 2.081.145 2.081-.934V7.365c0-1.08-1.276-1.651-2.081-.934L16.25 9.476V8.25A2.75 2.75 0 0 0 13.5 5.5z"/></svg></button>
                <button><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M9 15.25a1.25 1.25 0 1 1 2.5 0a1.25 1.25 0 0 1-2.5 0m0-5a1.25 1.25 0 1 1 2.5 0a1.25 1.25 0 0 1-2.5 0m0-5a1.249 1.249 0 1 1 2.5 0a1.25 1.25 0 1 1-2.5 0"/></svg></button>
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
                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24"><path fill="currentColor" d="M14.36 14.23a3.76 3.76 0 0 1-4.72 0a1 1 0 0 0-1.28 1.54a5.68 5.68 0 0 0 7.28 0a1 1 0 1 0-1.28-1.54M9 11a1 1 0 1 0-1-1a1 1 0 0 0 1 1m6-2a1 1 0 1 0 1 1a1 1 0 0 0-1-1m-3-7a10 10 0 1 0 10 10A10 10 0 0 0 12 2m0 18a8 8 0 1 1 8-8a8 8 0 0 1-8 8"/></svg>
            </button>
            <input type="text" placeholder="Écrire un message..." class="flex-1 px-4 py-2 rounded-full border bg-gray-100 focus:outline-none" />
            <button class="bg-violet-600 text-white p-2 rounded-full hover:bg-violet-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13"></path><path d="M22 2l-7 20-4-9-9-4 20-7z"></path></svg>
            </button>
        </div>
    </div>
</template>
