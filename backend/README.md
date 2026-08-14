# ESPOL Eventos — Módulo de Gestión de Eventos (Backend PHP)

Módulo backend correspondiente al objetivo específico:
> "Diseñar e implementar el módulo de creación, edición y administración
> de eventos con control de aforo" — **Responsable: Hailie Jimenez**

Este entregable implementa dos funcionalidades:

1. **Crear un evento** — `POST /api/eventos/crear.php`
2. **Ver panel de organizador** — `GET /api/eventos/panel-organizador.php`

---

## 1. Requisitos

- PHP >= 8.0 con extensión `pdo_mysql`
- MySQL / MariaDB
- Servidor web (Apache/Nginx) o el servidor embebido de PHP

## 2. Instalación

```bash
mysql -u root -p < database/schema.sql

export DB_HOST=127.0.0.1
export DB_NAME=espol_eventos
export DB_USER=root
export DB_PASS=pswd
export APP_ENV=development

# 3. Levantar servidor de pruebas
php -S localhost:8000 -t .
```


```
Para simular login:

```
GET http://localhost:8000/api/dev-login.php?usuario_id=1
```

---

## 4. Endpoint: Crear un evento

**POST** `/api/eventos/crear.php`

### Request body (JSON)

```json
{
  "titulo": "Feria de Emprendimiento ESPOL 2026",
  "descripcion": "Espacio para que estudiantes presenten sus proyectos de emprendimiento ante inversionistas.",
  "fecha_evento": "2026-09-15",
  "hora_evento": "09:00",
  "lugar": "Coliseo ESPOL",
  "categoria_id": 4,
  "aforo_maximo": 300
}
```

## 5. Endpoint: Ver panel de organizador

**GET** `/api/eventos/panel-organizador.php`

### Query params (opcionales)

| Parámetro | Valores               | Descripción                        |
|-----------|------------------------|-------------------------------------|
| `estado`  | activo, cancelado, finalizado | Filtra los eventos por estado |

Ejemplo: `/api/eventos/panel-organizador.php?estado=activo`

## Pruebas rápidas con cURL

```bash
curl -c cookies.txt "http://localhost:8000/api/dev-login.php?usuario_id=1"

curl -b cookies.txt -X POST http://localhost:8000/api/eventos/crear.php \
  -H "Content-Type: application/json" \
  -d '{
        "titulo": "Feria de Emprendimiento ESPOL 2026",
        "descripcion": "Espacio para que estudiantes presenten sus proyectos.",
        "fecha_evento": "2026-09-15",
        "hora_evento": "09:00",
        "lugar": "Coliseo ESPOL",
        "categoria_id": 4,
        "aforo_maximo": 300
      }'

curl -b cookies.txt "http://localhost:8000/api/eventos/panel-organizador.php"
