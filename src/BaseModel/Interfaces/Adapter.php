<?php

namespace BaseModel\Interfaces;

interface Adapter
{

    /**
     * Выполнить sql запрос
     * @param string $sql
     * @param array $bindings
     * @return \PDOStatement
     */
    public function sql(string $sql, array $bindings = []): \PDOStatement;

    /**
     * Выбрать массив данных из источника согласно критериям
     * @param $tableName
     * @param array $condition
     * @return \PDOStatement
     */
    public function select(string $tableName, array $condition): \PDOStatement;

    /**
     * Вставить новые данные
     * @param $tableName
     * @param array $data
     * @return string
     */
    public function insert(string $tableName, array $data): string;

    /**
     * Обновить данные согласно критериям
     * @param $tableName
     * @param array $data
     * @param array $condition
     * @return integer
     */
    public function update(string $tableName, array $data, array $condition): int;

    /**
     * Удалить данные согласно критериям
     * @param $tableName
     * @param array $condition
     * @return integer
     */
    public function delete(string $tableName, array $condition): int;

}