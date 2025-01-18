
# Instrucciones para ejecutar el proyecto Laravel SkywealthBack

## Pasos para ejecutar el proyecto

### 1. Clonar el repositorio

Primero, clona el repositorio del proyecto desde GitHub:

```bash
git clone https://github.com/TeoMena98/SkywealthBack.git
```

### 2. Instalar dependencias de PHP

Dentro del directorio del proyecto, ejecuta Composer para instalar las dependencias de PHP:

```bash
cd SkywealthBack
composer install
```

### 3. Configurar el archivo `.env`

El archivo `.env` contiene configuraciones importantes para el proyecto, como la configuración de la base de datos y otros servicios. Copia el archivo `.env.example` a `.env`:

```bash
cp .env.example .env
```

Luego, abre el archivo `.env` y ajusta las configuraciones según tu entorno (como las credenciales de la base de datos).

### 4. Generar la clave de la aplicación

Laravel necesita una clave de aplicación única. Ejecuta el siguiente comando para generar la clave:

```bash
php artisan key:generate
```

### 5. Configurar la base de datos

Si usas MySQL, asegúrate de tener una base de datos creada en tu servidor. Luego, actualiza las siguientes líneas en el archivo `.env` con tus credenciales de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_base_de_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 6. Ejecutar migraciones 

Las migraciones no solo crean las tablas necesarias para la base de datos, sino que también insertan algunos datos iniciales necesarios para que el sistema funcione correctamente.

Ejecuta el siguiente comando para aplicar las migraciones:

```bash
php artisan migrate
```

### 7. Credenciales de inicio de sesión

Después de ejecutar las migraciones, puedes iniciar sesión con las siguientes credenciales predeterminadas para acceder al sistema:

- **Correo electrónico**: `admins@test.com`
- **Contraseña**: `123`


### 8. Iniciar el servidor de desarrollo
Finalmente, puedes iniciar el servidor de desarrollo de Laravel:

```bash
php artisan serve
```

Esto arrancará un servidor local en `http://localhost:8000`.

