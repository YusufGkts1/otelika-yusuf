FROM volbrene/redoc

RUN mkdir -p /var/www/html/static/swagger-files/docs

COPY ./docs/openapi_guest.json /var/www/html/static/swagger-files/docs



ENV URLS="[{url: '/static/swagger-files/docs/openapi_guest.json', name: 'Guest'}]"