# SmashArchive
SmashArchive is an archive of results of tournaments for the Super Smash. Bros
video game series (although it could easily be modified to accommodate any type
of game where two parties compete against each other). Besides building an
exhaustive database of tournament results, it also aims to expose this data
using an API.

This project originated from the now defunct smashranking.eu project. A
prototype version of SmashArchive can still be inspected in the history of
this repository (it is tagged as version 0.1.0).

## Contribute
SmashArchive is currently in an early state. The front-end in particular still
needs a lot of work before others can start adding tournament results using
the site (currently all tournaments are imported manually on the CLI). We are
currently looking for developers who are interested in contributing to the
project.

### Git workflow
For developers contributing to the project, please note that we use the
[Gitflow](https://github.com/nvie/gitflow) workflow.

## Installing
### Back-end
The SmashArchive back-end is built on the Symfony 4 framework (it requires PHP 7.2). To install
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
You will also need to set the `FACEBOOK_APP_ID` and `FACEBOOK_APP_SECRET`
environment variables (for example in your `.env` file).

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

#### Generating standings for a tournament
After you have imported a tournament, standings will not be automatically
generated for it. To do so, execute this command:

```bin/console app:event:standings:generate -i {event_id}```

You can also execute the following command to generate standings for all
imported events:

```bin/console app:event:standings:generate --all 1```

Be aware that this may take a long time on a database containing many
tournaments.

#### Starting the server
You can start a local server for development purposes using this command:

```bin/console server:start```

Alternatively, you can use `server:run` if you want to see the server's output
in the terminal. After running one of these commands, you will be able to
access the server by navigating to `http://localhost:8000` in your browser
(assuming you did not change the default port). The API can be accessed at
`http://localhost:8000/api`. You can access the automatically generated
API documentation at `http://localhost:8000/api/doc`.

### Front-end
The front-end of SmashArchive currently uses Vue.js, but is in such an early
state that this could still change. For now, you can build the front-end
locally by navigating to the directory `/source/front-end` in your terminal
and running:

```yarn install```

Followed by:

```FB_APP_ID={your_facebook_app_id} yarn build:server```

This will run a local development server using Webpack. Setting the `FB_APP_ID`
to a Facebook app that is configured to run on `localhost` domains will allow
you to log in locally.

If you want to make a production build instead, simply run the following
command:

```yarn build```

This will output a build to the `/source/front-end/dist` directory. You will
have to manually move the `.js` file to `/source/symfony/public/dist/bundle.js`
to use the build together with a running PHP server. On non-local environments
this should be a part of a build step somehow.

If you don't use Yarn yet, you can get it [here](https://yarnpkg.com/).

## License
SmashArchive is licensed under
[the MIT license](https://opensource.org/licenses/MIT).
