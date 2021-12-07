FROM redocly/redoc

COPY ./docs/* /usr/share/nginx/html/swagger/

ENV SPEC_URL=swagger/openapi.json