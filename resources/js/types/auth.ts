export interface AuthUser {
  id?: number | string;
  name: string;
  email: string;
  role?: string;
  avatarUrl?: string;
}

export interface LoginCredentials {
  sitio_web: string;
  correo: string;
  password: string;
}

export interface VerifyAuth {
  code?: string;
  otp?: string;
  email?: string;
  correo?: string;
}
