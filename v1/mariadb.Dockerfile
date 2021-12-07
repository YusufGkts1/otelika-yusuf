FROM mariadb:10

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

COPY ./data/mariadb/sql/setup.sql /setup.sql
COPY ./data/mariadb/sql/grant.sql /grant.sql
COPY ./data/mariadb/integration-entrypoint.sh /integration-entrypoint.sh

# encryption setup
ARG AWS_ACCESS_KEY_ID
ARG AWS_SECRET_ACCESS_KEY

COPY ./data/mariadb/aws-kms.conf /etc/systemd/system/mariadb.service.d/

# create the file where the AWS secrets will be hold
RUN touch /etc/systemd/system/mariadb.service.d/aws-kms.env
RUN chown root /etc/systemd/system/mariadb.service.d/aws-kms.env
RUN chmod 600 /etc/systemd/system/mariadb.service.d/aws-kms.env

# copy AWS secrets to aws-kms.env file
RUN echo AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID} >> /etc/systemd/system/mariadb.service.d/aws-kms.env
RUN echo AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY} >> /etc/systemd/system/mariadb.service.d/aws-kms.env

# end of encryption setup

RUN chmod 777 ./integration-entrypoint.sh

CMD ["mysqld"];

ENTRYPOINT [ "./integration-entrypoint.sh" ];