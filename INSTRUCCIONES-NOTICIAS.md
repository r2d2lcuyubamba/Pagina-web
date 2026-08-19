# Sección de Noticias — Guía rápida

Se agregó una sección de **Noticias** al sitio, con una zona pública (todos la
leen) y un **panel privado** protegido con usuario y contraseña para que el
personal publique.

## ¿Qué archivos se añadieron?

| Archivo | Para qué sirve |
|---|---|
| `noticias.php` | Página pública con la lista de noticias. |
| `noticia.php` | Página pública de cada noticia individual. |
| `incluir.php` | Menú, logo y pie compartidos (para no repetirlos). |
| `panel/` | Zona privada: acceso y administración de noticias. |
| `panel/datos/` | Aquí se guardan la cuenta y las noticias (archivos JSON). |
| `img/noticias/` | Aquí se guardan las fotos que se suben. |

No se modificó el diseño ni el funcionamiento de las páginas que ya existían;
solo se agregó el enlace **"Noticias"** en el menú y en el pie.

## Primer uso (crear la cuenta)

1. Sube todos los archivos a tu hosting (igual que subes el resto de la web).
2. Entra en el navegador a **`tudominio.com/panel/`**.
3. La primera vez te pedirá **crear el usuario y la contraseña** del personal.
   Elígelos y guárdalos en un lugar seguro. **Esa clave la comparte todo el personal.**
4. Listo: ya puedes publicar noticias.

> No hay ninguna contraseña "por defecto": la cuenta no existe hasta que tú la
> creas en ese primer paso, así nadie más la conoce.

## Cómo publicar una noticia

1. Entra a `tudominio.com/panel/` e inicia sesión.
2. Completa el formulario: **título**, **fecha**, categoría (opcional),
   un **resumen** breve (opcional) y el **contenido**.
   - Para separar párrafos, deja una **línea en blanco** entre ellos.
3. Si quieres, adjunta una **foto de portada** (JPG, PNG, WEBP o GIF, máx. 5 MB).
4. Pulsa **Publicar noticia**. Aparecerá al instante en `noticias.php`.

Desde la lista de abajo del panel puedes **Ver**, **Editar** o **Eliminar**
cualquier noticia. Para cambiar la clave usa **"Cambiar contraseña"**.

## Requisitos del hosting

- Debe permitir **PHP** (tu contador de visitas ya lo usa, así que sí).
- La carpeta `panel/datos/` y `img/noticias/` deben tener **permiso de escritura**
  (normalmente `755`; si tu hosting lo exige, `775`). Si al crear la cuenta ves un
  error de permisos, ajusta esos permisos desde tu panel de hosting o por FTP.

## Seguridad incluida

- Las contraseñas se guardan **cifradas** (nunca en texto plano).
- El acceso al panel requiere iniciar sesión; sin sesión, redirige al login.
- Los formularios están protegidos contra CSRF.
- El texto de las noticias se limpia para evitar inyección de código.
- La carpeta `panel/datos/` está bloqueada al acceso web con un `.htaccess`
  (solo funciona en servidores Apache; casi todos los hosting compartidos lo son).

## Nota para copias de seguridad

Las noticias y la cuenta viven en `panel/datos/*.json` y las fotos en
`img/noticias/`. Esos archivos se crean en el servidor y **no** se guardan en el
repositorio de código. Si quieres respaldarlos, descárgalos por FTP de vez en cuando.
