<?php

namespace App\Service\DB;

class QueryBuilder
{
    private string $query;
    
    public function __construct()
    {
        $this->query = '';
    }
    
    public function __toString(): string
    {
        return $this->getQuery();
    }
    
    /**
     * @param mixed ...$selectors e.g. 'id', 'name', 'surname'
     * @return $this
     */
    public function select(...$selectors): static
    {
        $this->query = sprintf('SELECT %s ', implode(', ', $selectors));
        return $this;
    }
    
    /**
     * FROM statement for SELECT, DELETE queries
     * @param string $table
     * @return $this
     */
    public function from(string $table): static
    {
        $this->query .= sprintf(' FROM %s ', $table);
        return $this;
    }
    
    /**
     * @param string $where e.g. 'id=15', or 'input=?'
     * @return $this
     */
    public function where(string $where): static
    {
        $this->query .= sprintf(' WHERE %s ', $where);
        return $this;
    }
    
    /**
     * INSERT query header
     * @param string $table
     * @param array $fields fields to set, e.g. ['id', 'username', 'birthdate'] 
     * @return $this
     */
    public function insertInto(string $table, array $fields): static
    {
        $this->query = sprintf('INSERT INTO %s (%s) ', $table, implode(', ', $fields));
        return $this;
    }
    
    /**
     * Values set for INSERT query
     * @param string $valueSet single value set, excluding brackets, e.g. 'val1, val2', or '?, ?'
     * @param int $repeat number of times to repeat $valueSet
     * @return $this
     */
    public function values(string $valueSet, int $repeat): static
    {
        $valueSet = sprintf('(%s), ', $valueSet);
        $valueSet = str_repeat($valueSet, $repeat);
        $valueSet = substr($valueSet, 0, -2); // remove trailing comma and space
        $this->query.= sprintf(' VALUES %s ', $valueSet);
        
        
        //$format = sprintf(' VALUES %%%d$s', $repeat); // make %s with repeat, e.g. %5$s // TODO replace with str_repeat 
        //$this->query .= sprintf($format, $valueSet); // TODO add (), 
        return $this;
    }
    
    public function update(string $table): static
    {
        $this->query = sprintf('UPDATE %s ', $table);
        return $this;
    }
    
    public function set(array $values): static
    {
        $setString = ' SET ';
        foreach ($values as $key => $value) {
            $setString .= sprintf(' %s=%s, ', $key, $value);
        }
        $setString = substr($setString, 0, -2); // remove trailing comma and space
        $this->query .= $setString;
        return $this;
    }
    
    /**
     * @return $this
     */
    public function delete(): static
    {
        $this->query = 'DELETE ';
        return $this;
    }
    
    /**
     * Add pagination params limit and offset. If either $limit or $offset ir FALSE,
     * that param will not included in query
     * @param int|bool $limit
     * @param int|bool $offset
     * @return $this
     */
    public function limitOffset(int|bool $limit, int|bool $offset): static
    {
        if ($limit !== false)
            $this->query .= sprintf(' LIMIT %d ', $limit);
        
        if ($offset !== false)
            $this->query .= sprintf(' OFFSET %d ', $offset);
        
        return $this;
    }
    
    public function joinOn(string $joinedTable, string $on): static
    {
        $this->query .= sprintf(' JOIN %s ON %s ', $joinedTable, $on);
        return $this;
    }
    
    public function truncate(string $table): static
    {
        $this->query = sprintf('TRUNCATE %s ', $table);
        return $this;
    }
    
    /**
     * Insert any custom text into the query. Can be used for any operations not 
     * supported by QueryBuilder
     * @param string $query
     * @return $this
     */
    public function custom(string $query): static
    {
        $this->query .= $query;
        return $this;
    }
    
    /**
     * Get final query as string
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }
}