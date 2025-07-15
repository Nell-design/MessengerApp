<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
</script>

<template>
  <Head title="Welcome" />
  <div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 dark:bg-[#0a0a0a] px-4 py-8 relative">
    <!-- Ombre floue violette accentuée derrière le formulaire -->
    <div class="absolute z-0 w-[520px] h-[400px] rounded-3xl bg-violet-500/60 dark:bg-violet-700/70 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none select-none" style="filter: blur(96px);"></div>
    <div class="w-full max-w-md bg-white dark:bg-[#161615] rounded-2xl shadow-xl p-8 flex flex-col items-center gap-8 z-10 relative">
      <div class="text-center">
        <h1 class="text-3xl font-extrabold mb-2 tracking-tight text-violet-700 dark:text-violet-400">Welcome to MessengerApp</h1>
        <p class="text-base opacity-90 font-medium text-gray-500 dark:text-gray-400">Connect, chat and share instantly.<br>Join us or log in to start the conversation!</p>
      </div>
      <div class="flex flex-col sm:flex-row gap-4 w-full justify-center">
        <Link :href="route('login')" class="flex-1">
          <Button variant="default" size="lg" class="w-full text-base font-bold py-3 shadow-md">
            Log in
          </Button>
        </Link>
        <Link :href="route('register')" class="flex-1">
          <Button variant="outline" size="lg" class="w-full text-base font-bold py-3 border-violet-600 text-violet-700 hover:bg-violet-50 dark:border-violet-400 dark:text-violet-300 dark:hover:bg-violet-900/20">
            Register
          </Button>
        </Link>
      </div>
      <div class="w-full flex flex-col gap-2 mt-4">
        <a href="https://laravel.com/docs" target="_blank" class="text-center text-sm text-violet-600 dark:text-violet-400 underline hover:opacity-80 transition">Laravel Documentation</a>
        <a href="https://laracasts.com" target="_blank" class="text-center text-sm text-violet-600 dark:text-violet-400 underline hover:opacity-80 transition">Laracasts Tutorials</a>
      </div>
    </div>
  </div>
</template>
