#!/bin/bash

./entrypoint.sh "$@" &

ps cax | grep postgres > /dev/null

while [ ! $? -eq 0 ]; do
        echo "waiting for postgres daemon to start"
        sleep 5
        ps cax | grep postgres > /dev/null
done

sleep 5

echo "postgres daemon has started. Running integration script"

response=$(bash -c "psql -U appuser -p${MYSQL_ROOT_PASSWORD} < ./setup.sql 2>&1")

while [[ $response == *"ERROR 2002"* ]] || [[ $response == *"ERROR 1045"* ]] || [[ $response == *"ERROR 2013"* ]]; do
        echo "waiting for postgresql socket to open"
        response=$(bash -c "psql -U appuser -p${MYSQL_ROOT_PASSWORD} < ./setup.sql 2>&1")
        sleep 5
done;

echo "integration is complete"

while true; do
        sleep 10
done