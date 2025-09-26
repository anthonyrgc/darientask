<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
        <div class="w-full max-w-md bg-white p-6 rounded-xl shadow">
            <h1 class="text-2xl font-bold">Crear cuenta</h1>
            <form class="mt-4 space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm">Nombre</label>
                        <input
                            v-model="form.name"
                            required
                            class="w-full border rounded p-2"
                        />
                    </div>
                    <div>
                        <label class="block text-sm">Apellido</label>
                        <input
                            v-model="form.last_name"
                            required
                            class="w-full border rounded p-2"
                        />
                    </div>
                </div>
                <div>
                    <label class="block text-sm">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full border rounded p-2"
                    />
                </div>
                <div>
                    <label class="block text-sm">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="w-full border rounded p-2"
                    />
                </div>
                <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
                <button
                    :disabled="loading"
                    class="w-full bg-green-600 text-white py-2 rounded"
                >
                    {{ loading ? "Creando..." : "Crear cuenta" }}
                </button>
            </form>
            <p class="text-sm text-center mt-3 text-gray-600">
                ¿Ya tienes cuenta?
                <router-link to="/login" class="text-blue-700 hover:underline"
                    >Inicia sesión</router-link
                >
            </p>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import api from "../api";

const router = useRouter();
const loading = ref(false);
const error = ref(null);
const form = reactive({ name: "", last_name: "", email: "", password: "" });

const submit = async () => {
    error.value = null;
    loading.value = true;
    try {
        const { data } = await api.post("/register", form);
        localStorage.setItem("token", data.token);
        localStorage.setItem("user", JSON.stringify(data.user));
        router.push("/");
    } catch (e) {
        error.value = e?.response?.data?.message || "Error de registro";
    } finally {
        loading.value = false;
    }
};
</script>
