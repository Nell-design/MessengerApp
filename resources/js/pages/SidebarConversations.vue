<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { defineEmits } from 'vue';

function logout() {
    router.post(route('logout'), {}, {
            onFinish: () => {
                window.location.href = '/';
        }
    });
}

const conversations = [
    {
        id: 1,
        name: 'Marie Dupont',
        message: 'Salut, ça va ?',
        time: '12:30',
        active: true,
        online: true,
    },
    {
        id: 2,
        name: 'Jean Martin',
        message: 'À demain pour la réunion',
        time: '10:15',
        active: false,
        online: true,
    },
    {
        id: 3,
        name: 'Sophie Leroy',
        message: "J'ai envoyé le fichier",
        time: 'Hier',
        active: false,
        online: false,
    },
    {
        id: 4,
        name: 'Thomas Bernard',
        message: 'Merci pour ton aide !',
        time: 'Hier',
        active: false,
        online: true,
    },
];

// Responsive: detect mobile
const isMobile = ref(window.innerWidth < 640);
function handleResize() {
    isMobile.value = window.innerWidth < 640;
}
onMounted(() => {
    window.addEventListener('resize', handleResize);
});
onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});

const emit = defineEmits(['select-conversation']);
</script>

<template>
    <div
        :class="[
            'flex min-h-0 flex-col transition-all duration-200',
            isMobile ? 'fixed inset-0 z-30 h-full w-full rounded-none bg-white' : 'h-full w-80 rounded-l-xl bg-white shadow',
            $attrs.class,
        ]"
        :style="isMobile ? { left: 0, top: 0 } : {}"
    >
        <div class="flex items-center justify-between rounded-tl-xl bg-violet-600 px-6 py-4 text-white">
            <span class="text-lg font-bold">Messages</span>
            <button class="rounded-full bg-violet-500 p-2 hover:bg-violet-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="px-4 py-2">
            <input type="text" placeholder="Rechercher..." class="w-full rounded bg-gray-100 px-3 py-2 focus:outline-none" />
        </div>
        <div class="w-full flex-1 overflow-y-auto bg-white">
            <ul>
                <li
                    v-for="conv in conversations"
                    :key="conv.id"
                    :class="[
                        'flex cursor-pointer items-center px-4 py-3 hover:bg-gray-100',
                        conv.active ? 'border-l-4 border-violet-600 bg-violet-100' : '',
                        'w-full',
                    ]"
                    @click="emit('select-conversation', conv)"
                >
                    <div class="relative mr-3">
                        <div class="h-10 w-10 rounded-full bg-gray-200"></div>
                        <span v-if="conv.online" class="absolute right-0 bottom-0 h-3 w-3 rounded-full border-2 border-white bg-green-400"></span>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold" :class="conv.active ? 'text-violet-700' : ''">{{ conv.name }}</div>
                        <div class="truncate text-sm text-gray-500">{{ conv.message }}</div>
                    </div>
                    <div class="ml-2 text-xs text-gray-400">{{ conv.time }}</div>
                </li>
            </ul>
        </div>

        <div
            :class="[
                isMobile
                    ? 'fixed bottom-0 left-0 z-40 w-full rounded-none border-t bg-white p-4'
                    : 'z-10 mt-auto w-full rounded-bl-xl border-t bg-white p-4',
            ]"
        >
            <button
                @click="logout"
                class="logout-btn flex w-full items-center justify-center md:relative md:top-70 gap-2 rounded bg-red-70 px-4 py-2 font-semibold text-red-600 transition hover:bg-red-100"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"
                    />
                </svg>
                Déconnexion
            </button>
        </div>
    </div>
</template>
