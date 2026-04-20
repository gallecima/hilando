🚀 Deploy Completo Laravel en VPS

Laravel + GitHub + MySQL + Nginx + SSL + rsync

Guía completa paso a paso para subir un proyecto Laravel desde Mac a un VPS.

⸻

📌 Datos del servidor
	•	IP: 138.219.42.49
	•	Puerto SSH: 5676
	•	Dominio: bilingualtreasure.com
	•	Ruta del proyecto: /var/www/html/treasure

⸻

1️⃣ Resetear Git y crear repositorio nuevo

En tu Mac:

cd /Applications/XAMPP/xamppfiles/htdocs/bilingual-tresure
rm -rf .git
git init

Verificar que .gitignore contenga:

.env
/vendor
/node_modules
/storage/logs
/storage/framework

Crear primer commit:

git add .
git commit -m "Initial clean project"


⸻

2️⃣ Crear repositorio en GitHub y subir proyecto
	1.	Crear nuevo repositorio en GitHub (privado recomendado)
	2.	No agregar README

Conectar repositorio:

git remote add origin https://github.com/gallecima/bilingual-treasure.git
git branch -M main
git push -u origin main


⸻

3️⃣ Crear Token (PAT) en GitHub
	1.	GitHub → Settings
	2.	Developer settings
	3.	Personal access tokens
	4.	Generate new token (classic)
	5.	Scope: repo
	6.	Copiar token

⸻

4️⃣ Clonar repositorio en VPS

Conectar:

ssh -p 5676 root@138.219.42.49

Eliminar demo si existe:

sudo rm -rf /var/www/html/treasure

Clonar repo:

sudo git clone https://github.com/gallecima/bilingual-treasure.git /var/www/html/treasure

Usuario: gallecima
Password: pegar TOKEN

Instalar dependencias:

cd /var/www/html/treasure
composer install --no-dev --optimize-autoloader


⸻

5️⃣ Configurar MySQL

Entrar a MySQL:

sudo mysql

Crear base:

CREATE DATABASE treasure_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

Crear usuario local:

CREATE USER 'treasure_user'@'localhost' IDENTIFIED BY 'PASSWORD_SEGURA';
GRANT ALL PRIVILEGES ON treasure_db.* TO 'treasure_user'@'localhost';
FLUSH PRIVILEGES;

Crear usuario externo (opcional):

CREATE USER 'treasure_ext'@'%' IDENTIFIED BY 'PASSWORD_EXTERNA';
GRANT ALL PRIVILEGES ON treasure_db.* TO 'treasure_ext'@'%';
FLUSH PRIVILEGES;
EXIT;

Reiniciar MySQL si fue necesario:

sudo systemctl restart mysql


⸻

6️⃣ Crear archivo .env y generar APP_KEY

cd /var/www/html/treasure
cp .env.example .env
nano .env

Contenido mínimo:

APP_NAME="Bilingual Treasure"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bilingualtreasure.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=treasure_db
DB_USERNAME=treasure_user
DB_PASSWORD=PASSWORD_SEGURA

Limpiar caché:

php artisan config:clear
php artisan cache:clear

Generar clave:

php artisan key:generate

Migraciones:

php artisan migrate --force

Crear symlink storage:

php artisan storage:link

Permisos:

sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache


⸻

7️⃣ Crear sitio en Nginx

Ver versión PHP:

ls /run/php/

Crear archivo:

sudo nano /etc/nginx/sites-available/bilingualtreasure.com.conf

Contenido:

server {
    listen 80;
    server_name bilingualtreasure.com www.bilingualtreasure.com;

    root /var/www/html/treasure/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}

(Ajustar versión php si es diferente)

Habilitar:

sudo ln -sf /etc/nginx/sites-available/bilingualtreasure.com.conf /etc/nginx/sites-enabled/

Validar y recargar:

sudo nginx -t
sudo systemctl reload nginx


⸻

8️⃣ Crear SSL con Let’s Encrypt

sudo certbot --nginx -d bilingualtreasure.com -d www.bilingualtreasure.com

Si no se instala automáticamente:

sudo certbot install --cert-name bilingualtreasure.com

Verificar:

sudo nginx -t
sudo systemctl reload nginx

Probar:

curl -I https://bilingualtreasure.com


⸻

9️⃣ Subir archivos storage/app/public con rsync

Desde tu Mac:

rsync -avzi --progress -e "ssh -p 5676" \
/Applications/XAMPP/xamppfiles/htdocs/bilingual-tresure/storage/app/public/ \
root@138.219.42.49:/var/www/html/treasure/storage/app/public/

Modo espejo exacto (borra diferencias):

rsync -avzi --delete --progress -e "ssh -p 5676" \
/Applications/XAMPP/xamppfiles/htdocs/bilingual-tresure/storage/app/public/ \
root@138.219.42.49:/var/www/html/treasure/storage/app/public/


⸻

🔄 Actualizaciones futuras

cd /var/www/html/treasure
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan view:cache
sudo systemctl reload nginx


⸻

✅ Checklist final
	•	Nginx OK → sudo nginx -t
	•	HTTPS activo
	•	Base de datos conectada
	•	APP_KEY generada
	•	Permisos correctos
	•	storage link creado
	•	Migraciones ejecutadas

⸻

🎯 Buenas prácticas
	•	No subir .env al repositorio.
	•	No versionar storage.
	•	Usar SSH keys en producción.
	•	Restringir acceso externo a MySQL por IP.
	•	Usar usuario deploy en vez de root (recomendado).

⸻



10.
cd /var/www/html/treasure
npm install
npm run build

🚀 Proyecto Laravel desplegado correctamente en producción.