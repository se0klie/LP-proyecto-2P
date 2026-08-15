import { useState } from "react";
import { CalendarDays, LogIn, Mail, Lock } from "lucide-react";
import { userLogIn, type LoginPayload, type AuthUser } from "./api";

export default function Login({
  onLogin,
  onSwitchToRegister
}: {
  onLogin: (user: AuthUser) => void;
  onSwitchToRegister: () => void;
}) {
  const [form, setForm] = useState<LoginPayload>({ correo: "", contrasena: "" });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const update = (key: keyof LoginPayload, value: string) =>
    setForm(current => ({ ...current, [key]: value }));

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      const user = await userLogIn(form);
      onLogin(user);
    } catch (err) {
      setError(err instanceof Error ? err.message : "No se pudo iniciar sesión.");
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
          <h2>Inicia sesión</h2>
          <p>Accede a tu panel para gestionar tus eventos.</p>
        </div>

        <form className="create-form" onSubmit={submit}>
          {error && <div className="alert">{error}</div>}

          <div className="form-grid">
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
              Contraseña
              <div className="input-with-icon">
                <Lock size={16} />
                <input
                  required
                  type="password"
                  value={form.contrasena}
                  onChange={e => update("contrasena", e.target.value)}
                  placeholder="••••••••"
                  autoComplete="current-password"
                />
              </div>
            </label>
          </div>

          <div className="form-actions">
            <button type="submit" className="primary-button" disabled={loading}>
              <LogIn size={18} /> {loading ? "Ingresando..." : "Ingresar"}
            </button>
          </div>
        </form>

        <p className="auth-switch">
          ¿No tienes cuenta?{" "}
          <button type="button" className="link-button" onClick={onSwitchToRegister}>
            Regístrate
          </button>
        </p>
      </div>
    </div>
  );
}
