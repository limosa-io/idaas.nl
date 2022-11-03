FROM nginx

COPY nginx/default.conf /etc/nginx/conf.d/
COPY nginx/fastcgi.conf /etc/nginx/conf.d/

