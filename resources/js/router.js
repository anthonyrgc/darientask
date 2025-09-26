import { createRouter, createWebHistory } from "vue-router";
import { isAuthenticated } from "./session";
import Login from "./views/Login.vue";
import Register from "./views/Register.vue";
import Dashboard from "./views/Dashboard.vue";
import Tasks from "./views/Tasks.vue";
import TaskForm from "./views/TaskForm.vue";
import TaskShow from "./views/TaskShow.vue";

const routes = [
    { path: "/login", component: Login, meta: { guest: true } },
    { path: "/register", component: Register, meta: { guest: true } },
    { path: "/", component: Dashboard, meta: { auth: true } },
    { path: "/tasks", component: Tasks, meta: { auth: true } },
    { path: "/tasks/new", component: TaskForm, meta: { auth: true } },
    { path: "/tasks/:id", component: TaskShow, meta: { auth: true } },
    {
        path: "/tasks/:id/edit",
        component: TaskForm,
        meta: { auth: true, edit: true },
    },
    { path: "/:pathMatch(.*)*", redirect: "/" },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const auth = isAuthenticated();
    if (to.meta.auth && !auth) return { path: "/login" };
    if (to.meta.guest && auth) return { path: "/" };
    return true;
});

export default router;
