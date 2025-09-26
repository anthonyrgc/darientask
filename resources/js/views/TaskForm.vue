<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Navbar />

        <main class="flex-1 max-w-5xl mx-auto w-full p-6">
            <!-- Header / Hero -->
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-3"
            >
                <div>
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-gray-900"
                    >
                        {{ isEdit ? "Editar tarea" : "Nueva tarea" }}
                    </h1>
                    <p class="text-gray-600 text-sm">
                        Completa la información de la tarea y guarda los
                        cambios.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <router-link
                        to="/tasks"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded border bg-white shadow-sm hover:shadow"
                    >
                        Volver
                    </router-link>
                </div>
            </div>

            <!-- Card del formulario -->
            <div class="mt-6 bg-white rounded-xl shadow">
                <form class="p-6 space-y-6" @submit.prevent="submit">
                    <!-- Error general -->
                    <p v-if="error" class="text-sm text-red-600">
                        {{ error }}
                    </p>

                    <!-- Título -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Título</label
                        >
                        <input
                            v-model="form.title"
                            required
                            placeholder="Ej. Preparar informe mensual"
                            class="mt-1 w-full border rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p
                            v-if="fieldErrors.title"
                            class="text-xs text-red-600 mt-1"
                        >
                            {{ fieldErrors.title }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Es el nombre principal que verás en la lista.
                        </p>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Descripción</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="5"
                            placeholder="Detalles, checklist o notas adicionales…"
                            class="mt-1 w-full border rounded-lg p-2.5 resize-y focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                        <p
                            v-if="fieldErrors.description"
                            class="text-xs text-red-600 mt-1"
                        >
                            {{ fieldErrors.description }}
                        </p>
                    </div>

                    <!-- Due date + Completed -->
                    <div
                        class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center"
                    >
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Fecha y hora límite</label
                            >
                            <input
                                v-model="form.due_date"
                                type="datetime-local"
                                class="mt-1 w-full border rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <p
                                v-if="fieldErrors.due_date"
                                class="text-xs text-red-600 mt-1"
                            >
                                {{ fieldErrors.due_date }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Opcional. Dejar vacío si no aplica.
                            </p>
                        </div>

                        <div class="md:col-span-2 flex items-end">
                            <label
                                class="inline-flex items-center gap-2 select-none"
                            >
                                <input
                                    type="checkbox"
                                    v-model="form.completed"
                                    class="h-4 w-4"
                                />
                                <span class="text-sm text-gray-700"
                                    >Marcar como completada</span
                                >
                            </label>
                        </div>
                    </div>

                    <!-- Footer acciones -->
                    <div class="flex gap-2 pt-2 justify-center">
                        <router-link
                            to="/tasks"
                            class="px-4 py-2 rounded border bg-white shadow-sm hover:shadow"
                        >
                            Cancelar
                        </router-link>
                        <button
                            :disabled="saving"
                            class="px-4 py-2 rounded bg-blue-600 text-white shadow hover:bg-blue-700 disabled:opacity-60"
                        >
                            {{ isEdit ? "Guardar cambios" : "Crear tarea" }}
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import Navbar from "../components/Navbar.vue";
import api from "../api";

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => route.path.endsWith("/edit"));

const form = reactive({
    title: "",
    description: "",
    completed: false,
    due_date: "",
});

const error = ref(null);
const fieldErrors = reactive({});
const saving = ref(false);

function toLocalDatetime(value) {
    if (!value) return "";
    const d = new Date(value);
    if (isNaN(d)) return "";
    const pad = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(
        d.getDate()
    )}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

onMounted(async () => {
    if (isEdit.value) {
        const { data } = await api.get(`/tasks/${route.params.id}`);
        form.title = data.title ?? "";
        form.description = data.description ?? "";
        form.completed = !!data.completed;
        form.due_date = toLocalDatetime(data.due_date);
    }
});

async function submit() {
    error.value = null;
    Object.keys(fieldErrors).forEach((k) => delete fieldErrors[k]);
    saving.value = true;

    const payload = { ...form };
    if (!payload.due_date) payload.due_date = null;

    try {
        if (isEdit.value) {
            await api.put(`/tasks/${route.params.id}`, payload);
        } else {
            await api.post("/tasks", payload);
        }
        router.push("/tasks");
    } catch (e) {
        error.value = e?.response?.data?.message || "Error al guardar";
        const v = e?.response?.data?.errors;
        if (v && typeof v === "object") {
            for (const [k, arr] of Object.entries(v))
                fieldErrors[k] = Array.isArray(arr) ? arr[0] : String(arr);
        }
    } finally {
        saving.value = false;
    }
}
</script>
