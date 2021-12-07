FROM mariadb:10

RUN apt-get update

# TOOLS
RUN apt-get install -y nano \
        curl \
        git \
        net-tools

COPY ./data/mariadb/plugin/aws_key_management.so /usr/lib/mysql/plugin/
COPY ./data/mariadb/conf/* /etc/mysql/conf.d/

# encryption setup
# ARG AWS_ACCESS_KEY_ID
# ARG AWS_SECRET_ACCESS_KEY

# COPY ./data/mariadb/aws-kms.conf /etc/systemd/system/mariadb.service.d/

# # create the file where the AWS secrets will be hold
# RUN touch /etc/systemd/system/mariadb.service.d/aws-kms.env
# RUN chown root /etc/systemd/system/mariadb.service.d/aws-kms.env
# RUN chmod 777 /etc/systemd/system/mariadb.service.d/aws-kms.env

# # copy AWS secrets to aws-kms.env file
# RUN echo AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID} >> /etc/systemd/system/mariadb.service.d/aws-kms.env
# RUN echo AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY} >> /etc/systemd/system/mariadb.service.d/aws-kms.env

# end of encryption setup