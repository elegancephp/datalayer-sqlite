<?php

namespace Elegance\Connection;

use Elegance\Connection\Sqlite\SqliteConfigTrait;
use Elegance\Connection\Sqlite\SqliteExecuteQueryTrait;
use Elegance\Connection\Sqlite\SqliteExecuteSchemeQueryTrait;
use Elegance\Connection\Sqlite\SqliteMapTrait;
use Elegance\Datalayer\Connection;
use Elegance\File;

class Sqlite extends Connection
{
    use SqliteMapTrait;
    use SqliteConfigTrait;
    use SqliteExecuteQueryTrait;
    use SqliteExecuteSchemeQueryTrait;

    /** Inicializa a conexão */
    protected function load()
    {
        $this->data['file'] =
            $this->data['file']
            ?? env(strtoupper("DB_{$this->datalayer}_FILE"))
            ?? $this->datalayer;

        $this->data['path'] =
            $this->data['path']
            ?? env(strtoupper("DB_{$this->datalayer}_PATH"))
            ?? env('PATH_SQLITE');

        File::ensure_extension($this->data['file'], ['sqlite', 's3db', 's2db', 'sqlite3']);

        $this->instancePDO = ["sqlite:" . path($this->data['path'], $this->data['file'])];
    }
}