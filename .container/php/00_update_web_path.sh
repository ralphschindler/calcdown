#!/usr/bin/env bash

# move the project root down to /app/demo/public
mv /etc/nginx/sites-available/default /etc/nginx/sites-available/default.original
sed 's#/app/public#/app/demo/public#' /etc/nginx/sites-available/default.original > /etc/nginx/sites-available/default

echo -e "/app/src\n/app/demo/app\n/app/demo/bootstrap\n/app/demo/config\n/app/demo/resources\n/app/demo/routes\n/app/demo/storage" >> /etc/php/opcache-blacklist
