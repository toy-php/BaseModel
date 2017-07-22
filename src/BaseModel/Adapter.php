<?php

namespace BaseModel;

use BaseModel\Interfaces\Adapter as AdapterInterface;

class Adapter implements AdapterInterface
{

    /**
     * Массив метаданных таблиц
     * @var array
     */
    protected $tablesMeta = [];

    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Получить метаданные о таблице
     * @param string $table
     * @return array
     */
    protected function getMeta(string $table): array
    {
        if (!isset($this->tablesMeta[$table])) {
            $this->tablesMeta[$table] = $this->pdo->query('SHOW COLUMNS FROM ' . $table)
                ->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $this->tablesMeta[$table];
    }

    /**
     * Получить поля для выборки
     * @param string $tableName
     * @return array
     */
    protected function getColumns(string $tableName): array
    {
        $tableMeta = $this->getMeta($tableName);
        return array_column($tableMeta, 'Field');
    }

    /**
     * @inheritdoc
     */
    public function sql(string $sql, array $bindings = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    /**
     * @inheritdoc
     */
    public function select(string $tableName, array $condition = []): \PDOStatement
    {
        $sql = sprintf(/** @lang text */
            'SELECT %s FROM %s %s %s %s %s',
            $this->parseColumns(isset($condition['COLUMNS'])
                ? $condition['COLUMNS']
                : $this->getColumns($tableName)),
            $this->parseTableName($tableName),
            $this->parseJoin(isset($condition['JOINS']) ? $condition['JOINS'] : null),
            $this->parseConditions(isset($condition['WHERE']) ? $condition['WHERE'] : null),
            $this->parseOrder(isset($condition['ORDER']) ? $condition['ORDER'] : null),
            $this->parseLimit(isset($condition['LIMIT']) ? $condition['LIMIT'] : null)
        );
        $bindings = $this->parseBindings(isset($condition['WHERE']) ? $condition['WHERE'] : []);
        return $this->sql($sql, $bindings);
    }

    /**
     * @inheritdoc
     */
    public function insert(string $table, array $data): string
    {
        $keys = array_keys($data);
        $sql = sprintf(/** @lang text */
            'INSERT INTO %s %s VALUES %s;',
            $this->parseTableName($table),
            '(' . rtrim(implode(', ', $keys), ', ') . ')',
            '(' . rtrim(str_repeat('?, ', count($keys)), ', ') . ')');
        $bindings = $this->parseBindings($data);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $this->pdo->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function update(string $table, array $data, array $condition): int
    {
        $set = '';
        $bindings = [];
        foreach ($data as $key => $value) {
            $set .= $key . ' = ?, ';
            $bindings[] = $value;
        }
        $sql = sprintf(/** @lang text */
            'UPDATE %s SET %s %s;',
            $this->parseTableName($table),
            rtrim($set, ', '),
            $this->parseConditions($condition));
        $bindings = array_merge($this->parseBindings($data),
            $this->parseBindings($condition));
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($bindings)) {
            return $stmt->rowCount();
        }
        throw new Exception('При сохранении данных возникла ошибка');
    }

    /**
     * @inheritdoc
     */
    public function delete(string $table, array $condition): int
    {
        $sql = sprintf(/** @lang text */
            'DELETE FROM %s %s',
            $this->parseTableName($table),
            $this->parseConditions($condition));
        $bindings = $this->parseBindings($condition);
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($bindings)) {
            return $stmt->rowCount();
        }
        throw new Exception('При удалении данных возникла ошибка');
    }

    /**
     * Парсинг имени таблицы
     * @param $data
     * @return string
     */
    protected function parseTableName($data)
    {
        if (is_string($data)) {
            return $data;
        }

        $parseAlias = function ($data) {
            if (preg_match('/^([A-Za-z_0-9]+)\(([A-Za-z_0-9]+)\)$/i', $data, $matches)) {
                return $matches;
            }
            return $data;
        };

        $result = '';
        foreach ($data as $tableName) {
            $result .= ((($parsedTableName = $parseAlias($tableName)) != $tableName)
                    ? $parsedTableName[1] . ' AS ' . $parsedTableName[2]
                    : $tableName) . ', ';
        }
        return rtrim($result, ', ');
    }

    /**
     * Парсинг связанных данных
     * @param $data
     * @return array|mixed
     */
    protected function parseBindings($data)
    {
        if (!is_array($data)) {
            return [];
        }
        $parseBindings = function (\RecursiveArrayIterator $iterator) use (&$parseBindings) {
            $result = [];
            while ($iterator->valid()) {
                if ($iterator->hasChildren()) {
                    $result = array_merge($result, $parseBindings($iterator->getChildren()));
                } else {
                    $result[] = $iterator->current();
                }
                $iterator->next();
            }
            return $result;
        };

        return $parseBindings(new \RecursiveArrayIterator($data));
    }

    /**
     * Парсинг колонок
     * @param $data
     * @return string
     */
    protected function parseColumns($data)
    {
        if (empty($data)) {
            return '*';
        }
        if (is_string($data)) {
            return $data;
        }
        $result = '';
        if (count($data) != count($data, COUNT_RECURSIVE)) {
            foreach ($data as $table => $columns) {
                foreach ($columns as $column) {
                    $result .= $table . '.' . $column . ', ';
                }
            }
        } else {
            $result = implode(', ', $data);
        }
        return rtrim($result, ', ');
    }

    /**
     * Парсинг джоинов
     * @param $joins
     * @return string
     */
    protected function parseJoin($joins)
    {
        if (empty($joins)) {
            return '';
        }
        if (is_string($joins)) {
            return ' ' . $joins . ' ';
        }
        $join_directions = [
            '>' => function ($table, $condition) {
                return ' LEFT JOIN ' . $table . ' ON ' . $condition;
            },
            '<' => function ($table, $condition) {
                return ' RIGHT JOIN ' . $table . ' ON ' . $condition;
            },
            '<>' => function ($table, $condition) {
                return ' FULL JOIN ' . $table . ' ON ' . $condition;
            },
            '><' => function ($table, $condition) {
                return ' INNER JOIN ' . $table . ' ON ' . $condition;
            },
        ];

        $condition = function (array $on, $alias) {
            $result = '';
            foreach ($on as $foreign => $primary) {
                $result .= $foreign . ' = ' . $alias . '.' . $primary . ' AND ';
            }
            return '(' . rtrim($result, ' AND ') . ')';
        };

        $result = '';
        foreach ($joins as $join => $on) {
            if (preg_match('/^(\[([<>]+)\])([A-Za-z_0-9]+)(\(([A-Za-z_0-9]+)\))*?$/i', $join, $matches)) {
                $direction = $matches[2];
                $source = $matches[3];
                $alias = isset($matches[5]) ? $matches[5] : $source;
                if (isset($join_directions[$direction])) {
                    $table = $source != $alias ? $source . ' AS ' . $alias : $source;
                    $result .= $join_directions[$direction]($table, $condition($on, $alias));
                }

            }
        }
        return $result;
    }

    /**
     * Парсинг критериев
     * @param $conditions
     * @return string
     */
    protected function parseConditions($conditions)
    {
        if (empty($conditions)) {
            return '';
        }
        if (is_string($conditions)) {
            return ' WHERE ' . $conditions;
        }
        $operators = [
            '=' => function ($field, $bind) {
                if (is_array($bind)) {
                    return $field . ' IN (' . implode(', ', $bind) . ')';
                }
                if (is_null($bind)) {
                    return $field . ' IS NULL';
                }
                return $field . ' = ' . $bind;
            },
            '>' => function ($field, $bind) {
                return $field . ' > ' . $bind;
            },
            '<' => function ($field, $bind) {
                return $field . ' < ' . $bind;
            },
            '>=' => function ($field, $bind) {
                return $field . ' >= ' . $bind;
            },
            '<=' => function ($field, $bind) {
                return $field . ' <= ' . $bind;
            },
            '!' => function ($field, $bind) {
                if (is_array($bind)) {
                    return $field . ' NOT IN (' . implode(', ', $bind) . ')';
                }
                if (is_null($bind)) {
                    return $field . ' IS NOT NULL';
                }
                return $field . ' != ' . $bind;
            },
            '~' => function ($field, $bind) {
                if (is_array($bind)) {
                    $like_value = '';
                    $count_value = count($bind);
                    foreach ($bind as $key => $item) {
                        $like_value .= (($count_value - 1) > $key)
                            ? $field . ' LIKE ' . $item . ' OR '
                            : $field . ' LIKE ' . $item;
                    }
                    return $like_value;
                }
                return $field . ' LIKE ' . $bind;
            },
            '!~' => function ($field, $bind) {
                if (is_array($bind)) {
                    $like_value = '';
                    $count_value = count($bind);
                    foreach ($bind as $key => $item) {
                        $like_value .= (($count_value - 1) > $key)
                            ? $field . ' NOT LIKE ' . $item . ' OR '
                            : $field . ' NOT LIKE ' . $item;
                    }
                    return $like_value;

                }
                return $field . ' NOT LIKE ' . $bind;
            },
            '<>' => function ($field, $bind) {
                return $field . ' BETWEEN ' . implode(' AND ', $bind);
            },
            '><' => function ($field, $bind) {
                return $field . ' NOT BETWEEN ' . implode(' AND ', $bind);
            }
        ];

        $parseConditions = function (array $criteria) use (&$parseConditions, $operators) {
            $result = [];
            foreach ($criteria as $key => $value) {
                if (preg_match('/^(and|or)(#[0-9]+)*?$/i', $key) and is_array($value)) {
                    $result[$key] = $parseConditions($value);
                } elseif (preg_match('/^([A-Za-z0-9_.\'`]+)(\[([~=<>!]+)\])*?$/i', $key, $matches)) {
                    $operator = isset($matches[3]) ? $matches[3] : '=';
                    if (isset($operators[$operator])) {
                        $bind = is_array($value) ? array_fill(0, count($value), '?') : '?';
                        $result[] = $operators[$operator]($matches[1], $bind);
                    }
                }
            }
            return $result;
        };

        $convertConditions = function (array $condition) use (&$convertConditions) {
            $result = [];
            foreach ($condition as $key => $value) {
                if (preg_match('/^(or|and)(#[0-9]+)*?$/i', $key, $matches)) {
                    $result[$key] = '(' . implode(' ' . strtoupper($matches[1]) . ' ', $convertConditions($value)) . ')';
                } else {
                    $result[$key] = $value;
                }
            }
            return $result;
        };

        $condition = $convertConditions($parseConditions($conditions));
        return !empty($condition) ? ' WHERE ' . implode(' ', $condition) : '';
    }

    /**
     * Парсинг лимита
     * @param $limit
     * @return string
     */
    protected function parseLimit($limit)
    {
        if (empty($limit)) {
            return '';
        }
        if (is_string($limit)) {
            return ' LIMIT ' . $limit;
        }
        if (is_array($limit)) {
            return ' LIMIT ' . implode(', ', $limit);
        }
        return '';
    }

    /**
     * Парсинг сортировки
     * @param $order
     * @return string
     */
    protected function parseOrder($order)
    {
        if (empty($order)) {
            return '';
        }
        $result = ' ORDER BY ';
        if (is_string($order)) {
            return $result . $order;
        }
        foreach ($order as $key => $value) {
            $result .= ' ' . $key . ' ' . $value . ',';
        }
        return rtrim($result, ',');
    }
}