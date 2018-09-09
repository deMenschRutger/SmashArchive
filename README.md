# SmashArchive 
SmashArchive is an archive of results of tournaments for the Super Smash. Bros
video game series (although it could easily be modified to accommodate any type
of game where two parties compete against each other). Besides building an
exhaustive database of tournament results, it also aims to expose this data
using an API.

This project originated from the now defunct smashranking.eu project. A
prototype version of SmashArchive, using the old smashranking.eu database, can
be found at [smasharchive.eu](http://smasharchive.eu/). The code used for this
prototype is no longer in use, but can still be inspected in the history of
this repository (it is tagged as version 0.1.0).

## Contribute
SmashArchive is currently in an early state. The front-end in particular still
needs a lot of work before others can start adding tournament results using
the site (currently all tournaments are imported manually on the CLI). We are
currently looking for developers who are interested in contributing to the
development of the front-end. If you are interested, please contact the
project lead at rutger@rutgermensch.com or on
[Twitter](https://twitter.com/UttoNL).

### Git workflow
For developers contributing to the project, please note that we use the
[Gitflow](https://github.com/nvie/gitflow) workflow.

## Installing
### Back-end
The SmashArchive back-end is built on the Symfony 4 framework. To install 
the project, first check out the repository (use the develop branch initially),
then navigate to the Symfony root directory (`/source/symfony`) in your
terminal and run:

```composer install```

If you don't have composer installed, you can get it
[here](https://getcomposer.org/doc/00-intro.md). After completing the
installation you can access all Symfony commands by running:

```bin/console```

#### Environment configuration
Environment specific configuration can be configured in the automatically
generated `.env` file in the Symfony root directory (they will be overwritten
by environment variables on other environments). On your local machine you can
use the default values for some of these variables, but you will need to
set some of them yourself if you want specific functionality to work.

##### Authentication
Currently the only authentication mechanism available is Facebook. To make it
work locally you will have to configure your own app ID and secret, which you
can create [here](https://developers.facebook.com/). After this you have to
enable JWT generation by creating your own keys in the directory
`/source/symfony/config/jwt`, using these 
[instructions](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#getting-started).

##### Challonge
You need to set your own Challonge API key if you want to be able to import
tournaments from Challonge.

#### Database
SmashArchive requires a MySQL database to run. You can configure the settings
for your local MySQL instance in the `.env` file. After this you will have to
execute the migrations to create the database schema, using the following
command:

```bin/console doctrine:migrations:migrate```

#### Importing a tournament
After executing the database migrations the database will still be empty. You
can import a tournament using this command:

```bin/console app:tournament:import```

You will be asked a few of questions about the details of the tournament. For
example:

1. *Which provider would like to use?* => smash.gg
2. *Please enter the slug of this tournaments* => spice-14
3. *Please select the IDs of the events you would like to import* => 0,1

This will add the tournament *Spice 14* to the database. If the tournament
already exists in the database, the importer will synchronize it with its
provider (smash.gg in the example).

#### Starting the server
You can start a local server for development purposes using this command:

```bin/console server:start```

Alternatively, you can use `server:run` if you want to see the server's output
in the terminal. After running one of these commands, you will be able to
access the server by navigating to `http://localhost:8000` in your browser
(assuming you did not change the default port). The API can be accessed at
`http://localhost:8000/api` (documentation for the API will be added soon).

### Front-end
The front-end of SmashArchive currently uses Vue.js, but is in such an early
state that this could still change. For now, you can build the front-end by
navigating to the directory `/source/front-end` in your terminal and running:

```yarn install```

Followed by:

```yarn build```

If you don't use Yarn yet, you can get it [here](https://yarnpkg.com/).

## Roadmap
* Add additional API endpoints to retrieve information about tournaments and
  players.
* Improve the way tournaments and players are shown in the front-end.
* Add API endpoints to add and modify tournaments and players.
* Create an admin panel for admins to manage the data in the database.

## License
SmashArchive is licensed under
[the MIT license](https://opensource.org/licenses/MIT).
