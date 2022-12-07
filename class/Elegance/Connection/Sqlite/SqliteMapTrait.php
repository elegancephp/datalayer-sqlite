<?php

namespace Elegance\Connection\Sqlite;

use Elegance\Datalayer\Query;

trait SqliteMapTrait
{
    /** Retorna o mapa real do banco de dados */
    protected function loadRealMap(): array
    {
        $listTable = $this->executeQuery(
            Query::select('sqlite_master')
                ->fields('name')
                ->order('name')
                ->where('type', 'table')
                ->where('name != ?', 'sqlite_sequence')
        );

        $map = [];

        foreach ($listTable as $itemTable) {
            $table = $itemTable['name'];
            $map[$table]  = ['comment' => null, 'fields' => []];
            $listFilds = $this->executeQuery("PRAGMA table_info('$table')");
            foreach ($listFilds as $itemField) {
                if ($itemField['name'] != 'id') {

                    $tmp = $itemField['type'];

                    $tmp = str_replace(' ', '', $tmp);
                    $tmp = mb_strtolower($tmp);
                    $tmp = str_replace(')', '(', $tmp);
                    $tmp = explode('(', $tmp);

                    $sqlType = array_shift($tmp);

                    $size = intval(array_shift($tmp));
                    $size = $size ? $size : null;

                    $name = $itemField['name'];
                    $default = $itemField['dflt_value'];

                    $null = !boolval($itemField['notnull']);

                    $comment = null;

                    $map[$table]['fields'][$name]  = [
                        'type' => $sqlType,
                        'comment' => $comment ?? null,
                        'default' => $default,
                        'size' => $size,
                        'null' => $null,
                    ];
                }
            }
        }
        return $map;
    }
}
