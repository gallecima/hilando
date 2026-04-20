# 📦 Configurar tamaño máximo de subida en Laravel (AlmaLinux + PHP 8.3 + Nginx)

Este documento explica cómo aumentar el tamaño máximo de archivos que se pueden subir en un entorno de producción con:

- AlmaLinux
- PHP 8.3 (php-fpm)
- Nginx
- Laravel

---

# 🔍 1. Identificar el php.ini correcto

En este caso, PHP-FPM está usando:

/etc/php/8.3/fpm/php.ini

Podés confirmarlo con:

sudo php-fpm8.3 -i | grep "Loaded Configuration File"

---

# ⚙️ 2. Modificar límites en PHP

Editar el archivo:

sudo nano /etc/php/8.3/fpm/php.ini

Buscar y modificar:

upload_max_filesize = 20M
post_max_size = 25M
memory_limit = 256M
max_execution_time = 120

⚠️ Importante:
- post_max_size debe ser mayor o igual que upload_max_filesize

---

# 🔁 3. Reiniciar PHP-FPM

sudo systemctl restart php8.3-fpm

---

# 🌐 4. Configurar Nginx

Editar el vhost:

sudo nano /etc/nginx/sites-available/bilingualtreasure.com.conf

Agregar dentro de server {}:

client_max_body_size 20M;

Guardar y aplicar:

sudo nginx -t
sudo systemctl reload nginx

---

# 🧪 5. Verificar configuración

Crear archivo temporal:

echo "<?php phpinfo();" > /var/www/html/treasure/public/info.php

Abrir en navegador:
https://bilingualtreasure.com/info.php

Buscar:
- upload_max_filesize
- post_max_size

Eliminar archivo:

rm /var/www/html/treasure/public/info.php

---

# 🧠 6. Validación en Laravel

Ejemplo:

'image' => 'image|max:20480'

📌 Importante:
- max está en KB
- 20480 KB = 20 MB

---

# ✅ 7. Checklist final

- php.ini actualizado
- PHP-FPM reiniciado
- Nginx configurado
- Nginx recargado
- Validación Laravel correcta
- Verificación realizada

---

🚀 Sistema listo para producción
