FROM php:8.1-cli
WORKDIR /app
COPY public/ /app/public
CMD ["php", "-S", "0.0.0.0:10000", "-t", "/app/public"]
