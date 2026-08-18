import { useEffect, useMemo, useState } from "react";
import {
  CalendarDays,
  ChevronRight,
  CirclePlus,
  Clock3,
  MapPin,
  Search,
  Ticket,
  Users,
  X,
  LayoutDashboard,
  LogOut,
  Star,
  BarChart3
} from "lucide-react";

import {
  createEvent,
  updateEvent,
  getEvent,
  getEvents,
  userLogOut,
  hasSession,
  deleteEvent,
  getStoredUser,
  createResena,
  getReporte,
  createInscripcion,
  InscripcionData,
  type CreateEventPayload,
  type EventItem,
  type AuthUser,
  type ReporteData
} from "./api";

import Login from "./Login";
import Register from "./Register";

type View = "dashboard" | "create" | "edit";
type Screen = "login" | "register" | "dashboard";


const titleOf = (e: EventItem) =>
  String(e.titulo ?? e.nombre ?? "Evento sin título");

const dateOf = (e: EventItem) =>
  String(e.fecha_evento ?? e.fecha ?? "");

const placeOf = (e: EventItem) =>
  String(e.lugar ?? e.ubicacion ?? "Por definir");

function formatDate(value: string) {
  if (!value) return "Fecha por definir";

  const d = new Date(value);

  if (Number.isNaN(d.getTime())) return value;

  return new Intl.DateTimeFormat("es-EC", {
    day: "2-digit",
    month: "short",
    year: "numeric"
  }).format(d);
}

function App() {
  const [view, setView] = useState<View>("dashboard");

  const [events, setEvents] = useState<EventItem[]>([]);
  const [categories, setCategories] = useState<{ id: number; nombre: string }[]>([]);

  const [selected, setSelected] = useState<EventItem | null>(null);
  const [editingEvent, setEditingEvent] = useState<EventItem | null>(null);

  const [search, setSearch] = useState("");

  const [selectedCategory, setSelectedCategory] = useState<number | undefined>();

  const [loading, setLoading] = useState(true);
  const [detailLoading, setDetailLoading] = useState(false);

  const [error, setError] = useState("");

  const [screen, setScreen] = useState<Screen>(() =>
    hasSession() ? "dashboard" : "login"
  );

  const [user, setUser] = useState<AuthUser | null>(() =>
    getStoredUser()
  );
  async function loadEvents() {
    setLoading(true);
    setError("");

    try {
      const { eventos, categorias } = await getEvents(search, selectedCategory);
      setEvents(eventos);
      if (categorias.length > 0) setCategories(categorias);
    } catch (err) {
      setError(
        err instanceof Error
          ? err.message
          : "No se pudieron cargar los eventos."
      );
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (screen === "dashboard") {
      void loadEvents();
    }
  }, [screen]);

  async function openDetail(id: number) {
    setSelected(null);
    setDetailLoading(true);

    try {
      const event = await getEvent(id);
      setSelected(event);
      console.log("EVENTO:", event);
      console.log("ORGANIZADOR ID:", event.organizador_id);
      console.log("USUARIO:", user);
      console.log("USER ID:", user?.id);
    } catch (err) {
      setError(
        err instanceof Error
          ? err.message
          : "No se pudo cargar el detalle."
      );
    } finally {
      setDetailLoading(false);
    }
  }

  function startEditing(event: EventItem) {
    setSelected(null);
    setEditingEvent(event);
    setView("edit");
  }

  function cancelEditing() {
    setEditingEvent(null);
    setView("dashboard");
  }
  async function handleDelete(event: EventItem) {
    const confirmed = window.confirm(
      `¿Estás seguro de que deseas eliminar "${titleOf(event)}"?`
    );

    if (!confirmed) return;

    setError("");

    try {
      await deleteEvent(event.id);

      setSelected(null);

      setEvents(current =>
        current.filter(e => e.id !== event.id)
      );

    } catch (err) {
      setError(
        err instanceof Error
          ? err.message
          : "No se pudo eliminar el evento."
      );
    }
  }

  const stats = useMemo(() => {
    const upcoming = events.filter(e => {
      const d = new Date(dateOf(e));

      return (
        !Number.isNaN(d.getTime()) &&
        d >= new Date()
      );
    }).length;

    const capacity = events.reduce(
      (sum, e) =>
        sum +
        Number(
          e.aforo_maximo ??
          e.cupos_disponibles ??
          0
        ),
      0
    );

    return {
      total: events.length,
      upcoming,
      capacity
    };
  }, [events]);

  if (screen === "login") {
    return (
      <Login
        onLogin={u => {
          console.log("LOGIN RECIBIDO EN APP:", u);
          console.log("TIPO:", typeof u);

          setUser(u);
          setScreen("dashboard");
        }}
        onSwitchToRegister={() =>
          setScreen("register")
        }
      />
    );
  }

  if (screen === "register") {
    return (
      <Register
        onRegistered={u => {
          setUser(u);
          setScreen("dashboard");
        }}
        onSwitchToLogin={() =>
          setScreen("login")
        }
      />
    );
  }

  return (
    <div className="app-shell">

      <aside className="sidebar">

        <div className="brand">
          <div className="brand-mark">
            <CalendarDays size={21} />
          </div>

          <div>
            <strong>Eventia</strong>
            <span>Organizador</span>
          </div>
        </div>

        <nav>

          <button
            className={
              view === "dashboard"
                ? "nav-item active"
                : "nav-item"
            }
            onClick={() => {
              setEditingEvent(null);
              setView("dashboard");
            }}
          >
            <LayoutDashboard size={18} />
            Dashboard
          </button>

          <button
            className={
              view === "create"
                ? "nav-item active"
                : "nav-item"
            }
            onClick={() => {
              setEditingEvent(null);
              setView("create");
            }}
          >
            <CirclePlus size={18} />
            Crear evento
          </button>

        </nav>

        <div className="sidebar-bottom">

          <button
            className="nav-item muted"
            onClick={async () => {
              try {
                await userLogOut();
              } finally {
                setUser(null);
                setScreen("login");
              }
            }}
          >
            <LogOut size={18} />
            Cerrar sesión
          </button>

        </div>

      </aside>

      <main className="main">

        <header className="topbar">

          <div>
            <p className="eyebrow">
              PANEL ORGANIZADOR
            </p>

            <h1>
              {view === "dashboard"
                ? "Mis eventos"
                : view === "create"
                  ? "Crear evento"
                  : "Editar evento"}
            </h1>
          </div>

          {view === "dashboard" && (
            <button
              className="primary-button"
              onClick={() => {
                setEditingEvent(null);
                setView("create");
              }}
            >
              <CirclePlus size={18} />
              Nuevo evento
            </button>
          )}

        </header>

        {view === "dashboard" ? (

          <>
            <section className="stats">

              <Stat
                icon={<Ticket size={20} />}
                label="Eventos"
                value={stats.total}
              />

              <Stat
                icon={<CalendarDays size={20} />}
                label="Próximos"
                value={stats.upcoming}
              />

              <Stat
                icon={<Users size={20} />}
                label="Capacidad total"
                value={stats.capacity || "—"}
              />

            </section>

            <section className="toolbar">

              <div className="search-box">
                <Search size={18} />

                <input
                  value={search}
                  placeholder="Buscar eventos..."
                  onChange={e =>
                    setSearch(e.target.value)
                  }
                  onKeyDown={e => {
                    if (e.key === "Enter") {
                      void loadEvents();
                    }
                  }}
                />
              </div>

              <select
                className="secondary-button"
                style={{ height: "42px", padding: "0 12px", cursor: "pointer" }}
                value={selectedCategory ?? ""}
                onChange={e => {
                  const val = e.target.value ? Number(e.target.value) : undefined;
                  setSelectedCategory(val);
                }}
              >
                <option value="">Todas las categorías</option>
                {categories.map(c => (
                  <option key={c.id} value={c.id}>{c.nombre}</option>
                ))}
              </select>

              <button
                className="secondary-button"
                onClick={() => void loadEvents()}
              >
                Filtrar
              </button>

            </section>

            {error && (
              <div className="alert">
                {error}
              </div>
            )}

            {loading ? (

              <div className="empty">
                Cargando eventos...
              </div>

            ) : events.length === 0 ? (

              <div className="empty">

                <CalendarDays size={36} />

                <h3>No hay eventos</h3>

                <p>
                  Crea tu primer evento para verlo aquí.
                </p>

                <button
                  className="primary-button"
                  onClick={() => setView("create")}
                >
                  <CirclePlus size={18} />
                  Crear evento
                </button>

              </div>

            ) : (

              <section className="event-grid">

                {events.map(event => (
                  <EventCard
                    key={event.id}
                    event={event}
                    onClick={() =>
                      void openDetail(event.id)
                    }
                  />
                ))}

              </section>

            )}

          </>

        ) : view === "create" ? (

          <CreateForm
            onCancel={() =>
              setView("dashboard")
            }

            onCreated={async event => {
              setEvents(current => [
                event,
                ...current
              ]);

              setView("dashboard");

              await openDetail(event.id);
            }}
          />

        ) : (

          <CreateForm
            event={editingEvent}
            onCancel={cancelEditing}
            onUpdated={async updatedEvent => {

              setEvents(current =>
                current.map(event =>
                  event.id === updatedEvent.id
                    ? updatedEvent
                    : event
                )
              );

              setEditingEvent(null);
              setView("dashboard");

              await openDetail(updatedEvent.id);
            }}
          />

        )}

      </main>

      {(selected || detailLoading) && (

        <div
          className="modal-backdrop"
          onClick={() => setSelected(null)}
        >

          <div
            className="detail-panel"
            onClick={e =>
              e.stopPropagation()
            }
          >

            <button
              className="close-button"
              onClick={() =>
                setSelected(null)
              }
            >
              <X size={20} />
            </button>

            {detailLoading ? (

              <div className="empty">
                Cargando detalle.c..
              </div>

            ) : selected ? (

              <EventDetail
                event={selected}
                user={user}
                onEdit={() => startEditing(selected)}
                onDelete={() => void handleDelete(selected)}
              />

            ) : null}

          </div>

        </div>

      )}

    </div>
  );
}

function Stat({
  icon,
  label,
  value
}: {
  icon: React.ReactNode;
  label: string;
  value: string | number;
}) {
  return (
    <div className="stat-card">

      <div className="stat-icon">
        {icon}
      </div>

      <div>
        <span>{label}</span>
        <strong>{value}</strong>
      </div>

    </div>
  );
}

function EventCard({
  event,
  onClick
}: {
  event: EventItem;
  onClick: () => void;
}) {
  const capacity =
    event.aforo_maximo ??
    event.cupos_disponibles;

  return (
    <article
      className="event-card"
      onClick={onClick}
    >

      <div className="event-cover">

        {event.imagen ? (
          <img
            src={event.imagen}
            alt=""
          />
        ) : (
          <CalendarDays size={32} />
        )}

        {event.estado && (
          <span className="status">
            {String(event.estado)}
          </span>
        )}

      </div>

      <div className="event-content">

        <p className="category">
          {String(
            event.categoria ?? "EVENTO"
          )}
        </p>

        <h3>
          {titleOf(event)}
        </h3>

        <div className="event-meta">
          <CalendarDays size={15} />
          {formatDate(dateOf(event))}
        </div>

        <div className="event-meta">
          <MapPin size={15} />
          {placeOf(event)}
        </div>

        <div className="card-footer">

          <span>
            {capacity
              ? `${capacity} cupos`
              : "Ver detalles"}
          </span>

          <ChevronRight size={18} />

        </div>

      </div>

    </article>
  );
}
function EventDetail({
  event,
  user,
  onEdit,
  onDelete
}: {
  event: EventItem;
  user: AuthUser | null;
  onEdit: () => void;
  onDelete: () => void;
}) {
  const [showReporte, setShowReporte] = useState(false);
  const [showResena, setShowResena] = useState(false);

  const [inscripcion, setInscripcion] = useState<InscripcionData | null>(null);
  const [enrolling, setEnrolling] = useState(false);
  const [enrollError, setEnrollError] = useState("");

  // Guardamos en variables si el usuario es organizador de este evento o si es estudiante
  const isOrganizador = Number(event.organizador_id) === Number(user?.id);
  const isEstudiante = !isOrganizador && Boolean(user?.id); // Si está logueado pero no es el organizador
  const eventStatus = String(
    (event as EventItem & { estado?: string }).estado ?? "activo"
  ).toLowerCase();
  const noSpotsAvailable =
    event.cupos_disponibles != null && Number(event.cupos_disponibles) <= 0;
  const registrationClosed = eventStatus !== "activo" || noSpotsAvailable;

  async function handleInscription() {
    setEnrollError("");
    setEnrolling(true);

    try {
      const result = await createInscripcion(event.id);
      setInscripcion(result);
    } catch (err) {
      setEnrollError(
        err instanceof Error
          ? err.message
          : "No se pudo completar la inscripción."
      );
    } finally {
      setEnrolling(false);
    }
  }
  return (
    <div>
      {/* Modales de Christian flotando sobre el detalle */}
      {showReporte && <ReporteModal eventId={event.id} onClose={() => setShowReporte(false)} />}
      {showResena && <ResenaModal eventId={event.id} onClose={() => setShowResena(false)} />}

      <div className="detail-hero">

        {event.imagen ? (
          <img
            src={event.imagen}
            alt=""
          />
        ) : (
          <CalendarDays size={48} />
        )}

      </div>

      <div className="detail-body">

        <p className="category">
          {String(
            event.categoria ?? "EVENTO"
          )}
        </p>

        <h2>
          {titleOf(event)}
        </h2>

        <p className="description">
          {String(
            event.descripcion ??
            "Sin descripción disponible."
          )}
        </p>

        <div className="detail-info">

          <Info
            icon={<CalendarDays />}
            label="Fecha"
            value={formatDate(
              dateOf(event)
            )}
          />

          <Info
            icon={<Clock3 />}
            label="Hora"
            value={String(
              event.hora_evento ??
              "Por definir"
            )}
          />

          <Info
            icon={<MapPin />}
            label="Lugar"
            value={placeOf(event)}
          />

          <Info
            icon={<Users />}
            label="Capacidad"
            value={String(
              event.aforo_maximo ??
              event.cupos_disponibles ??
              "No especificada"
            )}
          />

        </div>

        <div className="form-actions" style={{ flexWrap: "wrap" }}>

          {/* BOTÓN DE CHRISTIAN: Solo para organizadores */}
          {isOrganizador && (
            <button
              type="button"
              className="secondary-button"
              style={{ borderColor: "#1d4ed8", color: "#1d4ed8" }}
              onClick={() => setShowReporte(true)}
            >
              <BarChart3 size={18} /> Ver Reporte Estadístico
            </button>
          )}

          {isOrganizador && (
            <button
              type="button"
              className="primary-button"
              onClick={onEdit}
            >
              Editar evento
            </button>
          )}

          {isOrganizador && (
            <button
              type="button"
              className="secondary-button delete-button"
              onClick={onDelete}
            >
              Eliminar evento
            </button>
          )}

          {isEstudiante && (
            <button
              type="button"
              className={inscripcion ? "secondary-button" : "primary-button"}
              disabled={Boolean(inscripcion) || enrolling || registrationClosed}
              onClick={() => void handleInscription()}
            >
              <Ticket size={18} />
              {inscripcion
                ? "Inscripción confirmada"
                : enrolling
                  ? "Inscribiendo..."
                  : noSpotsAvailable
                    ? "Cupos agotados"
                    : eventStatus !== "activo"
                      ? "Inscripciones cerradas"
                      : "Inscribirse al evento"}
            </button>
          )}

          {/* BOTÓN DE CHRISTIAN: Solo para asistentes/estudiantes */}
          {isEstudiante && (
            <button
              type="button"
              className="primary-button"
              onClick={() => setShowResena(true)}
            >
              <Star size={18} /> Dejar una Reseña
            </button>
          )}

        </div>

      </div>

    </div>
  );
}

function Info({
  icon,
  label,
  value
}: {
  icon: React.ReactNode;
  label: string;
  value: string;
}) {
  return (
    <div className="info-row">

      <div className="info-icon">
        {icon}
      </div>

      <div>
        <span>{label}</span>
        <strong>{value}</strong>
      </div>

    </div>
  );
}

function CreateForm({
  event,
  onCancel,
  onCreated,
  onUpdated
}: {
  event?: EventItem | null;
  onCancel: () => void;
  onCreated?: (event: EventItem) => void;
  onUpdated?: (event: EventItem) => void;
}) {

  const isEditing = Boolean(event);

  const normalizeOptionalString = (
    value: unknown
  ): string => {
    if (
      value === null ||
      value === undefined ||
      value === ""
    ) {
      return "";
    }

    return typeof value === "string"
      ? value
      : String(value);
  };

  const normalizeOptionalNumber = (
    value: unknown
  ): number | undefined => {
    if (
      value === null ||
      value === undefined ||
      value === ""
    ) {
      return undefined;
    }

    const parsed = Number(value);

    return Number.isFinite(parsed)
      ? parsed
      : undefined;
  };

  const [form, setForm] =
    useState<CreateEventPayload>({
      titulo: normalizeOptionalString(
        event?.titulo
      ),
      descripcion: normalizeOptionalString(
        event?.descripcion
      ),
      fecha_evento:
        normalizeOptionalString(
          event?.fecha_evento
        ),
      hora_evento:
        normalizeOptionalString(
          event?.hora_evento
        ),
      lugar:
        normalizeOptionalString(
          event?.lugar ?? event?.ubicacion
        ),
      categoria_id:
        normalizeOptionalNumber(
          event?.categoria_id
        ),
      aforo_maximo:
        normalizeOptionalNumber(
          event?.aforo_maximo
        )
    });

  const categorias = {
    1: "Académico",
    2: "Bienestar Estudiantil",
    3: "Cultural",
    4: "Tecnológico",
    5: "Voluntariado"
  };

  const [saving, setSaving] =
    useState(false);

  const [error, setError] =
    useState("");

  const update = (
    key: keyof CreateEventPayload,
    value: string
  ) => {

    setForm(current => ({
      ...current,

      [key]:
        ["categoria_id", "aforo_maximo"]
          .includes(key)
          ? value === ""
            ? undefined
            : Number(value)
          : value
    }));

  };

  async function submit(
    e: React.FormEvent
  ) {
    e.preventDefault();

    setError("");
    setSaving(true);

    try {

      if (isEditing && event) {

        const updated =
          await updateEvent(
            event.id,
            form
          );

        onUpdated?.(updated);

      } else {

        const created =
          await createEvent(form);

        onCreated?.(created);

      }

    } catch (err) {

      setError(
        err instanceof Error
          ? err.message
          : isEditing
            ? "No se pudo actualizar el evento."
            : "No se pudo crear el evento."
      );

    } finally {

      setSaving(false);

    }
  }

  return (
    <form
      className="create-form"
      onSubmit={submit}
    >

      {error && (
        <div className="alert">
          {error}
        </div>
      )}

      <div className="form-section">

        <h2>
          {isEditing
            ? "Editar evento"
            : "Información del evento"}
        </h2>

        <p>
          {isEditing
            ? "Modifica los datos de tu evento."
            : "Completa los datos principales para publicar tu evento."}
        </p>

      </div>

      <div className="form-grid">

        <label className="full">
          Título

          <input
            required
            value={form.titulo}
            onChange={e =>
              update(
                "titulo",
                e.target.value
              )
            }
            placeholder="Ej. Conferencia de tecnología"
          />

        </label>

        <label className="full">
          Descripción

          <textarea
            required
            rows={5}
            value={form.descripcion}
            onChange={e =>
              update(
                "descripcion",
                e.target.value
              )
            }
            placeholder="Describe el evento..."
          />

        </label>

        <label>
          Fecha

          <input
            required
            type="date"
            value={form.fecha_evento}
            onChange={e =>
              update(
                "fecha_evento",
                e.target.value
              )
            }
          />

        </label>

        <label>
          Hora

          <input
            required
            type="time"
            value={form.hora_evento}
            onChange={e =>
              update(
                "hora_evento",
                e.target.value
              )
            }
          />

        </label>

        <label className="full">
          Ubicación

          <input
            required
            value={form.lugar}
            onChange={e =>
              update(
                "lugar",
                e.target.value
              )
            }
            placeholder="Lugar del evento"
          />

        </label>

        <label>
          Categoría

          <select
            required
            value={
              form.categoria_id ?? ""
            }
            onChange={e =>
              update(
                "categoria_id",
                e.target.value
              )
            }
          >

            <option value="">
              Selecciona una categoría
            </option>

            {Object.entries(
              categorias
            ).map(
              ([id, nombre]) => (
                <option
                  key={id}
                  value={id}
                >
                  {nombre}
                </option>
              )
            )}

          </select>

        </label>

        <label>
          Capacidad

          <input
            required
            type="number"
            min="1"
            value={
              form.aforo_maximo ?? ""
            }
            onChange={e =>
              update(
                "aforo_maximo",
                e.target.value
              )
            }
          />

        </label>

      </div>

      <div className="form-actions">

        <button
          type="button"
          className="secondary-button"
          onClick={onCancel}
        >
          Cancelar
        </button>

        <button
          type="submit"
          className="primary-button"
          disabled={saving}
        >
          {saving
            ? isEditing
              ? "Guardando..."
              : "Creando..."
            : isEditing
              ? "Guardar cambios"
              : "Crear evento"}
        </button>

      </div>

    </form>
  );
}



function ReporteModal({ eventId, onClose }: { eventId: number; onClose: () => void }) {
  const [reporte, setReporte] = useState<ReporteData | null>(null);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getReporte(eventId)
      .then(setReporte)
      .catch(err => setError(err.message))
      .finally(() => setLoading(false));
  }, [eventId]);

  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div className="detail-panel" onClick={e => e.stopPropagation()}>
        <button className="close-button" onClick={onClose}><X size={20} /></button>
        <div className="detail-body">
          <p className="category">ESTADÍSTICAS</p>
          <h2>Reporte del Evento</h2>

          {loading ? <div className="empty">Calculando métricas...</div> : error ? <div className="alert">{error}</div> : reporte && (
            <div className="stats" style={{ gridTemplateColumns: "repeat(2, 1fr)", marginTop: "20px" }}>
              <Stat icon={<Users size={20} />} label="Total Inscritos" value={reporte.total_inscritos} />
              <Stat icon={<CalendarDays size={20} />} label="Asistencia Final" value={reporte.asistencia_final} />
              <Stat icon={<BarChart3 size={20} />} label="Tasa de Asistencia" value={reporte.porcentaje_asistencia} />
              <Stat icon={<Star size={20} />} label="Valoración Promedio" value={reporte.valoracion_promedio} />
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

function ResenaModal({ eventId, onClose }: { eventId: number; onClose: () => void }) {
  const [calificacion, setCalificacion] = useState(0);
  const [comentario, setComentario] = useState("");
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(false);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (calificacion === 0) return setError("Por favor selecciona una calificación de 1 a 5 estrellas.");

    setError(""); setSaving(true);
    try {
      await createResena({ evento_id: eventId, calificacion, comentario });
      setSuccess(true);
      setTimeout(onClose, 2000); // Cierra automáticamente después de 2 segundos
    } catch (err) {
      setError(err instanceof Error ? err.message : "Error al enviar la reseña.");
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div className="detail-panel" onClick={e => e.stopPropagation()}>
        <button className="close-button" onClick={onClose}><X size={20} /></button>
        <div className="detail-body">
          <h2>Deja tu reseña</h2>
          <p className="description">Tu opinión nos ayuda a mejorar los próximos eventos en ESPOL.</p>

          {success ? (
            <div className="alert" style={{ background: "#eef3ff", color: "#1d4ed8", borderColor: "#7190dc" }}>
              ¡Gracias! Tu reseña se envió exitosamente.
            </div>
          ) : (
            <form className="create-form" style={{ padding: 0, border: "none" }} onSubmit={submit}>
              {error && <div className="alert">{error}</div>}

              <div className="form-grid" style={{ gridTemplateColumns: "1fr" }}>
                <label>
                  Calificación general
                  <div style={{ display: "flex", gap: "10px", marginTop: "10px" }}>
                    {[1, 2, 3, 4, 5].map(num => (
                      <Star
                        key={num}
                        size={32}
                        fill={calificacion >= num ? "#f59e0b" : "transparent"}
                        color={calificacion >= num ? "#f59e0b" : "#d9dee5"}
                        style={{ cursor: "pointer" }}
                        onClick={() => setCalificacion(num)}
                      />
                    ))}
                  </div>
                </label>

                <label className="full">
                  Comentario (Opcional)
                  <textarea rows={4} value={comentario} onChange={e => setComentario(e.target.value)} placeholder="¿Qué te pareció el evento?" maxLength={500} />
                </label>
              </div>

              <div className="form-actions">
                <button type="button" className="secondary-button" onClick={onClose}>Cancelar</button>
                <button type="submit" className="primary-button" disabled={saving}>{saving ? "Enviando..." : "Enviar reseña"}</button>
              </div>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}

export default App;