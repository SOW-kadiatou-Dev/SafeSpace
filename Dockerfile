# ─────────────────────────────────────────
# SafeSpace — PHP vanilla + SQLite
# For Render deployment (ephemeral SQLite)
# ─────────────────────────────────────────
FROM php:8.2-cli-alpine

# Install SQLite extension + PDO
RUN apk add --no-cache sqlite-libs sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite

WORKDIR /app

# Copy all project files
COPY . .

# Ensure the SQLite file is writable (ephemeral, reset on redeploy)
RUN touch /app/database.sqlite /app/SafeSpace.sqlite \
    && chmod 666 /app/database.sqlite /app/SafeSpace.sqlite

RUN php public/setup.php

EXPOSE 8000

# Serve public/ as document root via PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
