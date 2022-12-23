<?php

namespace Elegance\Connection\Sqlite;

use Elegance\Datalayer\Query;

trait SqliteExecuteSchemeQueryTrait
{
    /** Executa uma lista de querys de esquema */
    function executeSchemeQuery(array $schemeQueryList): void
    {
        $queryList = [];

        foreach ($schemeQueryList as $schemeQuery) {
            list($action, $data) = $schemeQuery;
            array_push($queryList, ...match ($action) {
                'create' => $this->getQueryCreateTable(...$data),
                'alter' => $this->getQueryAlterTable(...$data),
                'drop' => $this->getQueryDropTable(...$data),
                default => []
            });
        }

        $this->executeQueryList($queryList);
    }

    /** Returna um array de query de criação de tabela */
    protected function getQueryCreateTable(string $table, ?string $comment, array $fields): array
    {
        $queryFields = [
            '[id] INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT'
        ];

        foreach ($fields['add'] ?? [] as $fielName => $field) {
            if ($field) {
                $queryFields[] = $this->getQueryFieldTemplate($fielName, $field);
            }
        }

        return [
            prepare("CREATE TABLE [[#name]] ([#fields])", [
                'name' => $table,
                'fields' => implode(', ', $queryFields)
            ])
        ];
    }

    /** Retorna um array de query para alteração de tabela */
    protected function getQueryAlterTable(string $table, ?string $comment, array $fields): array
    {
        $query = [];

        $newFields = $this->map()[$table]['fields'];

        foreach (array_keys($fields['drop']) as $fieldName) {
            if (isset($newFields[$fieldName])) {
                unset($newFields[$fieldName]);
            }
        }

        foreach ($fields['add'] as $name => $field) {
            if (is_null($field['default'])) {
                $field['null'] = true;
            }
            $newFields[$name] = $field;
        }

        foreach ($fields['alter'] as $name => $field) {
            $newFields[$name] = $field;
        }

        $fieldsName = ['id'];

        array_push($fieldsName, ...array_keys($newFields));

        $fieldsName = implode(', ', $fieldsName);

        $insert = [];

        foreach ($this->executeQuery(Query::select($table)) as $result) {
            $innerValues = [$result['id']];
            foreach ($newFields as $name => $field) {
                $inner = $result[$name] ?? $field['default'] ?? 'NULL';
                $inner = is_int($inner) || $inner == 'NULL' ? $inner : "'$inner'";
                $innerValues[] = $inner;
            }
            $insert[] = implode(', ', $innerValues);
        }

        $query[] = 'PRAGMA foreign_keys=off';

        array_push($query, ...$this->getQueryDropTable($table));
        array_push($query, ...$this->getQueryCreateTable($table, $comment, ['add' => $newFields]));

        if (count($insert)) {
            $query[] = prepare(
                "INSERT INTO [[#table]] ([#fieldsName]) VALUES ([#insert])",
                [
                    'table' => $table,
                    'fieldsName' => $fieldsName,
                    'insert' => implode('), (', $insert)
                ]
            );
        }

        $query[] = 'PRAGMA foreign_keys=on';

        return $query;
    }

    /** Retorna um array de query para remoçao de tabela */
    protected function getQueryDropTable(string $table): array
    {
        return [
            prepare(
                "DROP TABLE `[#]`",
                $table
            )
        ];
    }

    /** Retorna o template do campo para composição de querys */
    protected static function getQueryFieldTemplate(string $fieldName, array $field): string
    {
        $prepare = '';
        $field['name'] = $fieldName;
        $field['null'] = $field['null'] ? '' : ' NOT NULL';
        switch ($field['type']) {
            case 'idx':
            case 'time':
                $field['default'] = is_null($field['default']) ? '' : ' DEFAULT ' . $field['default'];
                $prepare = "[[#name]] int([#size]) [#default][#null]";
                break;

            case 'int':
                $field['default'] = is_null($field['default']) ? '' : ' DEFAULT ' . $field['default'];
                $prepare = "[[#name]] int([#size])[#default][#null]";
                break;

            case 'tinyint':
            case 'boolean':
            case 'status':
                $field['default'] = is_null($field['default']) ? '' : ' DEFAULT ' . $field['default'];
                $prepare = "[[#name]] tinyint([#size])[#default][#null]";
                break;

            case 'float':
                $field['default'] = is_null($field['default']) ? '' : ' DEFAULT ' . $field['default'];
                $prepare = "[[#name]] float([#size])[#default][#null]";
                break;

            case 'ids':
            case 'log':
            case 'tag':
            case 'text':
            case 'list':
            case 'meta':
            case 'config':
                $field['default'] = is_null($field['default']) ? '' : " DEFAULT '" . $field['default'] . "'";
                $prepare = "[[#name]] text[#default][#null]";
                break;

            case 'varchar':
            case 'string':
            case 'email':
            case 'code':
            case 'md5':
                $field['default'] = is_null($field['default']) ? '' : " DEFAULT '" . $field['default'] . "'";
                $prepare = "[[#name]] varchar([#size])[#default][#null]";
                break;
        }
        return prepare($prepare, $field);
    }
}