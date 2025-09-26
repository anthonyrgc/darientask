<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Navbar />

        <main class="flex-1 max-w-6xl mx-auto w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <router-link
                    to="/tasks"
                    class="px-4 py-2 bg-blue-600 text-white rounded"
                >
                    Ir a Tareas
                </router-link>
            </div>

            <!-- Tarjetas de conteo -->
            <div class="grid gap-4 grid-cols-1 sm:grid-cols-3">
                <div class="bg-white rounded-xl shadow p-5">
                    <div class="text-sm text-gray-500">Total de tareas</div>
                    <div class="mt-1 text-3xl font-extrabold">
                        {{ counts.total }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-5">
                    <div class="text-sm text-gray-500">Completadas</div>
                    <div class="mt-1 text-3xl font-extrabold text-emerald-700">
                        {{ counts.completed }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-5">
                    <div class="text-sm text-gray-500">Pendientes</div>
                    <div class="mt-1 text-3xl font-extrabold text-amber-700">
                        {{ counts.pending }}
                    </div>
                </div>
            </div>

            <!-- Bloques demo (opcional) -->
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="p-4 bg-white rounded-xl shadow">
                    <h2 class="font-semibold mb-1">
                        Aplicación Web de gestión de tareas individual
                    </h2>
                    <p class="text-sm text-gray-600">
                        DarienTask es una aplicación web para gestionar tus
                        tareas de forma sencilla. Permite crear, editar, ver y
                        eliminar tareas, organizarlas por estado y fecha de
                        vencimiento, y mantener el control de tus pendientes
                        desde cualquier lugar.
                    </p>
                </div>
                <div class="p-4 bg-white rounded-xl shadow">
                    <h2 class="font-semibold mb-1">Creado por:</h2>
                    <ul class="list-disc list-inside text-sm text-gray-700">
                        <li>Anthony Giannone</li>
                        <li>Para: Darien Technology</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { onMounted, reactive } from "vue";
import Navbar from "../components/Navbar.vue";
import api from "../api";

const counts = reactive({ total: 0, completed: 0, pending: 0 });

function getTotal(res) {
    if (res?.data?.meta?.total != null) return res.data.meta.total;
    if (res?.data?.total != null) return res.data.total;
    if (Array.isArray(res?.data)) return res.data.length;
    return 0;
}

async function fetchCounts() {
    const all = await api.get("/tasks", { params: { per_page: 1 } });
    const done = await api.get("/tasks", {
        params: { per_page: 1, completed: true },
    });
    const pending = await api.get("/tasks", {
        params: { per_page: 1, completed: false },
    });

    counts.total = getTotal(all);
    counts.completed = getTotal(done);
    counts.pending = getTotal(pending);
}

onMounted(fetchCounts);
</script>
