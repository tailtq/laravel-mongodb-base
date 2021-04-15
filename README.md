# Laravel Mongodb Application

## Setup steps

### Prerequisites

- Docker
- Docker-compose
- NodeJS
- Npm

1. Clone source from repository url

2. Run docker-compose: `docker-comopse up -d`

3. Install Laravel packages:
```shell
docker-compose exec api bash
composer update
exit
```

4. Create `.env` from `.env.example` and make sure all the variables are correct

5. Build CSS and JS: `npm run production`
