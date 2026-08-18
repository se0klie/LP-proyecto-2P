# Proyecto LP 

## Requisitos

### Backend

- PHP 8.5.8 con extensión `pdo_mysql`
- MySQL

### Frontend

- Node.js 24.14.1
- npm 11.11.0
- TypeScript 5.9.3

## Librerías y paquetes utilizados

### Frontend

- `@types/react-dom` 18.3.7
- `@types/react` 18.3.31
- `@vitejs/plugin-react` 4.7.0
- `lucide-react` 0.468.0
- `react-dom` 18.3.1
- `react` 18.3.1
- `typescript` 5.9.3
- `vite` 6.4.3

## Instalación del Backend

1. Ingresar a la carpeta del backend:

```bash
cd backend
```
## 2. Configurar la base de datos.

```bash
mysql -u root -p < database/schema.sql
```
(Asegurarse de tener todas las variables de entorno)

# 3. Levantar servidor de pruebas
```bash
php -S localhost:8000 -t .
```
#4. El backend estara disponible en http://localhost:8000

### Instalación y ejecución del FrontEnd

1. Ingresar a la carpeta del frontend:

```bash
cd frontend
```

2. Instalar todas las dependencias necesarias que aparecen en package.json.

```bash
npm install
```

3. Finalmente, desplegar el frontend.

```bash
npm run dev
```
La URL del frontend será mostrada en la terminal al iniciar el servidor de desarrollo.
