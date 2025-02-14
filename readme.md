# Sistema de procesamiento de pedidos con Symfony y RabbitMQ

Este proyecto es una aplicación basada en Symfony que demuestra cómo procesar pedidos de forma asíncrona usando RabbitMQ. Simula un sistema de e-commerce donde los pedidos se envían a una cola de mensajes para su procesamiento, y el stock se gestiona de manera concurrente.

---

## Tabla de Contenidos

1. [Características](#características)
2. [Entorno utilizado](#entorno-utilizado)
3. [Tecnologías utilizadas](#tecnologías-utilizadas)
4. [¿Por qué estas elecciones?](#por-qué-estas-elecciones)
5. [Diseño y lógica del proyecto](#diseño-y-lógica-del-proyecto)
6. [Instalación](#instalación)
7. [Configuración](#configuración)
8. [Uso](#uso)
9. [Testing](#testing)
10. [Estructura del proyecto](#estructura-del-proyecto)
11. [Licencia](#licencia)
12. [Agradecimientos](#agradecimientos)

---

## Características

- **Procesamiento Asíncrono**: Los pedidos se envían a RabbitMQ para procesamiento en segundo plano.
- **Gestión de Stock**: El sistema verifica y reduce los niveles de stock, garantizando consistencia incluso con alta concurrencia.
- **Manejo de Errores**: Los mensajes fallidos se reintentan automáticamente y se mueven a una cola de errores para su inspección.
- **Integración con Docker**: El proyecto está completamente containerizado para facilitar su configuración.
- **Herramientas CLI**: Incluye comandos para enviar pedidos y resetear el stock.
- **phpMyAdmin**: Se ha añadido un contenedor con phpMyAdmin para facilitar la gestión visual de la base de datos.

---

## Entorno utilizado

- **Windows 10**: Aunque acostumbrado a usar Linux para trabajar, use mi ordenador personal. Sí, el que uso para 
los jueguecitos.
- **PHPStorm 2024**: Es el IDE que más he usado, y le tengo cariño, 
a pesar de que usa más recursos que Chrome con 3 pestañas simultáneas.
- **Docker Desktop con WSL 2**: Fácil y sencillo para Windows.
- **Chrome**: Para visualizar las interfaces de RabbitMQ y phpMyAdmin.

---

## Tecnologías utilizadas

- **Symfony 6.4**: Framework PHP para aplicaciones escalables.
- **RabbitMQ**: Broker de mensajes para comunicación asíncrona.
- **Docker**: Para containerización y despliegue sencillo.
- **MySQL**: Base de datos principal para almacenar pedidos y stock.
- **PHPUnit**: Para pruebas unitarias y funcionales.
- **phpMyAdmin**: Herramienta de gestión visual para MySQL.

---

## ¿Por qué estas elecciones?

El principal motivo es adaptarse a los requerimientos de la prueba técnica, asi como usar el stack indicado en la oferta.

### Symfony
- **Indicado como requerimiento.**
- Se usa la versión 6.4, indicada como stack en la oferta laboral.
- **Componente Messenger**: Simplifica la integración con RabbitMQ y ofrece soporte nativo para colas de mensajes.

### RabbitMQ
- **Indicado como requerimiento.**
- **Colas de Errores**: Los mensajes fallidos se redirigen a una cola específica para su análisis posterior.

### Docker
- **Indicado como requerimiento.**
- **Configuración Sencilla**: Se ha intentado simplificar todo el proceso de despliegue (PHP, MySQL, Composer, Migraciones, RabbitMQ, Nginx, phpMyAdmin) desde un comando.

### MySQL
- **Indicado como requerimiento.**
- Se usa version 8.0, indicada como stack en oferta laboral.

### phpMyAdmin
- **Gestión Visual**: Facilita la administración de la base de datos mediante una interfaz gráfica.

---

## Diseño y lógica del proyecto

### Flujo de la aplicación

1. **Creación de un Pedido**:
    - Cuando un cliente realiza un pedido, se ejecuta el comando `app:send-order`. Este comando genera un mensaje con los detalles del pedido (ID del pedido, ID del usuario, ID del producto y timestamp) y lo envía a la cola de RabbitMQ.

2. **Procesamiento del Pedido**:
    - Un consumidor (worker) escucha la cola de RabbitMQ y procesa los mensajes uno por uno.
    - Para cada pedido, el sistema verifica el stock del producto:
        - Si hay stock disponible, se aprueba el pedido y se reduce el stock en 1.
        - Si no hay stock, el pedido se rechaza.
    - El estado del pedido (aprobado o rechazado) se guarda en la base de datos.

3. **Manejo de Errores**:
    - Si ocurre un error durante el procesamiento (por ejemplo, un problema de conexión a la base de datos), el mensaje se reintenta automáticamente hasta 3 veces.
    - Si el mensaje sigue fallando después de los reintentos, se mueve a una cola de errores (dead-letter queue) para su inspección manual.
    - Igualmente, se va logueando parte del flujo en el log de Symfony (`var/log/dev.log`), tanto a nivel info como error.

4. **Reset del Stock**:
    - El comando `app:reset-stock` permite restablecer el stock de todos los productos a un valor predeterminado (10). Esto es útil para pruebas y simulaciones.

---

### Decisiones de diseño

1. **Dos entidades principales**:
    - **Order**: Representa un pedido con su estado (pendiente, aprobado, rechazado) y detalles (ID del pedido, ID del usuario, etc.).
    - **Stock**: Representa el stock de un producto. Se decidió separar el stock en su propia entidad para facilitar su gestión y evitar acoplamiento con la lógica de pedidos.

2. **Separación de responsabilidades**:
    - **Repositorios**: Se encargan de interactuar con la base de datos (por ejemplo, buscar o crear una orden). Esto permite mantener la lógica de negocio separada del acceso a datos.
    - **Servicios**: Contienen la lógica de negocio (por ejemplo, verificar y reducir el stock). Esto hace que el código sea más modular y fácil de probar.

3. **Uso de RabbitMQ**:
    - Requisito. Sus funciones son:
      - Desacoplar la creación de pedidos de su procesamiento.
      - Escalar el sistema fácilmente añadiendo más consumidores.
      - Manejar errores de manera robusta mediante colas de errores.

4. **Docker y phpMyAdmin**:
    - Docker se utilizó, por un lado, por ser requisito, y por el otro para crear un entorno de desarrollo consistente y fácil de configurar.
    - phpMyAdmin se añadió para facilitar la visualización de la base de datos durante el desarrollo y las pruebas posteriores.

---

### Funcionamiento Interno

1. **Mensajes y colas**:
    - Cada pedido se convierte en un mensaje que se envía a RabbitMQ. Los mensajes contienen toda la información necesaria para procesar el pedido.
    - RabbitMQ garantiza que los mensajes se entreguen a los consumidores en orden y sin pérdidas.

2. **Concurrencia y bloqueos**:
    - Para evitar condiciones de carrera al reducir el stock, se utiliza un bloqueo pesimista (`PESSIMISTIC_WRITE`). Esto asegura que solo un consumidor pueda modificar el stock de un producto a la vez.

3. **Logs y monitoreo**:
    - Todas las operaciones importantes (creación de pedidos, procesamiento, errores) se registran en logs para facilitar la depuración y el monitoreo.
    - RabbitMQ proporciona una interfaz gráfica para monitorear las colas y los mensajes.

---

### Beneficios del enfoque

- **Escalabilidad**: El enfoque ha sido que el sistema pueda manejar un alto volumen de pedidos añadiendo más consumidores (eso espero).
- **Robustez**: Los errores se manejan de manera automática y los mensajes fallidos no se pierden.
- **Modularidad**: Se ha intentado separar el mayor número de responsabilidades, para que el código sea más mantenible y testable.
- **Facilidad de Pruebas**: El uso de Docker y herramientas como phpMyAdmin facilita las pruebas y el desarrollo local,
asi como la posterior comprobación de la prueba técnica.

---

## Instalación

### Prerrequisitos

- Docker y Docker Compose instalados en tu máquina, o Docker desktop.

### Pasos

1. Clona el repositorio:
   ```bash
   git clone https://github.com/borz93/ipglobal-app.git
   cd ipglobal-app
   ```

2. Inicia los contenedores de Docker:
   ```bash
   docker-compose up -d
   ```
   Esto:
    - Construye el contenedor de PHP.
    - Inicia MySQL, RabbitMQ, Nginx y phpMyAdmin.
    - Instala dependencias de Composer automáticamente (vía `entrypoint.sh`).
    - Ejecuta las migraciones de la base de datos.

3. Para verificar que los servicios estén funcionando:
    - PHP: Accede a la aplicación en `http://localhost:8080`.
    - RabbitMQ Management: Accede en `http://localhost:15672` (usuario: `guest`, contraseña: `guest`).
    - phpMyAdmin: Accede en `http://localhost:8081` (usuario: `symfony`, contraseña: `symfony`).

---

## Configuración

### Variables de entorno

| Variable          | Descripción                                  | Valor por Defecto                          |
|-------------------|----------------------------------------------|--------------------------------------------|
| `DATABASE_URL`    | Cadena de conexión a MySQL.                  | `mysql://symfony:symfony@mysql_db:3306/symfony_db` |
| `RABBITMQ_URL`    | URL de conexión a RabbitMQ.                  | `amqp://guest:guest@rabbitmq_server:5672`          |
| `APP_ENV`         | Entorno de la aplicación (`dev`, `prod`).    | `dev`                                      |
| `APP_SECRET`      | Clave secreta para componentes de seguridad. | `your_app_secret`                          |

---

## Uso

### Enviar un pedido

Envía un pedido a la cola con:
```bash
  docker-compose exec php bin/console app:send-order <id-producto>
```
**Ejemplo**:
```bash
  docker-compose exec php bin/console app:send-order 1
```

### Procesar pedidos

Ejecuta el consumidor para procesar pedidos:
```bash
  docker-compose exec php bin/console messenger:consume async -vv
```

### Simulación de múltiples pedidos

Envía 10 pedidos para el producto ID 1.:
```bash
  docker-compose exec php bash -c "for i in {1..10}; do bin/console app:send-order 1; done"
```
### Resetear el stock

Restablece el stock de todos los productos que existan en la tabla `stock` a 10:
```bash
  docker-compose exec php bin/console app:reset-stock
```

### Entrar en contenedor php

Entrar en el bash del contenedor php
```bash
  docker-compose exec php bash
```

---

## Testing

El proyecto incluye pruebas unitarias y funcionales.
Se pueden añadir bastantes más, pero sirven para ejemplificar el uso de phpUnit.

Para ejecutarlas:


1. Ejecuta los tests:
   ```bash
   docker-compose exec php ./vendor/bin/phpunit
   ```

---

## Estructura del proyecto

ipglobal_app/

├── src/

│ ├── Command/ # Comandos de Symfony

│ ├── Entity/ # Entidades de la base de datos

│ ├── Message/ # Clases de mensajes para RabbitMQ

│ ├── MessageHandler/ # Controladores de mensajes

│ ├── Repository/ # Repositorios personalizados

│ ├── Service/ # Lógica de negocio

│ ├── .env # Variables de entorno

├── tests/ # Pruebas unitarias y funcionales

├── docker/ # Configuración Nginx y comandos (`entrypoint.sh`)

├── docker-compose.yml # Configuración de Docker Compose

├── Dockerfile # Dockerfile para PHP

└── README.md # Este archivo

---

## Licencia

Este proyecto está bajo la licencia MIT.

---

## Agradecimientos

- Symfony, RabbitMQ y demás stack utilizado en la prueba
- Docker por hacer la vida más sencilla.
- phpMyAdmin por evitar crear un visualizador de datos (punto de mejora de la aplicación)
- Y en general, la oportunidad de realizar esta prueba, que tanto para bien o mal,
me ha servido para aprender o usar algunas cosillas
- A Dios, Yahve, Ala, Shiva, Buda, Cthulhu... Por si acaso.