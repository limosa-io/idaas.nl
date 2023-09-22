FROM nginx

COPY nginx/default.conf /etc/nginx/conf.d/
COPY nginx/fastcgi.conf /etc/nginx/conf.d/

COPY nginx/_wildcard.notidaas.nl.pem /etc/nginx/
COPY nginx/_wildcard.notidaas.nl-key.pem /etc/nginx/
