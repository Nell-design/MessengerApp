<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

// Pour cohérence avec Login.vue (UI uniquement)
const socialProviders = [
    { name: 'Facebook', icon: 'facebook', color: 'text-blue-600', href: '#' },
    { name: 'Google', icon: 'google', color: 'text-red-500', href: '#' },
    { name: 'Twitter', icon: 'twitter', color: 'text-sky-500', href: '#' },
];
</script>

<template>
    <Head title="Register" />
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 px-2">
        <div class="w-full max-w-md relative z-10">
            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-xl px-6 py-8 mt-20 flex flex-col gap-6 relative">
                <!-- Titre et sous-titre -->
                <div class="text-center mb-4">
                    <h1 class="text-3xl font-extrabold mb-2 tracking-tight text-violet-700">Sign Up</h1>
                    <p class="text-base opacity-90 font-medium text-gray-500">Create your account to get started.</p>
                </div>
                <div class="flex flex-col gap-4">
                    <div>
                        <Label for="name" class="mb-1 font-semibold text-gray-700">Name</Label>
                        <Input id="name" type="text" required autofocus autocomplete="name" v-model="form.name" placeholder="Full name" class="rounded-lg border-gray-200 focus:border-violet-500 focus:ring-violet-500" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <Label for="email" class="mb-1 font-semibold text-gray-700">Email</Label>
                        <Input id="email" type="email" required autocomplete="email" v-model="form.email" placeholder="email@example.com" class="rounded-lg border-gray-200 focus:border-violet-500 focus:ring-violet-500" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div>
                        <Label for="password" class="mb-1 font-semibold text-gray-700">Password</Label>
                        <Input id="password" type="password" required autocomplete="new-password" v-model="form.password" placeholder="Password" class="rounded-lg border-gray-200 focus:border-violet-500 focus:ring-violet-500" />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div>
                        <Label for="password_confirmation" class="mb-1 font-semibold text-gray-700">Confirm Password</Label>
                        <Input id="password_confirmation" type="password" required autocomplete="new-password" v-model="form.password_confirmation" placeholder="Confirm password" class="rounded-lg border-gray-200 focus:border-violet-500 focus:ring-violet-500" />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>
                </div>
                <Button type="submit" class="w-full rounded-lg bg-violet-600 hover:bg-violet-700 text-white font-bold py-3 text-base flex items-center justify-center gap-2 shadow-md transition disabled:opacity-60" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin" />
                    <span>Sign Up</span>
                </Button>
                <!-- Séparateur "OR" -->
                <div class="flex items-center my-2">
                    <div class="flex-grow h-px bg-gray-200"></div>
                    <span class="mx-2 text-gray-400 text-xs font-medium">OR</span>
                    <div class="flex-grow h-px bg-gray-200"></div>
                </div>
                <!-- Icônes réseaux sociaux (UI uniquement) -->
                <div class="flex justify-center gap-4">
                    <a v-for="provider in socialProviders" :key="provider.name" :href="provider.href" :title="provider.name" class="rounded-full border border-gray-200 p-2 bg-white shadow-sm hover:bg-gray-100 transition">
                        <!-- Icône Facebook -->
                        <svg v-if="provider.icon === 'facebook'" class="w-6 h-6" :class="provider.color" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.522-4.477-10-10-10S2 6.478 2 12c0 4.991 3.657 9.128 8.438 9.877v-6.987h-2.54v-2.89h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.242 0-1.632.771-1.632 1.562v1.875h2.773l-.443 2.89h-2.33v6.987C18.343 21.128 22 16.991 22 12"/></svg>
                        <!-- Icône Google -->
                        <svg v-else-if="provider.icon === 'google'" class="w-6 h-6" :class="provider.color" fill="currentColor" viewBox="0 0 24 24"><path d="M21.805 10.023h-9.765v3.954h5.627c-.242 1.238-1.484 3.637-5.627 3.637-3.39 0-6.156-2.805-6.156-6.25s2.766-6.25 6.156-6.25c1.93 0 3.227.82 3.97 1.523l2.715-2.64C17.07 2.914 14.97 2 12.04 2 6.477 2 2 6.477 2 12s4.477 10 10.04 10c5.797 0 9.627-4.07 9.627-9.797 0-.66-.07-1.16-.162-1.68z"/></svg>
                        <!-- Icône Twitter -->
                        <svg v-else-if="provider.icon === 'twitter'" class="w-6 h-6" :class="provider.color" fill="currentColor" viewBox="0 0 24 24"><path d="M22.46 5.924c-.793.352-1.644.59-2.538.698a4.48 4.48 0 0 0 1.963-2.475 8.94 8.94 0 0 1-2.828 1.082A4.48 4.48 0 0 0 16.11 4c-2.482 0-4.495 2.013-4.495 4.495 0 .352.04.695.116 1.022C7.728 9.37 4.1 7.555 1.67 4.905c-.386.663-.607 1.434-.607 2.26 0 1.56.795 2.936 2.005 3.744a4.48 4.48 0 0 1-2.037-.563v.057c0 2.18 1.55 4.002 3.604 4.418-.377.103-.775.158-1.186.158-.29 0-.568-.028-.84-.08.57 1.776 2.22 3.07 4.18 3.106A8.99 8.99 0 0 1 2 19.54a12.7 12.7 0 0 0 6.88 2.017c8.253 0 12.77-6.835 12.77-12.77 0-.195-.004-.39-.013-.583A9.14 9.14 0 0 0 24 4.59a8.98 8.98 0 0 1-2.54.697z"/></svg>
                    </a>
                </div>
                <!-- Lien vers la page de connexion -->
                <div class="text-center text-sm text-gray-500 mt-4">
                    Already have an account?
                    <TextLink :href="route('login')" class="text-violet-600 font-semibold hover:underline">Log in</TextLink>
                </div>
            </form>
        </div>
    </div>
</template>
