export type EventItem = {
  id: number;
  titulo?: string;
  nombre?: string;
  descripcion?: string;
  fecha?: string;
  fecha_inicio?: string;
  fecha_fin?: string;
  hora?: string;
  ubicacion?: string;
  lugar?: string;
  categoria?: string;
  categoria_id?: number;
  capacidad?: number;
  cupo?: number;
  precio?: number;
  estado?: string;
  imagen?: string;
  [key: string]: unknown;
};

export type InscripcionData = {
  inscripcion_id: number;
  codigo_pase: string;
  estado: string;
  evento: {
    id: number;
    titulo: string;
    fecha_evento?: string;
    hora_evento?: string;
    lugar?: string;
  };
};


const BACKEND_URL = ((import.meta as any).env?.VITE_BACKEND_URL as string) ?? "/api";

const SESSION_FLAG = "eventia_authenticated";
const USER_KEY = "eventia_user";

export function hasSession(): boolean {
  return localStorage.getItem(SESSION_FLAG) === "1";
}
function markSession(user: AuthUser) {
  localStorage.setItem(SESSION_FLAG, "1");
  localStorage.setItem(USER_KEY, JSON.stringify(user));
}

function clearSession() {
  localStorage.removeItem(SESSION_FLAG);
  localStorage.removeItem(USER_KEY);
}

export function getStoredUser(): AuthUser | null {
  const stored = localStorage.getItem(USER_KEY);

  if (!stored) {
    return null;
  }

  try {
    return JSON.parse(stored) as AuthUser;
  } catch {
    clearSession();
    return null;
  }
}

async function request<T>(url: string, options: RequestInit = {}): Promise<T> {
  const headers = new Headers(options.headers);

  if (options.body && !headers.has("Content-Type")) {
    headers.set("Content-Type", "application/json");
  }

  const response = await fetch(url, {
    ...options,
    headers,
    credentials: "include"
  });

  
  const text = await response.text();

  let data: any = null;
  try {
    data = text ? JSON.parse(text) : null;
  } catch {
    data = text;
  }

  if (!response.ok) {
  console.error("API ERROR STATUS:", response.status);
  console.error("API ERROR RESPONSE:", data);

  throw new Error(
    data?.message ??
    data?.error ??
    JSON.stringify(data) ??
    `Error HTTP ${response.status}`
  );
}

  if (response.status === 401 || response.status === 403) {
    clearSession();
  }

  if (!response.ok) {
    throw new Error(data?.message ?? data?.error ?? `Error HTTP ${response.status}`);
  }

  return data as T;
}

function unwrapEvents(data: any): EventItem[] {
  if (Array.isArray(data)) return data;
  return data?.data ?? data?.eventos ?? data?.results ?? data?.items ?? [];
}

function unwrapEvent(data: any): EventItem {
  return data?.data ?? data?.evento ?? data;
}

export async function getEvents(search = "", categoriaId?: number) {
  const params = new URLSearchParams();
  if (search) params.append("busqueda", search);
  if (categoriaId) params.append("categoria_id", categoriaId.toString());

  const query = params.toString() ? `?${params.toString()}` : "";
  const data = await request<any>(`${BACKEND_URL}/eventos/catalogo.php${query}`);
  
  return {
    eventos: unwrapEvents(data.data.eventos),
    categorias: (data.data.categorias ?? []) as { id: number; nombre: string }[]
  };
}

export async function getEvent(id: number) {
  const data = await request<any>(`${BACKEND_URL}/eventos/detalle.php?id=${id}`);
  return unwrapEvent(data);
}

export async function getInscripcionesFromUser(userId: number) {
  const data = await request<any>(
    `${BACKEND_URL}/inscripciones/get_inscripciones.php?estudiante_id=${userId}`,
    {
      method: "GET",
      credentials: "include"
    }
  );

  return data;
}

export async function createInscripcion(eventoId: number): Promise<InscripcionData> {
  const data = await request<any>(`${BACKEND_URL}/inscripciones/crear.php`, {
    method: "POST",
    body: JSON.stringify({ evento_id: eventoId }),
    credentials: "include"
  });
  return (data?.data ?? data) as InscripcionData;
}

export async function deleteEvent(id: number): Promise<void> {
  await request(`${BACKEND_URL}/eventos/eliminar.php?id=${id}`, {
    method: "DELETE",
  });
}

export type CreateEventPayload = {
  titulo: string;
  descripcion: string;
  fecha_evento: string;
  hora_evento: string;
  lugar: string;
  categoria_id?: number;
  aforo_maximo?: number;
};


export async function createEvent(payload: CreateEventPayload) {
  const data = await request<any>(`${BACKEND_URL}/eventos/crear.php`, {
    method: "POST",
    body: JSON.stringify(payload)
  });
  return unwrapEvent(data);
}

export async function updateEvent(
  id: number,
  payload: CreateEventPayload
): Promise<EventItem> {
  const data = await request<any>(
    `${BACKEND_URL}/eventos/editar.php?id=${id}`,
    {
      method: "PUT",
      body: JSON.stringify(payload)
    }
  );

  return data.data ?? data;
}

export type LoginPayload = {
  correo: string;
  contrasena: string;
};
export type RegisterPayload = {
  nombre: string;
  correo: string;
  contrasena: string;
  confirmar_contrasena: string;
  cargo: "estudiante" | "administrativo" | "profesor" | "";
};

export type AuthUser = {
  id: number;
  nombre?: string;
  correo?: string;
  [key: string]: unknown;
};

function unwrapUser(data: any): AuthUser {
  const payload = data?.data ?? data;

  return payload?.usuario && typeof payload.usuario === "object"
    ? payload.usuario
    : payload?.user && typeof payload.user === "object"
      ? payload.user
      : payload;
}

export async function userLogIn(payload: LoginPayload): Promise<AuthUser> {
  const data = await request<any>(`${BACKEND_URL}/auth-user/login.php`, {
    method: "POST",
    body: JSON.stringify(payload)
  });

  const user = unwrapUser(data);

  markSession(user);

  return user;
}
export async function userRegister(payload: RegisterPayload): Promise<AuthUser> {
  const data = await request<any>(`${BACKEND_URL}/auth-user/register.php`, {
    method: "POST",
    body: JSON.stringify(payload)
  });

  const user = unwrapUser(data);

  markSession(user);

  return user;
}

export async function userLogOut(): Promise<void> {
  try {
    await request<any>(`${BACKEND_URL}/auth-user/logout.php`, {
      method: "POST"
    });
  } finally {
    clearSession();
  }
}



export type CreateResenaPayload = {
  evento_id: number;
  calificacion: number;
  comentario: string;
};

export type ReporteData = {
  total_inscritos: number;
  asistencia_final: number;
  porcentaje_asistencia: string;
  valoracion_promedio: string;
};

export async function createResena(payload: CreateResenaPayload) {
  const data = await request<any>(`${BACKEND_URL}/resenas/crear.php`, {
    method: "POST",
    body: JSON.stringify(payload)
  });
  return data;
}

export async function getReporte(eventoId: number): Promise<ReporteData> {
  const data = await request<any>(`${BACKEND_URL}/eventos/reporte.php?evento_id=${eventoId}`);
  return data.data ?? data;
}