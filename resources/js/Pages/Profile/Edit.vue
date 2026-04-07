<template>
  <div class="mx-auto max-w-xl space-y-8 px-4 py-10">
    <Head title="Perfil" />

    <section class="space-y-4">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">Perfil</h1>
        <p class="text-sm text-slate-600">Actualiza tus datos de acceso.</p>
      </div>

      <form class="space-y-4" @submit.prevent="updateProfile">
        <input v-model="profileForm.name" type="text" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
        <input v-model="profileForm.email" type="email" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-white">Guardar cambios</button>
      </form>
    </section>

    <section class="space-y-4">
      <div>
        <h2 class="text-xl font-semibold text-slate-900">Eliminar cuenta</h2>
        <p class="text-sm text-slate-600">Esta accion cerrara tu acceso de forma permanente.</p>
      </div>

      <form class="space-y-4" @submit.prevent="deleteAccount">
        <input v-model="deleteForm.password" type="password" placeholder="Confirma tu contrasena" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-3 text-white">Eliminar cuenta</button>
      </form>
    </section>
  </div>
</template>

<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps<{
  mustVerifyEmail?: boolean;
  status?: string | null;
  auth: {
    user: {
      name: string;
      email: string;
    };
  };
}>();

const profileForm = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
});

const deleteForm = useForm({
  password: "",
});

const updateProfile = () => {
  profileForm.patch("/profile");
};

const deleteAccount = () => {
  deleteForm.delete("/profile", {
    onFinish: () => deleteForm.reset("password"),
  });
};
</script>
