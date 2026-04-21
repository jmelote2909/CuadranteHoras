# CuadranteHoras

Aplicación desarrollada con Laravel, Livewire Volt y NativePHP para la gestión mensual de horas de trabajo, días amarillos, festivos, costes y resúmenes administrativos de operarios.

La herramienta permite registrar horas por empleado y por día, diferenciar entre jornadas normales, sábados, domingos y festivos, controlar bolsas de "días amarillos" y generar resúmenes administrativos exportables a Excel para distintas empresas.

## Funcionalidades principales

- Gestión mensual de cuadrantes por operario.
- Registro de horas diarias con guardado automático.
- Separación de datos por empresa.
- Modo de días amarillos y azules para control de bolsa de horas.
- Configuración global del coste de horas extras.
- Gestión de festivos del calendario.
- Registro de importes de operaciones externas.
- Vista de administración con resumen mensual por operario.
- Exportación de los datos administrativos a Excel.
- Ejecución en navegador o como aplicación de escritorio.

## Módulos de la aplicación

### Inicio

Desde la pantalla de inicio se gestionan dos elementos globales:

- Festivos del sistema.
- Tarifas globales de horas extras para días laborables, sábados y domingos.

### Cuadrante

Permite registrar las horas trabajadas por cada operario para cada día del mes. La aplicación clasifica automáticamente las horas según el tipo de día:

- Lunes a viernes.
- Sábados.
- Domingos.
- Festivos.

Además, calcula de forma automática:

- Horas totales del mes.
- Coste por tipo de jornada.
- Coste total mensual.
- Importe adicional de operaciones externas.

### Días amarillos

Este modo permite registrar horas especiales con dos marcadores visuales:

- Amarillo.
- Azul.

Con ello se controla la bolsa de horas y se muestran:

- Horas amarillas del mes.
- Horas azules del mes.
- Balance acumulado entre amarillos y azules.

### Administración

Genera una vista resumida por empresa y por mes con:

- Total de horas de lunes a viernes.
- Total de horas en sábado.
- Total de horas en domingo.
- Total de horas en festivos.
- Coste mensual total.
- Zona asignada al operario.

Desde esta vista se puede exportar la información a un archivo Excel `.xlsx`.

## Arquitectura

La aplicación sigue una arquitectura cliente-servidor basada en Laravel:

- Frontend: Blade + Livewire Volt + Tailwind CSS.
- Backend: Laravel 13 sobre PHP 8.3.
- Base de datos: SQLite.
- Exportación Excel: `maatwebsite/excel`.
- Aplicación de escritorio: NativePHP Desktop.

## Modelo de datos principal

La aplicación utiliza principalmente las siguientes entidades:

- `operators`: operarios, empresa y zona.
- `shifts`: horas por operario, fecha y color.
- `external_operations`: importes adicionales mensuales por operario.
- `holidays`: festivos configurados en el sistema.
- `settings`: configuración global de tarifas.

## Tecnologías utilizadas

- PHP 8.3
- Laravel 13
- Livewire 4
- Volt
- Blade
- Tailwind CSS 4
- Vite
- SQLite
- Maatwebsite Excel
- NativePHP Desktop
- Git

## Requisitos

- PHP 8.3 o superior
- Composer
- Node.js y npm
- SQLite

## Puesta en marcha

### Instalación

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
```

Si no existe el archivo de base de datos SQLite, créalo en:

```text
database/database.sqlite
```

### Ejecución en modo web

En una terminal:

```bash
npm run dev
```

En otra terminal:

```bash
php artisan serve
```

Después, abrir la URL local mostrada por Laravel en el navegador.

### Ejecución en modo escritorio

```bash
composer run native:dev
```

## Estructura funcional de rutas

- `/inicio`: configuración global y festivos.
- `/cuadrante`: cuadrante de Aráncalo.
- `/amarillos-arancalo`: días amarillos de Aráncalo.
- `/cima`: cuadrante de CIMA.
- `/amarillos-cima`: días amarillos de CIMA.
- `/administracion-arancalo`: resumen administrativo de Aráncalo.
- `/administracion-cima`: resumen administrativo de CIMA.

## Flujo de uso

1. Configurar los festivos y las tarifas globales desde Inicio.
2. Acceder al cuadrante de la empresa correspondiente.
3. Crear operarios si aún no existen.
4. Introducir las horas diarias de cada trabajador.
5. Registrar, si procede, operaciones externas mensuales.
6. Consultar los totales automáticos por tipo de día y coste.
7. Revisar la vista de Administración.
8. Exportar el resumen mensual a Excel.

## Exportación

La exportación administrativa genera un archivo Excel con el resumen mensual por operario y empresa, preparado para revisión administrativa.

## Estado del proyecto

Proyecto orientado a la gestión interna de cuadrantes, costes mensuales y control de horas de personal para varias empresas desde una única aplicación.
