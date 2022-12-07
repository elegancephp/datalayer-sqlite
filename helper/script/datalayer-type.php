<?php

use Elegance\Connection\Sqlite;
use Elegance\Datalayer;

Datalayer::registerType('sqlite', Sqlite::class);