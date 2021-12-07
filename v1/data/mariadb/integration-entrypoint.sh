#!/bin/bash

/usr/local/bin/docker-entrypoint.sh "$@" &

ps cax | grep mysqld > /dev/null

while [ ! $? -eq 0 ]; do
        echo "waiting for mysql daemon to start"
        sleep 5
        ps cax | grep mysqld > /dev/null
done

sleep 5

echo "mysql daemon has started. Running integration script"

response=$(bash -c "mysql -u root -p${MYSQL_ROOT_PASSWORD} < ./setup.sql 2>&1")

while [[ $response == *"ERROR 2002"* ]] || [[ $response == *"ERROR 1045"* ]] || [[ $response == *"ERROR 2013"* ]]; do
        echo "waiting for mysql socket to open"
        sleep 5
        response=$(bash -c "mysql -u root -p${MYSQL_ROOT_PASSWORD} < ./setup.sql 2>&1")
done;

response=$(bash -c "mysql -u root -p${MYSQL_ROOT_PASSWORD} < ./setup.sql 2>&1")

echo "integration is complete"

while true; do
        sleep 10
done