#!/bin/bash

# This will install mary ui kit and restores mary ui/daisy to default configuration in linux

docker compose run --rm composer require livewire/livewire livewire/volt

docker compose run --rm artisan volt:install

docker compose run --rm npm install --save-dev tailwindcss daisyui@latest postcss autoprefixer

rm ../tailwind.config.js

rm ../postcss.config.js

docker compose run --rm artisan mary:docker-install

docker compose run --rm npm run build
