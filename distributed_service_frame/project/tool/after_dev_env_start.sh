#!/bin/bash

ENV=development php /var/www/distributed_service_frame/public/cli.php migrate:install
ENV=development php /var/www/distributed_service_frame/public/cli.php migrate
