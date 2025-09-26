<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Navbar />

        <main class="flex-1 max-w-5xl mx-auto w-full p-6">
            <div v-if="loading" class="animate-pulse space-y-4">
                <div class="h-8 w-1/3 bg-gray-200 rounded"></div>
                <div class="h-24 bg-gray-200 rounded"></div>
                <div class="h-40 bg-gray-200 rounded"></div>
            </div>

            <template v-else>
                <div
                    class="flex flex-col md:flex-row md:items-start md:justify-between gap-4"
                >
                    <div class="flex items-center gap-3">
                        <h1
                            class="text-3xl font-extrabold tracking-tight text-gray-900"
                        >
                            Detalles de tarea
                        </h1>
                        <span
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                            :class="
                                task?.completed
                                    ? 'bg-emerald-100 text-emerald-800'
                                    : 'bg-amber-100 text-amber-800'
                            "
                        >
                            {{ task?.completed ? "Completada" : "Pendiente" }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="toggleCompleted"
                            class="px-4 py-2 rounded border shadow-sm hover:shadow transition bg-white"
                            :class="
                                task?.completed
                                    ? 'border-amber-300 text-amber-700'
                                    : 'border-emerald-300 text-emerald-700'
                            "
                        >
                            {{
                                task?.completed
                                    ? "Marcar como pendiente"
                                    : "Marcar como completada"
                            }}
                        </button>

                        <router-link
                            :to="`/tasks/${id}/edit`"
                            class="px-4 py-2 rounded border border-gray-300 shadow-sm hover:shadow transition bg-white"
                        >
                            Editar
                        </router-link>

                        <router-link
                            to="/tasks"
                            class="px-4 py-2 rounded border border-gray-300 shadow-sm hover:shadow transition bg-white"
                        >
                            Volver
                        </router-link>
                    </div>
                </div>

                <section class="mt-6">
                    <div class="bg-white rounded-xl shadow p-5">
                        <h2
                            class="font-semibold text-gray-900 mb-3 text-center"
                        >
                            Información
                        </h2>
                        <dl class="grid grid-cols-1 gap-y-4 text-sm">
                            <div>
                                <dt class="text-gray-500">
                                    Nombre de la tarea
                                </dt>
                                <dd class="text-gray-900 font-medium mt-1">
                                    {{ task?.title || "—" }}
                                </dd>
                            </div>
                            <div v-if="task?.description">
                                <dt class="text-gray-500">Descripción</dt>
                                <dd
                                    class="whitespace-pre-line text-gray-900 mt-1"
                                >
                                    {{ task.description }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="mt-6 grid gap-4 md:grid-cols-3">
                    <div class="bg-white rounded-xl shadow p-5">
                        <div class="text-sm text-gray-500">Creada</div>
                        <div class="mt-1 text-lg font-semibold">
                            {{ fmtDate(task?.created_at) }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ relative(task?.created_at) }}
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow p-5">
                        <div class="text-sm text-gray-500">Vence</div>
                        <div class="mt-1 text-lg font-semibold">
                            {{ fmtDate(task?.due_date) }}
                        </div>
                        <div
                            class="text-xs text-gray-500"
                            v-if="task?.due_date"
                        >
                            {{ relative(task?.due_date) }}
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow p-5">
                        <div class="text-sm text-gray-500">Actualizada</div>
                        <div class="mt-1 text-lg font-semibold">
                            {{ fmtDate(task?.updated_at) }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ relative(task?.updated_at) }}
                        </div>
                    </div>
                </section>
            </template>
        </main>
    </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import Navbar from "../components/Navbar.vue";
import api from "../api";

const route = useRoute();
const id = route.params.id;
const task = ref(null);
const loading = ref(true);

function fmtDate(d) {
    if (!d) return "—";
    const dt = new Date(d);
    return isNaN(dt) ? "—" : dt.toLocaleString();
}

function relative(d) {
    if (!d) return "";
    const now = new Date();
    const dt = new Date(d);
    if (isNaN(dt)) return "";
    const diff = dt - now;
    const abs = Math.abs(diff);
    const days = Math.floor(abs / (1000 * 60 * 60 * 24));
    const hours = Math.floor((abs / (1000 * 60 * 60)) % 24);
    const mins = Math.floor((abs / (1000 * 60)) % 60);
    const label = `${days}d ${hours}h ${mins}m`;
    return diff >= 0 ? `en ${label}` : `hace ${label}`;
}

async function toggleCompleted() {
    if (!task.value) return;
    const next = !task.value.completed;
    const { data } = await api.put(`/tasks/${id}`, { completed: next });
    task.value = data;
}

onMounted(async () => {
    try {
        const { data } = await api.get(`/tasks/${id}`);
        task.value = data;
    } finally {
        loading.value = false;
    }
});
</script>
