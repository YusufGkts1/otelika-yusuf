docker rm -f otelika-api-yusuf || true
docker rm -f otelika-db-yusuf || true
docker rm -f otelika-db-polling-yusuf || true
docker rm -f otelika-phpmyadmin-yusuf || true
docker rm -f otelika-redis-yusuf || true
docker rm -f otelika-redocly-yusuf || true
docker rm -f otelika-postgis-yusuf || true
docker rm -f otelika-pgadmin-yusuf || true
docker rm -f otelika-invoker-event-yusuf || true
docker rm -f otelika-invoker-polling-queue-yusuf || true

docker-compose -p otelika-yusuf up --build -d
