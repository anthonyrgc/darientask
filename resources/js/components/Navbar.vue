<template>
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b">
      <div class="max-w-6xl mx-auto px-4">
        <div class="h-14 flex items-center justify-between">
          <router-link to="/" class="inline-flex items-center gap-2 group">
            <div class="h-8 w-8 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white grid place-items-center font-bold">
              DT
            </div>
            <span class="font-semibold tracking-tight text-gray-900 group-hover:opacity-90">DarienTask</span>
          </router-link>
  
          <nav class="hidden md:flex items-center gap-1">
            <template v-if="auth">
              <NavLink to="/" :active="isActive('/')">Dashboard</NavLink>
              <NavLink to="/tasks" :active="isActive('/tasks')">Tareas</NavLink>
  
              <div class="h-6 w-px bg-gray-200 mx-2" />
  
              <div class="relative" @keydown.escape="openUser=false">
                <button
                  @click="openUser = !openUser"
                  class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  aria-haspopup="menu"
                  :aria-expanded="openUser ? 'true' : 'false'"
                >
                  <span class="text-sm text-gray-700">Hola, {{ fullName }}</span>
                  <div class="h-7 w-7 rounded-full bg-gray-900 text-white grid place-items-center text-xs font-semibold">
                    {{ initials }}
                  </div>
                </button>
  
                <transition name="fade">
                  <div
                    v-if="openUser"
                    class="absolute right-0 mt-2 w-48 bg-white border rounded-xl shadow-lg overflow-hidden"
                    role="menu"
                  >
                    <div class="px-3 py-2 text-xs text-gray-500">Sesión</div>
                    <button
                      @click="logout"
                      class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50"
                      role="menuitem"
                    >
                      Cerrar sesión
                    </button>
                  </div>
                </transition>
              </div>
            </template>
  
            <template v-else>
              <NavLink to="/login" :active="isActive('/login')">Login</NavLink>
              <NavLink to="/register" :active="isActive('/register')">Registro</NavLink>
            </template>
          </nav>
  
          <button
            class="md:hidden inline-flex items-center justify-center h-9 w-9 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            @click="openMobile = !openMobile"
            :aria-expanded="openMobile ? 'true' : 'false'"
            aria-label="Abrir menú"
          >
            <svg v-if="!openMobile" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
  
      <transition name="slide">
        <div v-if="openMobile" class="md:hidden border-t bg-white">
          <div class="max-w-6xl mx-auto px-4 py-3 space-y-1">
            <template v-if="auth">
              <MobileLink to="/" :active="isActive('/')">Dashboard</MobileLink>
              <MobileLink to="/tasks" :active="isActive('/tasks')">Tareas</MobileLink>
  
              <div class="h-px bg-gray-200 my-2" />
              <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                  <div class="h-8 w-8 rounded-full bg-gray-900 text-white grid place-items-center text-xs font-semibold">
                    {{ initials }}
                  </div>
                  <span class="text-sm text-gray-700">{{ fullName }}</span>
                </div>
                <button @click="logout" class="text-sm text-red-600 hover:underline">Salir</button>
              </div>
            </template>
            <template v-else>
              <MobileLink to="/login" :active="isActive('/login')">Login</MobileLink>
              <MobileLink to="/register" :active="isActive('/register')">Registro</MobileLink>
            </template>
          </div>
        </div>
      </transition>
    </header>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue'
  import { useRouter, useRoute, RouterLink } from 'vue-router'
  import api from '../api'
  import { getUser, clearSession, getToken } from '../session'
  
  const router = useRouter()
  const route = useRoute()
  
  const auth = computed(() => (getToken() ? getUser() : null))
  const fullName = computed(() => {
    const u = auth.value || {}
    return [u.name, u.last_name].filter(Boolean).join(' ')
  })
  const initials = computed(() => {
    const u = auth.value || {}
    const n = `${u.name ?? ''} ${u.last_name ?? ''}`.trim()
    return n ? n.split(/\s+/).map(p => p[0]).join('').slice(0,2).toUpperCase() : 'US'
  })
  
  const isActive = (path) => route.path === path || route.path.startsWith(path + '/')
  
  const openMobile = ref(false)
  const openUser = ref(false)
  
  const logout = async () => {
    try { await api.post('/logout') } catch(_) {}
    clearSession()
    openMobile.value = false
    openUser.value = false
    router.push('/login')
  }
  </script>
  
  <script>
  export default {
    components: {
      NavLink: {
        props: { to: String, active: Boolean },
        components: { RouterLink },
        template: `
          <RouterLink
            :to="to"
            class="text-sm px-3 py-1.5 rounded-lg hover:bg-gray-100"
            :class="active ? 'font-semibold text-gray-900' : 'text-gray-700'"
          >
            <slot />
          </RouterLink>
        `
      },
      MobileLink: {
        props: { to: String, active: Boolean },
        components: { RouterLink },
        template: `
          <RouterLink
            :to="to"
            class="block px-2 py-2 rounded-lg"
            :class="active ? 'font-semibold text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50'"
          >
            <slot />
          </RouterLink>
        `
      }
    }
  }
  </script>
  
  <style>
  .fade-enter-from,.fade-leave-to{opacity:0; transform:scale(.98)}
  .fade-enter-active,.fade-leave-active{transition:all .12s ease}
  .slide-enter-from{max-height:0; opacity:0}
  .slide-enter-active{transition:all .18s ease}
  .slide-leave-to{max-height:0; opacity:0}
  .slide-leave-active{transition:all .18s ease}
  </style>
  