#!/usr/bin/env bash
set -euo pipefail

# =========================================================
# Laravel VPS Deploy Assistant
# Interactive deploy script for Nginx + PHP-FPM + MySQL + Certbot
# =========================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

say() { echo -e "${BLUE}==>${NC} $1"; }
ok() { echo -e "${GREEN}✔${NC} $1"; }
warn() { echo -e "${YELLOW}!${NC} $1"; }
err() { echo -e "${RED}✘${NC} $1"; }

require_root() {
  if [[ "${EUID}" -ne 0 ]]; then
    err "Ejecutá este script como root."
    exit 1
  fi
}

ask() {
  local prompt="$1"
  local varname="$2"
  local default="${3:-}"
  local value
  if [[ -n "$default" ]]; then
    read -r -p "$prompt [$default]: " value
    value="${value:-$default}"
  else
    read -r -p "$prompt: " value
  fi
  printf -v "$varname" '%s' "$value"
}

ask_secret() {
  local prompt="$1"
  local varname="$2"
  local value
  read -r -s -p "$prompt: " value
  echo
  printf -v "$varname" '%s' "$value"
}

ask_yes_no() {
  local prompt="$1"
  local varname="$2"
  local default="${3:-y}"
  local value
  local hint="[y/N]"
  [[ "$default" == "y" ]] && hint="[Y/n]"
  read -r -p "$prompt $hint: " value
  value="${value:-$default}"
  case "$value" in
    y|Y|yes|YES) printf -v "$varname" '%s' "y" ;;
    *) printf -v "$varname" '%s' "n" ;;
  esac
}

detect_php_socket() {
  local sock=""
  if compgen -G "/run/php/php*-fpm.sock" > /dev/null; then
    sock="$(ls /run/php/php*-fpm.sock | head -n1)"
  elif [[ -S /run/php-fpm/www.sock ]]; then
    sock="/run/php-fpm/www.sock"
  elif [[ -S /var/run/php-fpm/www.sock ]]; then
    sock="/var/run/php-fpm/www.sock"
  fi
  echo "$sock"
}

detect_php_fpm_service() {
  local svc=""
  svc="$(systemctl list-units --type=service --all | awk '/php.*fpm.*service/ {print $1; exit}')"
  echo "$svc"
}

detect_web_user() {
  if id www-data >/dev/null 2>&1; then
    echo "www-data"
  elif id nginx >/dev/null 2>&1; then
    echo "nginx"
  elif id apache >/dev/null 2>&1; then
    echo "apache"
  else
    echo "www-data"
  fi
}

nginx_test_reload() {
  nginx -t
  systemctl reload nginx
}

mysql_exec_root() {
  local sql="$1"
  if [[ "$MYSQL_ROOT_MODE" == "socket" ]]; then
    mysql -e "$sql"
  else
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "$sql"
  fi
}

set_mysql_bind_all() {
  local files=(
    /etc/mysql/mysql.conf.d/mysqld.cnf
    /etc/my.cnf
    /etc/my.cnf.d/mariadb-server.cnf
    /etc/my.cnf.d/server.cnf
  )
  local changed="n"
  for f in "${files[@]}"; do
    if [[ -f "$f" ]]; then
      if grep -qE '^\s*bind-address\s*=' "$f"; then
        sed -i 's/^\s*bind-address\s*=.*/bind-address = 0.0.0.0/' "$f"
        changed="y"
      else
        printf '\n[mysqld]\nbind-address = 0.0.0.0\n' >> "$f"
        changed="y"
      fi
      break
    fi
  done
  if [[ "$changed" == "y" ]]; then
    if systemctl is-active --quiet mysqld; then
      systemctl restart mysqld
    elif systemctl is-active --quiet mysql; then
      systemctl restart mysql
    fi
  fi
}

open_firewall_port() {
  local port="$1"
  if command -v firewall-cmd >/dev/null 2>&1 && systemctl is-active --quiet firewalld; then
    firewall-cmd --permanent --add-port="${port}/tcp" >/dev/null || true
    firewall-cmd --reload >/dev/null || true
    ok "Puerto ${port}/tcp abierto en firewalld"
  elif command -v ufw >/dev/null 2>&1; then
    ufw allow "${port}/tcp" >/dev/null || true
    ok "Puerto ${port}/tcp abierto en ufw"
  else
    warn "No detecté firewalld/ufw. Si usás firewall externo, abrí el puerto ${port} manualmente."
  fi
}

write_nginx_http_conf() {
  cat > "$NGINX_CONF_PATH" <<EOF
server {
    listen 80;
    server_name $DOMAIN${WWW_DOMAIN:+ $WWW_DOMAIN};

    root $PROJECT_PATH/public;
    index index.php index.html;

    client_max_body_size $CLIENT_MAX_BODY_SIZE;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$PHP_SOCKET;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
}

summary() {
  cat <<EOF

================ RESUMEN ================
Dominio:              $DOMAIN ${WWW_DOMAIN:+(+ $WWW_DOMAIN)}
Ruta proyecto:        $PROJECT_PATH
Repo:                 $REPO_URL
DB:                   $DB_NAME
DB usuario local:     $DB_USER_LOCAL
DB usuario remoto:    ${DB_USER_REMOTE:-no}
PHP socket:           $PHP_SOCKET
PHP-FPM service:      $PHP_FPM_SERVICE
Web user:             $WEB_USER
Nginx conf:           $NGINX_CONF_PATH
APP_URL:              $APP_URL
========================================

EOF
}

require_root

say "Asistente interactivo para deploy Laravel"
warn "Este script puede BORRAR la carpeta del proyecto en el VPS y sobrescribir configuración Nginx."
ask_yes_no "¿Querés continuar?" CONTINUE "n"
[[ "$CONTINUE" == "y" ]] || exit 0

DEFAULT_PROJECT_PATH="/var/www/html/procurement"
DEFAULT_APP_NAME="Laravel App"
DEFAULT_CLIENT_MAX_BODY_SIZE="20M"
DEFAULT_PHP_SOCKET="$(detect_php_socket)"
DEFAULT_PHP_FPM_SERVICE="$(detect_php_fpm_service)"
DEFAULT_WEB_USER="$(detect_web_user)"

ask "Dominio principal" DOMAIN
ask_yes_no "¿Querés agregar dominio www?" WANT_WWW "n"
WWW_DOMAIN=""
if [[ "$WANT_WWW" == "y" ]]; then
  ask "Dominio www" WWW_DOMAIN "www.${DOMAIN}"
fi

ask "Ruta absoluta del proyecto en el VPS" PROJECT_PATH "$DEFAULT_PROJECT_PATH"
ask "Nombre de la app (APP_NAME)" APP_NAME "$DEFAULT_APP_NAME"
ask "APP_URL" APP_URL "https://${DOMAIN}"
ask "URL del repositorio Git (HTTPS o SSH)" REPO_URL
ask_yes_no "¿El repo es privado y querés clonar por HTTPS con token?" REPO_PRIVATE_HTTPS "y"

GIT_CLONE_URL="$REPO_URL"
if [[ "$REPO_PRIVATE_HTTPS" == "y" ]]; then
  ask "Usuario GitHub" GITHUB_USER
  ask_secret "Token GitHub" GITHUB_TOKEN
  if [[ "$REPO_URL" =~ ^https:// ]]; then
    GIT_CLONE_URL="$(echo "$REPO_URL" | sed "s#https://#https://${GITHUB_USER}:${GITHUB_TOKEN}@#")"
  else
    warn "La URL no es HTTPS. Se usará tal cual."
  fi
fi

ask_yes_no "¿Borrar completamente la carpeta actual del proyecto si existe?" WIPE_PROJECT "n"
ask_yes_no "¿Crear base de datos y usuarios MySQL?" SETUP_DB "y"

MYSQL_ROOT_MODE="socket"
if [[ "$SETUP_DB" == "y" ]]; then
  ask_yes_no "¿Entrás a MySQL con sudo mysql (socket)?" SOCKET_MODE "y"
  if [[ "$SOCKET_MODE" == "n" ]]; then
    MYSQL_ROOT_MODE="password"
    ask_secret "Password de root MySQL" MYSQL_ROOT_PASSWORD
  fi

  ask "Nombre de la base de datos" DB_NAME "procurement_db"
  ask "Usuario MySQL local (Laravel en el VPS)" DB_USER_LOCAL "procurement_user"
  ask_secret "Password usuario MySQL local" DB_PASS_LOCAL
  ask_yes_no "¿Crear también usuario remoto desde cualquier host (%)?" CREATE_REMOTE_DB_USER "n"
  if [[ "$CREATE_REMOTE_DB_USER" == "y" ]]; then
    ask "Usuario MySQL remoto" DB_USER_REMOTE "${DB_USER_LOCAL}_remote"
    ask_secret "Password usuario MySQL remoto" DB_PASS_REMOTE
  fi
fi

ask_yes_no "¿Ejecutar composer install?" RUN_COMPOSER "y"
ask_yes_no "¿Ejecutar npm install?" RUN_NPM_INSTALL "y"
ask_yes_no "¿Ejecutar npm run build?" RUN_NPM_BUILD "y"
ask_yes_no "¿Ejecutar php artisan migrate --force?" RUN_MIGRATIONS "y"
ask_yes_no "¿Crear symlink storage con artisan?" RUN_STORAGE_LINK "y"
ask_yes_no "¿Configurar Nginx?" SETUP_NGINX "y"
ask "Tamaño máximo de subida para Nginx (client_max_body_size)" CLIENT_MAX_BODY_SIZE "$DEFAULT_CLIENT_MAX_BODY_SIZE"
ask_yes_no "¿Emitir/instalar SSL con Certbot?" RUN_CERTBOT "y"

PHP_SOCKET="$DEFAULT_PHP_SOCKET"
PHP_FPM_SERVICE="$DEFAULT_PHP_FPM_SERVICE"
WEB_USER="$DEFAULT_WEB_USER"

ask "Socket PHP-FPM" PHP_SOCKET "${PHP_SOCKET:-/run/php/php8.3-fpm.sock}"
ask "Servicio PHP-FPM" PHP_FPM_SERVICE "${PHP_FPM_SERVICE:-php8.3-fpm.service}"

NGINX_CONF_PATH="/etc/nginx/sites-available/${DOMAIN}.conf"

summary
ask_yes_no "¿Confirmás que querés ejecutar el deploy con esta configuración?" GO "n"
[[ "$GO" == "y" ]] || exit 0

say "Verificando paquetes básicos"
command -v git >/dev/null 2>&1 || { err "git no está instalado"; exit 1; }
command -v nginx >/dev/null 2>&1 || { err "nginx no está instalado"; exit 1; }
command -v php >/dev/null 2>&1 || { err "php no está instalado"; exit 1; }

if [[ "$WIPE_PROJECT" == "y" && -d "$PROJECT_PATH" ]]; then
  warn "Borrando proyecto existente: $PROJECT_PATH"
  rm -rf "$PROJECT_PATH"
  ok "Proyecto viejo eliminado"
fi

mkdir -p "$(dirname "$PROJECT_PATH")"

if [[ ! -d "$PROJECT_PATH/.git" ]]; then
  say "Clonando proyecto"
  git clone "$GIT_CLONE_URL" "$PROJECT_PATH"
  ok "Repo clonado"
else
  warn "Ya existe un repositorio git en $PROJECT_PATH. Se omite clonación."
fi

cd "$PROJECT_PATH"

if [[ "$SETUP_DB" == "y" ]]; then
  say "Creando base de datos y usuarios MySQL"
  mysql_exec_root "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  mysql_exec_root "CREATE USER IF NOT EXISTS '${DB_USER_LOCAL}'@'localhost' IDENTIFIED BY '${DB_PASS_LOCAL}';"
  mysql_exec_root "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER_LOCAL}'@'localhost';"
  if [[ "${CREATE_REMOTE_DB_USER:-n}" == "y" ]]; then
    mysql_exec_root "CREATE USER IF NOT EXISTS '${DB_USER_REMOTE}'@'%' IDENTIFIED BY '${DB_PASS_REMOTE}';"
    mysql_exec_root "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER_REMOTE}'@'%';"
    mysql_exec_root "FLUSH PRIVILEGES;"
    set_mysql_bind_all
    open_firewall_port 3306
  else
    mysql_exec_root "FLUSH PRIVILEGES;"
  fi
  ok "Base de datos y usuarios configurados"
fi

if [[ ! -f .env && -f .env.example ]]; then
  cp .env.example .env
  ok ".env creado desde .env.example"
fi

if [[ -f .env ]]; then
  say "Actualizando variables principales de .env"
  sed -i "s#^APP_NAME=.*#APP_NAME=\"${APP_NAME//\"/\\\"}\"#g" .env || true
  if grep -q '^APP_ENV=' .env; then sed -i 's/^APP_ENV=.*/APP_ENV=production/' .env; else echo 'APP_ENV=production' >> .env; fi
  if grep -q '^APP_DEBUG=' .env; then sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env; else echo 'APP_DEBUG=false' >> .env; fi
  if grep -q '^APP_URL=' .env; then sed -i "s#^APP_URL=.*#APP_URL=${APP_URL}#g" .env; else echo "APP_URL=${APP_URL}" >> .env; fi

  if [[ "$SETUP_DB" == "y" ]]; then
    if grep -q '^DB_CONNECTION=' .env; then sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env; else echo 'DB_CONNECTION=mysql' >> .env; fi
    if grep -q '^DB_HOST=' .env; then sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env; else echo 'DB_HOST=127.0.0.1' >> .env; fi
    if grep -q '^DB_PORT=' .env; then sed -i 's/^DB_PORT=.*/DB_PORT=3306/' .env; else echo 'DB_PORT=3306' >> .env; fi
    if grep -q '^DB_DATABASE=' .env; then sed -i "s#^DB_DATABASE=.*#DB_DATABASE=${DB_NAME}#g" .env; else echo "DB_DATABASE=${DB_NAME}" >> .env; fi
    if grep -q '^DB_USERNAME=' .env; then sed -i "s#^DB_USERNAME=.*#DB_USERNAME=${DB_USER_LOCAL}#g" .env; else echo "DB_USERNAME=${DB_USER_LOCAL}" >> .env; fi
    if grep -q '^DB_PASSWORD=' .env; then sed -i "s#^DB_PASSWORD=.*#DB_PASSWORD=${DB_PASS_LOCAL}#g" .env; else echo "DB_PASSWORD=${DB_PASS_LOCAL}" >> .env; fi
  fi
else
  warn "No encontré .env ni .env.example. Vas a tener que crear .env manualmente."
fi

if [[ "$RUN_COMPOSER" == "y" ]]; then
  say "Instalando dependencias Composer"
  composer install --no-dev --optimize-autoloader
  ok "Composer finalizado"
fi

say "Preparando permisos mínimos antes de artisan"
mkdir -p storage/logs bootstrap/cache
chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache || true
find storage -type d -exec chmod 775 {} \; || true
find storage -type f -exec chmod 664 {} \; || true
chmod -R 775 bootstrap/cache || true

if [[ -f artisan ]]; then
  say "Generando APP_KEY"
  php artisan key:generate --force || warn "No pude generar APP_KEY automáticamente. Revisá .env."
  php artisan config:clear || true
  php artisan cache:clear || true
fi

if [[ "$RUN_NPM_INSTALL" == "y" ]]; then
  say "Instalando dependencias NPM"
  npm install
  ok "npm install finalizado"
fi

if [[ "$RUN_NPM_BUILD" == "y" ]]; then
  say "Ejecutando build frontend"
  npm run build
  ok "Build generado"
fi

if [[ -f artisan && "$RUN_MIGRATIONS" == "y" ]]; then
  say "Ejecutando migraciones"
  php artisan migrate --force
  ok "Migraciones ejecutadas"
fi

if [[ -f artisan && "$RUN_STORAGE_LINK" == "y" ]]; then
  say "Creando symlink storage"
  php artisan storage:link || warn "No pude crear storage:link. Puede que ya exista."
fi

say "Aplicando permisos finales"
chown -R "$WEB_USER":"$WEB_USER" "$PROJECT_PATH/storage" "$PROJECT_PATH/bootstrap/cache" || true
find "$PROJECT_PATH/storage" -type d -exec chmod 775 {} \; || true
find "$PROJECT_PATH/storage" -type f -exec chmod 664 {} \; || true
chmod -R 775 "$PROJECT_PATH/bootstrap/cache" || true

if command -v getenforce >/dev/null 2>&1; then
  SELINUX_MODE="$(getenforce || true)"
  if [[ "$SELINUX_MODE" == "Enforcing" ]]; then
    warn "SELinux está en Enforcing. Ajustando contexto para Laravel storage/public."
    chcon -R -t httpd_sys_rw_content_t "$PROJECT_PATH/storage" || true
    [[ -e "$PROJECT_PATH/public/storage" ]] && chcon -R -t httpd_sys_rw_content_t "$PROJECT_PATH/public/storage" || true
  fi
fi

if [[ "$SETUP_NGINX" == "y" ]]; then
  say "Configurando Nginx"
  write_nginx_http_conf
  ln -sf "$NGINX_CONF_PATH" "/etc/nginx/sites-enabled/${DOMAIN}.conf"
  nginx_test_reload
  ok "Nginx HTTP configurado"
fi

if [[ "$RUN_CERTBOT" == "y" ]]; then
  if ! command -v certbot >/dev/null 2>&1; then
    warn "Certbot no está instalado. Se omite SSL."
  else
    say "Intentando emitir/instalar SSL con Certbot"
    certbot --nginx -d "$DOMAIN" ${WWW_DOMAIN:+-d "$WWW_DOMAIN"} || warn "Certbot no pudo completar. Revisá DNS o configuración Nginx."
  fi
fi

say "Recargando PHP-FPM y Nginx"
systemctl restart "$PHP_FPM_SERVICE" || warn "No pude reiniciar $PHP_FPM_SERVICE"
systemctl reload nginx || true

cat <<EOF

✅ Deploy terminado.

Pasos manuales que este script NO puede completar por sí solo:
1. Subir archivos de uploads desde tu máquina local con rsync, por ejemplo:
   rsync -avzi --progress -e "ssh -p 5676" /ruta/local/storage/app/public/ root@TU_IP:$PROJECT_PATH/storage/app/public/

2. Luego corregir owner de uploads:
   chown -R $WEB_USER:$WEB_USER $PROJECT_PATH/storage
   find $PROJECT_PATH/storage -type d -exec chmod 775 {} \;
   find $PROJECT_PATH/storage -type f -exec chmod 664 {} \;

3. Verificar que el DNS del dominio apunte al VPS antes de emitir SSL.

Comandos útiles:
- Ver logs Laravel: tail -n 200 $PROJECT_PATH/storage/logs/laravel.log
- Ver logs Nginx: tail -n 200 /var/log/nginx/error.log
- Rebuild frontend: cd $PROJECT_PATH && npm run build
- Actualizar app: cd $PROJECT_PATH && git pull && composer install --no-dev --optimize-autoloader

EOF
