import { useState } from "react";
import { CalendarDays, UserPlus, Mail, Lock, User } from "lucide-react";
import { userRegister, type RegisterPayload, type AuthUser } from "./api";

export default function Register({
  onRegistered,
  onSwitchToLogin
}: {
  onRegistered: (user: AuthUser) => void;
  onSwitchToLogin: () => void;
}) {
  const [form, setForm] = useState<RegisterPayload>({
    nombre: "",
    correo: "",
    contrasena: "",
    confirmar_contrasena: "",
    cargo:""
  });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const update = (key: keyof RegisterPayload, value: string) =>
    setForm(current => ({ ...current, [key]: value }));

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setError("");

    if (form.contrasena !== form.confirmar_contrasena) {
      setError("Las contraseñas no coinciden.");
      return;
    }

    setLoading(true);
    try {
      const user = await userRegister(form);
      onRegistered(user);
    } catch (err) {
      setError(err instanceof Error ? err.message : "No se pudo crear la cuenta.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="auth-shell">
      <div className="auth-card">
        <div className="brand auth-brand">
          <div className="brand-mark"><CalendarDays size={21} /></div>
          <div>
            <strong>Eventia</strong>
            <span>Organizador</span>
          </div>
        </div>

        <div className="form-section">
          <h2>Crea tu cuenta</h2>
          <p>Regístrate para empezar a organizar tus eventos.</p>
        </div>

        <form className="create-form" onSubmit={submit}>
          {error && <div className="alert">{error}</div>}

          <div className="form-grid">
            <label className="full">
              Nombre completo
              <div className="input-with-icon">
                <User size={16} />
                <input
                  required
                  value={form.nombre}
                  onChange={e => update("nombre", e.target.value)}
                  placeholder="Tu nombre y apellido"
                  autoComplete="name"
                />
              </div>
            </label>

            <label className="full">
              Correo
              <div className="input-with-icon">
                <Mail size={16} />
                <input
                  required
                  type="email"
                  value={form.correo}
                  onChange={e => update("correo", e.target.value)}
                  placeholder="tucorreo@espol.edu.ec"
                  autoComplete="email"
                />
              </div>
            </label>

            <label className="full">
              Cargo
              <select
                required
                value={form.cargo}
                onChange={e => update("cargo", e.target.value)}
              >
                <option value="">Selecciona tu cargo</option>
                <option value="estudiante">Estudiante</option>
                <option value="administrativo">Administrativo</option>
                <option value="profesor">Profesor</option>
              </select>
            </label>

            <label>
              Contraseña
              <div className="input-with-icon">
                <Lock size={16} />
                <input
                  required
                  type="password"
                  value={form.contrasena}
                  onChange={e => update("contrasena", e.target.value)}
                  placeholder="••••••••"
                  autoComplete="new-password"
                  minLength={8}
                />
              </div>
            </label>

            <label>
              Confirmar contraseña
              <div className="input-with-icon">
                <Lock size={16} />
                <input
                  required
                  type="password"
                  value={form.confirmar_contrasena}
                  onChange={e =>
                    update("confirmar_contrasena", e.target.value)
                  }
                  placeholder="••••••••"
                  autoComplete="new-password"
                  minLength={8}
                />
              </div>
            </label>
          </div>

          <div className="form-actions">
            <button
              type="submit"
              className="primary-button"
              disabled={loading}
            >
              <UserPlus size={18} />
              {loading ? "Creando cuenta..." : "Crear cuenta"}
            </button>
          </div>
        </form>

        <p className="auth-switch">
          ¿Ya tienes cuenta?{" "}
          <button type="button" className="link-button" onClick={onSwitchToLogin}>
            Inicia sesión
          </button>
        </p>
      </div>
    </div>
  );
}
