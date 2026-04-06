import { reactive } from "vue";
import type { AuthUser, LoginCredentials, VerifyAuth } from "@/types/auth";

type AuthStore = {
  user: AuthUser | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
  message: string | null;
  success: boolean | null;
  hydrate: () => Promise<void>;
  login: (credentials: LoginCredentials) => Promise<boolean>;
  verify2Auth: (credentials: VerifyAuth) => Promise<boolean>;
  logout: () => Promise<void>;
};

const STORAGE_KEY = "seguimiento-sd-auth";

const store = reactive<AuthStore>({
  user: null,
  isAuthenticated: false,
  isLoading: false,
  error: null,
  message: null,
  success: null,
  hydrate,
  login,
  verify2Auth,
  logout,
});

function persist() {
  if (typeof window === "undefined") return;

  window.localStorage.setItem(
    STORAGE_KEY,
    JSON.stringify({
      user: store.user,
      isAuthenticated: store.isAuthenticated,
    }),
  );
}

function clearFeedback() {
  store.error = null;
  store.message = null;
  store.success = null;
}

function getRoleFromEmail(email: string) {
  const value = email.toLowerCase();

  if (value.includes("director")) return "director";
  if (value.includes("revisor")) return "revisor";
  return "docente";
}

async function hydrate() {
  if (typeof window === "undefined") return;

  const raw = window.localStorage.getItem(STORAGE_KEY);
  if (!raw) return;

  try {
    const parsed = JSON.parse(raw) as {
      user?: AuthUser | null;
      isAuthenticated?: boolean;
    };

    store.user = parsed.user ?? null;
    store.isAuthenticated = Boolean(parsed.isAuthenticated && parsed.user);
  } catch {
    window.localStorage.removeItem(STORAGE_KEY);
  }
}

async function login(credentials: LoginCredentials) {
  store.isLoading = true;
  clearFeedback();

  try {
    const email = credentials.correo.trim();
    const role = getRoleFromEmail(email);

    store.user = {
      id: email,
      name: email.split("@")[0] || "Usuario",
      email,
      role,
    };
    store.message = "Verifica el codigo para completar el acceso.";
    store.success = true;
    persist();
    return true;
  } catch {
    store.error = "No fue posible iniciar sesion.";
    store.success = false;
    return false;
  } finally {
    store.isLoading = false;
  }
}

async function verify2Auth(credentials: VerifyAuth) {
  store.isLoading = true;
  clearFeedback();

  try {
    if (!credentials.code && !credentials.otp) {
      store.error = "Ingresa el codigo de verificacion.";
      store.success = false;
      return false;
    }

    store.isAuthenticated = true;
    store.message = "Inicio de sesion completado.";
    store.success = true;
    persist();
    return true;
  } finally {
    store.isLoading = false;
  }
}

async function logout() {
  store.user = null;
  store.isAuthenticated = false;
  clearFeedback();

  if (typeof window !== "undefined") {
    window.localStorage.removeItem(STORAGE_KEY);
  }
}

export function useAuthStore() {
  return store;
}
