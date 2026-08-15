# Organizador Dashboard — React + TypeScript

Frontend tipo dashboard para el panel del organizador.

## Endpoints usados

- `GET /api/eventos/catalogo.php` para cargar eventos.
- `GET /api/eventos/catalogo.php?busqueda=texto` para búsqueda.
- `GET /api/eventos/detalle.php?id=1` para el detalle.
- `POST /api/eventos/crear.php` para crear un evento.

El backend que compartiste confirma estos endpoints y que la creación exige `Auth::requireOrganizador()`.

## Ejecutar

```bash
npm install
npm run dev
```

Por defecto Vite usa `http://localhost:5173` y redirige `/api` a `http://localhost:8000`.

Si tu PHP corre en otra dirección, cambia `target` en `vite.config.ts` o define:

```env
VITE_API_URL=http://localhost:8000
```

## Autenticación

El servicio agrega automáticamente:

```text
Authorization: Bearer <token>
```

si existe `localStorage.access_token` o `localStorage.token`.

Como el archivo de login no fue incluido, no se inventó un flujo de autenticación. Cuando conectes tu login, guarda allí el token.

## Importante sobre el POST

El endpoint `crear.php` solo muestra que recibe un POST y llama a `EventoController::crear($organizadorId)`. Como no se incluyó `EventoController.php`, el frontend usa un payload razonable (`titulo`, `descripcion`, `fecha`, `hora`, `ubicacion`, `categoria_id`, `capacidad`, `precio`). Si el controller espera nombres distintos, ajusta `CreateEventPayload` y el formulario en `src/App.tsx`.
