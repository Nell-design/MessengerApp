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
        name: "Marie Dupont",
        message: "Salut, ça va ?",
        time: "12:30",
        active: true,
        online: true,
    },
    {
        id: 2,
        name: "Jean Martin",
        message: "À demain pour la réunion",
        time: "10:15",
        active: false,
        online: true,
    },
    {
        id: 3,
        name: "Sophie Leroy",
        message: "J'ai envoyé le fichier",
        time: "Hier",
        active: false,
        online: false,
    },
    {
        id: 4,
        name: "Thomas Bernard",
        message: "Merci pour ton aide !",
        time: "Hier",
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
        'flex flex-col min-h-0 transition-all duration-200',
        isMobile ? 'fixed inset-0 w-full h-full z-30 bg-white rounded-none' : 'w-80 h-full bg-white rounded-l-xl shadow',
        $attrs.class
      ]"
      :style="isMobile ? { left: 0, top: 0 } : {}"
    >
        <div class="bg-violet-600 text-white px-6 py-4 rounded-tl-xl flex items-center justify-between">
            <span class="font-bold text-lg">Messages</span>
            <button class="bg-violet-500 rounded-full p-2 hover:bg-violet-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div class="px-4 py-2">
            <input type="text" placeholder="Rechercher..." class="w-full px-3 py-2 rounded bg-gray-100 focus:outline-none" />
        </div>
        <div class="flex-1 overflow-y-auto w-full bg-white">
            <ul>
                <li v-for="conv in conversations" :key="conv.id"
                    :class="[
                        'flex items-center px-4 py-3 cursor-pointer hover:bg-gray-100',
                        conv.active ? 'bg-violet-100 border-l-4 border-violet-600' : '',
                        'w-full'
                    ]"
                    @click="emit('select-conversation', conv)"
                >
                    <div class="relative mr-3">
                        <div class="w-10 h-10 rounded-full bg-gray-200"></div>
                        <span v-if="conv.online" class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></span>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold" :class="conv.active ? 'text-violet-700' : ''">{{ conv.name }}</div>
                        <div class="text-sm text-gray-500 truncate">{{ conv.message }}</div>
                    </div>
                    <div class="ml-2 text-xs text-gray-400">{{ conv.time }}</div>
                </li>
            </ul>
        </div>

        <div
          :class="[
            isMobile
              ? 'fixed bottom-0 left-0 w-full p-4 border-t bg-white rounded-none z-40'
              : 'mt-auto w-full p-4 border-t bg-white rounded-bl-xl z-10'
          ]"
        >
          <button
            @click="logout"
            class="logout-btn w-full flex items-center justify-center gap-2 px-4 py-2 rounded bg-red-50 text-red-600 font-semibold hover:bg-red-100 transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
            </svg>
            Déconnexion
          </button>
        </div>
    </div>
</template>
