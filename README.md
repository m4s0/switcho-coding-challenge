# Application

Application runs with PHP 8.2 and Postgres:17.4

## Environment Setup

1. Copy the example environment file:

```bash
cp .env.dist .env
```
2. Update `.env` with your configuration (DB credentials, ports, etc.).

## Running the Application

### Docker Setup

Build containers:

```bash
docker-compose build
```
Run containers:

```bash
docker-compose up -d
```

To stop:

```bash
docker-compose down
```

Enter into php container

```bash
make bash
```

install dependencies

```bash
make install
```

run all tests

```bash
make test
```

run Coding Standards checks

```bash
make cs
``` 
