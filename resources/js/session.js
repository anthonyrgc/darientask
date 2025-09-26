export function getUser() {
    try {
        return JSON.parse(localStorage.getItem("user") || "null");
    } catch {
        return null;
    }
}
export function getToken() {
    return localStorage.getItem("token");
}
export function isAuthenticated() {
    return !!getToken();
}
export function setSession({ token, user }) {
    localStorage.setItem("token", token);
    localStorage.setItem("user", JSON.stringify(user));
}
export function clearSession() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
}
