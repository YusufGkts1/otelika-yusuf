FROM mysql:latest

RUN apt-get update

# TOOLS
RUN apt-get install -y nano \
        curl \
        git \
        net-tools \
        procps

RUN echo "init_command=\"SET @@global.time_zone = '+03:00';\"" >> /etc/mysql/conf.d/mysql.cnf

RUN echo "[mysqld]" >> /etc/mysql/conf.d/mysql.cnf
RUN echo "max_connections = 512" >> /etc/mysql/conf.d/mysql.cnf

COPY ./data/mysql/sql/setup.sql /setup.sql
COPY ./data/mysql/sql/grant.sql /grant.sql
COPY ./data/mysql/integration-entrypoint.sh /integration-entrypoint.sh

RUN chmod 777 ./integration-entrypoint.sh

CMD ["mysqld"];

ENTRYPOINT [ "./integration-entrypoint.sh" ];