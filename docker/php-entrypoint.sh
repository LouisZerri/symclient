#!/bin/sh
set -e

# Attendre la base de données
if [ -n "$DATABASE_URL" ]; then
    echo "⏳ Attente de la base de données..."
    until php bin/console dbal:run-sql "SELECT 1" >/dev/null 2>&1; do
        sleep 2
    done
    echo "✅ Base de données prête."
fi

# Générer les clés JWT si absentes (montées via volume persistant)
if [ ! -f config/jwt/private.pem ]; then
    echo "🔑 Génération des clés JWT..."
    php bin/console lexik:jwt:generate-keypair --no-interaction
fi

# Préchauffer le cache et appliquer les migrations
php bin/console cache:clear --no-interaction
php bin/console cache:warmup --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

chown -R www-data:www-data var config/jwt

exec "$@"
