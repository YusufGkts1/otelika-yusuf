FROM volbrene/redoc

RUN mkdir -p /var/www/html/static/swagger-files/docs

COPY ./docs/openapi_gis.json /var/www/html/static/swagger-files/docs
COPY ./docs/openapi_procedure_management.json /var/www/html/static/swagger-files/docs
COPY ./docs/openapi_task_management.json /var/www/html/static/swagger-files/docs
COPY ./docs/openapi_polling.json /var/www/html/static/swagger-files/docs
COPY ./docs/openapi_sdm.json /var/www/html/static/swagger-files/docs


ENV URLS="[{url: '/static/swagger-files/docs/openapi_task_management.json', name: 'TaskManagement'}, {url: '/static/swagger-files/docs/openapi_procedure_management.json', name: 'ProcedureManagement'}, {url: '/static/swagger-files/docs/openapi_gis.json', name: 'GIS'}, {url: '/static/swagger-files/docs/openapi_polling.json', name: 'Polling'},{url: '/static/swagger-files/docs/openapi_sdm.json', name: 'Sdm'}]"