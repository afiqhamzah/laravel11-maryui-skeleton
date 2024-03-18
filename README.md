## Requirement
 - Install [docker](https://www.docker.com/get-started/)

## Steps to set up development environment

 - In terminal navigate to your development folder

 - Run `git clone https://github.com/afiqhamzah/docker-compose-laravel.git AROSv3`

 - Navigate into the newly create AROSv3

 - Run `rm -rf .git` folder or delete the the folder .git using the file explorer

 - We need the src folder to be empty before we can clone the project into the src folder.
   Run `rm -rf ./src/*`

 - Run `git clone https://github.com/afiqhamzah/laravel11-maryui-skeleton src`

 - Next its time to start up the docker containers using docker compose.
   Run `docker compose up --build -d`

 - After all the docker containers has started, move into the src folder where the source code located

 - To copy exampled .env file.
   Run `cp .env.docker.example .env`

 - Adjust the database host accordingly by refering to the mysql docker container

 - Setup the laravel project using docker containers
   - `docker compose run --rm composer install`
   - `docker compose run --rm artisan migrate`
   - `docker compose run --rm npm install`
   - `docker compose run --rm npm run build`

- Finally using your web browser go to http://localhost:8080


## Aliasing the docker commands

As you can see previously, the way to run artisan command is longer than usual command

Usual way = php artisan migrate<br>
Docker way = docker compose run --rm artisan migrate

We can make it short by using alias :

 - [Powershell Alias](https://stackoverflow.com/a/24914795)
 - [Linux Alias](https://askubuntu.com/a/17538)

For instance in my linux .zshrc config file I do :

```bash
alias dcd="docker compose down --remove-orphans"
alias dcu="docker compose up --build"
alias dcr="docker compose run --rm"
```

So, if I want to run any artisan command I just need to run :<br>
`dcr artisan migrate`
