
#!/bin/sh

# Entry point deployment: siapkan environment, migrasi database, lalu jalankan server.
# Port di-resolve oleh shell di sini sehingga aman dipanggil platform yang tidak
# mengekspansi sintaks ${PORT:-8080} pada start command.
cd "$(dirname "$0")" || exit 1

if [ ! -f .env ]; then
    cp .env.example .env
    echo "[start] .env dibuat dari .env.example"
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
fi

db_connection="${DB_CONNECTION:-$(grep '^DB_CONNECTION=' .env | cut -d= -f2- | tr -d '\r')}"
sqlite_path="${DB_DATABASE:-database/database.sqlite}"

fresh_database=0
if [ "$db_connection" = "sqlite" ] && [ ! -f "$sqlite_path" ]; then
    if touch "$sqlite_path"; then
        fresh_database=1
    fi
fi

if ! php artisan migrate --force --no-interaction; then
    echo "[start] migrate gagal, server tetap dijalankan"
fi

if [ "$fresh_database" = "1" ]; then
    if ! php artisan db:seed --force --no-interaction; then
        echo "[start] seed gagal"
    fi
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
