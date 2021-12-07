docker rm -f erp-api-yusuf || true
docker rm -f erp-db-yusuf || true
docker rm -f erp-db-polling-yusuf || true
docker rm -f erp-phpmyadmin-yusuf || true
docker rm -f erp-redis-yusuf || true
docker rm -f erp-redocly-yusuf || true
docker rm -f erp-postgis-yusuf || true
docker rm -f erp-pgadmin-yusuf || true
docker rm -f erp-invoker-event-yusuf || true
docker rm -f erp-invoker-polling-queue-yusuf || true

docker-compose -p erp-yusuf up --build -d
