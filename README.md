# Application

Application runs with PHP 8.2 and Postgres:17.4

#### Docker

Copy `docker/.env.dist` to `docker/.env` and customise it with your parameters

Build container

```
make build
```

Run container

```
make up
```

Stop container

```
make down
```

Remove database container

```
make rm-db
```

Display container logs

```
make logs
```

Enter into php container

```
make bash
```

#### Init Application

install dependencies

```
make install
```

#### Tests

run all tests

```
make test
```

run unit tests

```
make unit
``` 

#### Coding Standards

```
make cs
``` 

#### Static Code Analysis

```
make stan
``` 
