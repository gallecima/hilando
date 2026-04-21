# 🚀 Guía Completa de Deploy Laravel en VPS (AlmaLinux + Nginx)

Esta guía resume el proceso completo para desplegar un proyecto Laravel desde cero en un VPS productivo.


---

# 🔐 0. Configuración de Nginx + SSL

## Crear server block
Archivo:
```
/etc/nginx/sites-available/tu-dominio.conf
```

Ejemplo:
```
server {
    server_name tu-dominio.com;

    root /var/www/html/proyecto/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
}
```

Activar:
```
ln -s /etc/nginx/sites-available/tu-dominio.conf /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

## Instalar SSL
```
certbot --nginx -d tu-dominio.com
```

---

# 🧹 1. Limpiar Git viejo y crear nuevo repo local

```
rm -rf .git
git init
git add .
git commit -m "Initial commit"
```

---

# 🌐 2. Crear repositorio en GitHub

- Crear repo vacío (sin README)

---

# 🔑 3. Crear Token GitHub

- GitHub → Settings → Developer Settings → Personal Access Tokens
- Permisos: repo
- Guardalo en un gestor seguro. No lo pegues en el repositorio.

---

# ⬆️ 4. Subir proyecto a GitHub

```
git remote add origin https://TOKEN@github.com/usuario/repo.git
git branch -M main
git push -u origin main
```

---

# 📥 5. Clonación en VPS

```
cd /var/www/html
git clone https://TOKEN@github.com/usuario/repo.git proyecto
cd proyecto
```

---

# 🗄️ 6. Base de datos MySQL

Entrar a MySQL:
```
mysql -u root -p
```

Crear:
```
CREATE DATABASE proyecto_db;

CREATE USER 'proyecto_user'@'%' IDENTIFIED BY 'password_seguro';

GRANT ALL PRIVILEGES ON proyecto_db.* TO 'proyecto_user'@'%';

FLUSH PRIVILEGES;
```

---

# 📦 7. Instalar dependencias PHP

```
composer install --no-dev --optimize-autoloader
```

---

# ⚙️ 8. Configuración Laravel

Copiar env:
```
cp .env.example .env
```

Editar:
```
nano .env
```

Generar key:
```
php artisan key:generate
```

---

# 🎨 9. Frontend (Vite)

```
npm install
npm run build
```

---

# 🔄 10. Sincronización de archivos (uploads)

Desde local:
```
rsync -avzi --progress -e "ssh -p 5676" \
  /Applications/XAMPP/xamppfiles/htdocs/hilandoculturas/storage/app/public/ \
  root@138.219.42.49:/var/www/html/hilando/storage/app/public/

```

---

# 🔐 11. Permisos (CRÍTICO)

```
chown -R www-data:www-data /var/www/html/proyecto

chmod -R 775 /var/www/html/proyecto/storage
chmod -R 775 /var/www/html/proyecto/bootstrap/cache
```

---

# 🔗 12. Storage Link

```
php artisan storage:link
```

---

# 🧪 13. Verificación

```
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

# 🧰 14. Comandos útiles

Reiniciar servicios:
```
systemctl restart nginx
systemctl restart php8.3-fpm
```

---

# ⚠️ Problemas comunes

## ❌ 403 en storage
- Permisos incorrectos
- Falta storage:link
- SELinux activo

## ❌ Vite manifest not found
```
npm run build
```

## ❌ laravel.log permission denied
```
chown -R www-data:www-data storage
```

---

# 🧠 Flujo recomendado de deploy

```
git pull
composer install --no-dev
npm install
npm run build
php artisan migrate --force
php artisan config:cache
```

---

# ✅ Resultado final

✔ Proyecto funcionando  
✔ SSL activo  
✔ Archivos accesibles  
✔ Permisos correctos  
✔ Deploy listo para producción  

---

💡 Recomendación: automatizar todo esto en un script de deploy.
