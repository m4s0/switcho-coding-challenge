# Switcho Coding Challenge

### Table of Contents

- [Overview](#overview)
- [Application](#application)
    - [Environment Setup](#environment-setup)
    - [Running the Application](#running-the-application)
- [Postman Usage](#postman-usage)

---

### Overview

Our goal is to build the backend of a Tic-Tac-Toe game:
https://en.wikipedia.org/wiki/Tic-tac-toe

The backend will be used by a frontend built by a separate team, but they have
provided us with a set of product level requirements that we must meet, exposed as
an API. The requirements are as follows:
1. Need an endpoint to call to start a new game. The response should give me
   some kind of ID for me to use in other endpoints calls to tell the backend what
   game I am referring to.
2. Need an endpoint to call to play a move in the game. The endpoint should take
   as inputs the Game ID (from the first endpoint), a player number (either 1 or 2),
   and the position of the move being played. The response should include a data
   structure with the representation of the full board so that the UI can update
   itself with the latest data on the server. The response should also include a flag
   indicating whether someone has won the game or not and who that winner is if
   so.
3. The endpoint that handles moves being played should perform some basic
   error handling to ensure the move is valid, and that it is the right players turn
   (ie. a player cannot play two moves in a row, or place a piece on top of another
   player’s piece)

You do not need to worry about the UI, and can simply exercise your API using cURL
commands or other API testing tools you prefer. Please provide a test case example
(such as test cases or a list of cURL commands) of a fully played out game with your
solution. You can use any language you would like for your server, with preference for
PHP if it's one of your main languages, and you can pick any datastore you would like
to store the game state.

---

### Application

Application runs with **PHP 8.2** and **Postgres 17.4**.

#### Environment Setup

1. Copy the example environment file:

```bash
cp .env.dist .env
```
2. Update `.env` with your configuration (DB credentials, ports, etc.).

#### Running the Application

Build containers:

```bash
docker-compose build
```
Run containers:

```bash
docker-compose up -d
```

Stop containers:

```bash
docker-compose down
```

Enter into php container

```bash
make bash
```

Install dependencies

```bash
make install
```

Run migrations

```bash
make run-migrations
```

Run all Tests

```bash
make tests
```

Run Unit Tests

```bash
make unit
``` 

Run Integration Tests

```bash
make integration
``` 

Run Coding Standards checks

```bash
make cs
``` 

Run Static Code Analysis

```bash
make stan
``` 

### Postman Usage

To test the Tic-Tac-Toe API, you can use Postman.

1. Import the Environment `postman/TicTacToe.postman_environment.json` into Postman.
2. Import the Collection `postman/Tic-Tac-Toe.postman_collection.json` into Postman.

You should now be able to run the requests in the collection against your running application.