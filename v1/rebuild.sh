docker rm -f otelika-api || true
docker rm -f otelika-db || true
docker rm -f otelika-db-polling || true
docker rm -f otelika-phpmyadmin || true
docker rm -f otelika-redis || true
docker rm -f otelika-redocly || true
docker rm -f otelika-postgis || true
docker rm -f otelika-pgadmin || true
docker rm -f otelika-invoker-event || true
docker rm -f otelika-invoker-polling-queue || true

docker-compose -p otelika up --build -d
