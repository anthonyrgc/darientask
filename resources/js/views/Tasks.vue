<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Navbar />

        <main class="flex-1 max-w-6xl mx-auto w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold">Mis tareas</h1>
                <div class="flex items-center gap-2">
                    <div class="flex flex-wrap gap-2">
                        <router-link
                            to="/dashboard"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded border bg-white shadow-sm hover:shadow"
                        >
                            Volver
                        </router-link>
                    </div>
                    <router-link
                        to="/tasks/new"
                        class="px-4 py-2 bg-blue-600 text-white rounded"
                    >
                        Nueva tarea
                    </router-link>
                </div>
            </div>

            <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <input
                    v-model="filters.title"
                    @input="debouncedFetch()"
                    placeholder="Buscar por título..."
                    class="border rounded p-2 w-full"
                />
                <select
                    v-model="filters.completed"
                    @change="applyFilters"
                    class="border rounded p-2 w-full"
                >
                    <option value="">Estado: todos</option>
                    <option value="true">Completadas</option>
                    <option value="false">Pendientes</option>
                </select>
                <select
                    v-model="filters.sort"
                    @change="applyFilters"
                    class="border rounded p-2 w-full"
                >
                    <option value="created_at">Ordenar por creado</option>
                    <option value="due_date">Ordenar por vencimiento</option>
                </select>
                <select
                    v-model="filters.dir"
                    @change="applyFilters"
                    class="border rounded p-2 w-full"
                >
                    <option value="desc">Descendente</option>
                    <option value="asc">Ascendente</option>
                </select>
                <input
                    v-model="filters.due_from"
                    type="datetime-local"
                    @change="applyFilters"
                    class="border rounded p-2 w-full"
                    placeholder="Desde (vencimiento)"
                />
                <input
                    v-model="filters.due_to"
                    type="datetime-local"
                    @change="applyFilters"
                    class="border rounded p-2 w-full"
                    placeholder="Hasta (vencimiento)"
                />
            </div>

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y">
                    <thead class="bg-gray-50 text-left text-sm">
                        <tr>
                            <th class="px-4 py-2">Título</th>
                            <th class="px-4 py-2">Vence</th>
                            <th class="px-4 py-2">Estado</th>
                            <th class="px-4 py-2 w-60">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr v-for="t in tasks" :key="t.id">
                            <td class="px-4 py-2">
                                <router-link :to="`/tasks/${t.id}`">
                                    <span class="font-medium">
                                        {{ t.title }}</span
                                    >
                                </router-link>

                                <div
                                    v-if="t.description"
                                    class="text-gray-500 line-clamp-2"
                                >
                                    {{ t.description }}
                                </div>
                            </td>
                            <td class="px-4 py-2">{{ fmtDate(t.due_date) }}</td>
                            <td class="px-4 py-2">
                                <span
                                    :class="
                                        t.completed
                                            ? 'text-emerald-700'
                                            : 'text-amber-700'
                                    "
                                >
                                    {{
                                        t.completed ? "Completada" : "Pendiente"
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <!-- NUEVO: Ver -->
                                    <router-link
                                        :to="`/tasks/${t.id}`"
                                        class="px-3 py-1 border rounded"
                                    >
                                        Ver
                                    </router-link>

                                    <router-link
                                        :to="`/tasks/${t.id}/edit`"
                                        class="px-3 py-1 border rounded"
                                    >
                                        Editar
                                    </router-link>

                                    <button
                                        class="px-3 py-1 border rounded text-red-600"
                                        @click="askDelete(t)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!loading && tasks.length === 0">
                            <td
                                colspan="4"
                                class="px-4 py-6 text-center text-gray-500"
                            >
                                Sin resultados
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- paginación -->
            <div class="flex items-center justify-between mt-4">
                <div class="text-sm text-gray-600">
                    Página {{ page }} de {{ meta.last_page || 1 }} —
                    {{ meta.total || tasks.length }} total
                </div>
                <div class="flex gap-2">
                    <button
                        :disabled="page <= 1"
                        class="px-3 py-1 border rounded"
                        @click="prev"
                    >
                        Anterior
                    </button>
                    <button
                        :disabled="page >= meta.last_page"
                        class="px-3 py-1 border rounded"
                        @click="next"
                    >
                        Siguiente
                    </button>
                </div>
            </div>

            <div
                v-if="confirmOpen"
                class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            >
                <div class="bg-white rounded-xl shadow p-6 w-full max-w-sm">
                    <h3 class="font-semibold text-lg mb-2">Eliminar tarea</h3>
                    <p class="text-sm text-gray-700 mb-4">
                        ¿Eliminar "<strong>{{ selected?.title }}</strong
                        >"? Esta acción no se puede deshacer.
                    </p>

                    <p v-if="deleteError" class="text-sm text-red-600 mb-3">
                        {{ deleteError }}
                    </p>

                    <div class="flex justify-end gap-3">
                        <button
                            class="px-3 py-2 rounded border"
                            @click="closeConfirm"
                        >
                            Cancelar
                        </button>
                        <button
                            class="px-3 py-2 rounded bg-red-600 text-white disabled:opacity-60"
                            :disabled="deleting"
                            @click="confirmDelete"
                        >
                            {{ deleting ? "Eliminando..." : "Eliminar" }}
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import Navbar from "../components/Navbar.vue";
import api from "../api";

const tasks = ref([]);
const loading = ref(false);
const page = ref(1);
const meta = reactive({ total: 0, last_page: 1 });

const filters = reactive({
    title: "",
    completed: "",
    sort: "created_at",
    dir: "desc",
    due_from: "",
    due_to: "",
});

const perPage = 10;

function fmtDate(d) {
    if (!d) return "—";
    const dt = new Date(d);
    return isNaN(dt) ? "—" : dt.toLocaleString();
}

function buildParams() {
    const params = {
        page: page.value,
        per_page: perPage,
        sort: filters.sort,
        dir: filters.dir,
    };
    if (filters.title) params.title = filters.title;
    if (filters.completed !== "") params.completed = filters.completed;
    if (filters.due_from) params.due_from = filters.due_from;
    if (filters.due_to) params.due_to = filters.due_to;
    return params;
}

async function fetchTasks() {
    loading.value = true;
    try {
        const { data } = await api.get("/tasks", { params: buildParams() });
        tasks.value = Array.isArray(data) ? data : data.data ?? [];
        meta.total = data?.total ?? data?.meta?.total ?? tasks.value.length;
        meta.last_page = data?.last_page ?? data?.meta?.last_page ?? 1;
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    page.value = 1;
    fetchTasks();
}

function next() {
    if (page.value < meta.last_page) {
        page.value++;
        fetchTasks();
    }
}
function prev() {
    if (page.value > 1) {
        page.value--;
        fetchTasks();
    }
}

let debounceTimer = null;
function debouncedFetch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 300);
}

const confirmOpen = ref(false);
const selected = ref(null);
const deleting = ref(false);
const deleteError = ref(null);

function askDelete(t) {
    selected.value = t;
    deleteError.value = null;
    confirmOpen.value = true;
}

function closeConfirm() {
    confirmOpen.value = false;
    selected.value = null;
    deleting.value = false;
    deleteError.value = null;
}

async function confirmDelete() {
    if (!selected.value?.id) return;
    deleting.value = true;
    deleteError.value = null;
    try {
        await api.delete(`/tasks/${selected.value.id}`);
        // si era la última de la página, retrocede una página
        if (tasks.value.length === 1 && page.value > 1) page.value--;
        closeConfirm();
        await fetchTasks();
    } catch (e) {
        deleteError.value =
            e?.response?.data?.message || "No se pudo eliminar la tarea";
        deleting.value = false;
    }
}

onMounted(fetchTasks);
</script>
