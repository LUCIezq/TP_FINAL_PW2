# 🧠 Preguntados — Juego de preguntas y respuestas

**Preguntados** es un proyecto web desarrollado en **PHP** siguiendo el patrón **MVC (Modelo–Vista–Controlador)**.  
El sistema permite que los usuarios se registren, verifiquen su cuenta por correo electrónico, respondan preguntas para ganar puntos y suban de nivel.  
Incluye un rol especial de **editor**, encargado de crear y gestionar las preguntas del juego.

---

## 🚀 Tecnologías utilizadas

- **PHP 8+**
- **MySQL / MariaDB**
- **HTML5, CSS3 y JavaScript**
- **Mustache** (motor de plantillas)
- **PHPMailer** o `mail()` para envío de correos
- **XAMPP** (entorno local)
- **InfinityFree** (hosting de prueba)

---

## 🧩 Arquitectura — Patrón MVC

El proyecto está estructurado bajo el modelo **MVC**, lo que asegura una separación clara entre:

- **Modelo:** manejo de la base de datos y la lógica de negocio (usuarios, preguntas, respuestas, niveles, etc.).
- **Vista:** archivos Mustache encargados de renderizar el contenido dinámico en HTML.
- **Controlador:** intermediario entre la vista y el modelo; gestiona las acciones del usuario y las respuestas del sistema.

Esta arquitectura facilita la escalabilidad, el mantenimiento y la reutilización del código.

---

## ⚙️ Funcionalidades principales

### 👤 Sistema de usuarios

- Registro de usuarios con **hash seguro de contraseñas** (`password_hash` y `password_verify`).
- Envío de **correo de verificación** con **token único** generado al registrarse.
- Activación de cuenta mediante enlace recibido por email.
- Inicio de sesión y manejo de sesión seguro.
- Roles definidos:
  - 🧑‍🎓 **Jugador:** puede responder preguntas y acumular puntos.
  - ✏️ **Editor:** puede crear, editar y eliminar preguntas.

### 🎯 Lógica de juego

- Cada pregunta respondida correctamente otorga **1 punto**.
- El nivel del usuario se determina por la cantidad de puntos acumulados.
- Los niveles se definen en una tabla `nivel` con valores preconfigurados (por ejemplo: 1, 2, 3... según los puntos necesarios).

### 🧱 Otras características

- Validaciones tanto del lado del cliente como del servidor.
- Codificación **UTF-8** para evitar errores de caracteres.
- Implementación adaptable a distintos servidores (local o hosting gratuito como InfinityFree).

---

## 🔑 Credenciales de prueba (rol Editor)

- Email: editor@editor.com
- Contraseña: editoreditor

> Con este usuario podés ingresar al panel de edición para crear o modificar preguntas.

## 👨‍💻 Autor Ezequiel Luci - Yamila Sleiman - Leandro Carrazo Pedraza

- 📘 Proyecto académico para la Tecnicatura en Programación Web — Universidad Nacional de La Matanza (UNLaM)