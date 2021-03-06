<?php

/**
 * A dead simple PHP 5 OO interface for building SQL queries. No manual
 * string concatenation necessary.
 *
 * @author    Justin Stayton <justin.stayton@gmail.com>
 * @copyright Copyright 2011 by Justin Stayton
 * @license   http://en.wikipedia.org/wiki/MIT_License MIT License
 * @package   QueryBuilder
 * @version   3.0.0
 */
class QueryBuilder {

    /**
     * PDO database connection to use in executing the query.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Execution options like DISTINCT and SQL_CALC_FOUND_ROWS.
     *
     * @var array
     */
    private $option;

    /**
     * Columns, tables, and expressions to SELECT from.
     *
     * @var array
     */
    private $select;

    /**
     * Column to INSERT INTO.
     *
     * @var string
     */
    private $insert;

    /**
     * Column to UPDATE.
     *
     * @var string
     */
    private $update;

    /**
     * Column to DELETE FROM.
     *
     * @var string
     */
    private $delete;

    /**
     * Table to select FROM.
     *
     * @var array
     */
    private $from;

    /**
     * JOIN tables and ON criteria.
     *
     * @var array
     */
    private $join;

    /**
     * WHERE criteria.
     *
     * @var array
     */
    private $where;

    /**
     * Columns to GROUP BY.
     *
     * @var array
     */
    private $groupBy;

    /**
     * HAVING criteria.
     *
     * @var array
     */
    private $having;

    /**
     * Columns to ORDER BY.
     *
     * @var array
     */
    private $orderBy;

    /**
     * Number of rows to return from offset.
     *
     * @var array
     */
    private $limit;

    /**
     * WHERE placeholder values.
     *
     * @var array
     */
    private $wherePlaceholderValues;

    /**
     * SET placeholder values.
     *
     * @var array
     */
    private $setPlaceholderValues;

    /**
     * HAVING placeholder values.
     *
     * @var array
     */
    private $havingPlaceholderValues;

    /**
     * Constructor.
     *
     * @param  PDO|null $pdo optional PDO database connection
     * @return QueryBuilder
     */
    public function __construct(PDO $pdo = null) {
        $this->option = array();
        $this->select = array();
        $this->from = array();
        $this->join = array();
        $this->where = array();
        $this->set = array();
        $this->groupBy = array();
        $this->having = array();
        $this->orderBy = array();
        $this->limit = array();

        $this->wherePlaceholderValues = array();
        $this->setPlaceholderValues = array();
        $this->havingPlaceholderValues = array();

        $this->setpdo($pdo);
    }

    /**
     * Set the PDO database connection to use in executing this query.
     *
     * @param  PDO|null $pdo optional PDO database connection
     * @return QueryBuilder
     */
    public function setpdo(PDO $pdo = null) {
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Get the PDO database connection to use in executing this query.
     *
     * @return PDO|null
     */
    public function getpdo() {
        return $this->pdo;
    }

    /**
     * Safely escape a value for use in a query.
     *
     * @param  string $value value to escape
     * @return string|false escaped value or false if failed
     */
    public function quote($value) {
        $pdo = $this->getpdo();

        // If a PDO database connection is set, use it to quote the value using
        // the underlying database. Otherwise, quote it manually.
        if (isset($pdo)) {
            return $pdo->quote($value);
        }
        else {
            if (is_numeric($value)) {
                return $value;
            }
            else {
                return "'" . addslashes($value) . "'";
            }
        }
    }

    /**
     * Add an execution option like DISTINCT or SQL_CALC_FOUND_ROWS.
     *
     * @param  string $option execution option to add
     * @return QueryBuilder
     */
    public function option($option) {
        $this->option[] = $option;

        return $this;
    }

    /**
     * Add SQL_CALC_FOUND_ROWS execution option.
     *
     * @return QueryBuilder
     */
    public function calcFoundRows() {
        return $this->option('SQL_CALC_FOUND_ROWS');
    }

    /**
     * Add DISTINCT execution option.
     *
     * @return QueryBuilder
     */
    public function distinct() {
        return $this->option('DISTINCT');
    }

    /**
     * Add a SELECT column, table, or expression with optional alias.
     *
     * @param  string $column column name, table name, or expression
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function select($column, $alias = null) {
        $this->select[$column] = $alias;

        return $this;
    }

    /**
     * Set an INSERT table
     *
     * @param  string $table table name
     * @return QueryBuilder
     */
    public function insert($table) {
        $this->insert = $table;
        return $this;
    }

    /**
     * Set an UPDATE table
     *
     * @param  string $table table name
     * @return QueryBuilder
     */
    public function update($table) {
        $this->update = $table;
        return $this;
    }

    /**
     * Set an DELETE table
     *
     * @param  string $table table name
     * @return QueryBuilder
     */
    public function delete($table) {
        $this->delete = $table;
        return $this;
    }

    /**
     * Get the SELECT portion of the query as a string.
     *
     * @param  bool $includeText optional include 'SELECT' text, default true
     * @return string SELECT portion of the query
     */
    public function getSelectString($includeText = true) {
        $select = "";

        // Add any execution options.
        if (!empty($this->option)) {
            $select .= implode(' ', $this->option) . " ";
        }

        foreach ($this->select as $currentColumn => $currentAlias) {
            $select .= $currentColumn;

            if (isset($currentAlias)) {
                $select .= " AS " . $currentAlias;
            }

            $select .= ", ";
        }

        $select = substr($select, 0, -2);

        if ($includeText && !empty($select)) {
            $select = "SELECT " . $select;
        }

        return $select;
    }


    /**
     * Get the INSERT INTO portion of the query as a string.
     *
     * @return string INSERT INTO portion of the query
     */
    public function getInsertString() {
        $insert = "INSERT INTO " . $this->insert;
        return $insert;
    }

    /**
     * Get the UPDATE portion of the query as a string.
     *
     * @return string INSERT INTO portion of the query
     */
    public function getUpdateString() {
        $update = "UPDATE " . $this->update;
        return $update;
    }


    /**
     * Get the DELETE FROM portion of the query as a string.
     *
     * @return string INSERT INTO portion of the query
     */
    public function getDeleteString() {
        $delete = "DELETE FROM " . $this->delete;
        return $delete;
    }

    /**
     * Set the FROM table with optional alias.
     *
     * @param  string $table table name
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function from($table, $alias = null) {
        $this->from['table'] = $table;
        $this->from['alias'] = $alias;

        return $this;
    }

    /**
     * Get the FROM table.
     *
     * @return string FROM table
     */
    public function getFrom() {
        return $this->from['table'];
    }

    /**
     * Get the FROM table alias.
     *
     * @return string FROM table alias
     */
    public function getFromAlias() {
        return $this->from['alias'];
    }

    /**
     * Add a JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $type optional type of join, default INNER JOIN
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function join($table, $criteria = null, $type = "INNER JOIN", $alias = null) {
        if (is_string($criteria)) {
            $criteria = array($criteria);
        }

        $this->join[] = array('table'    => $table,
            'criteria' => $criteria,
            'type'     => $type,
            'alias'    => $alias);

        return $this;
    }

    /**
     * Add an INNER JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function innerJoin($table, $criteria = null, $alias = null) {
        return $this->join($table, $criteria, "INNER JOIN", $alias);
    }

    /**
     * Add a LEFT JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function leftJoin($table, $criteria = null, $alias = null) {
        return $this->join($table, $criteria, "LEFT JOIN", $alias);
    }

    /**
     * Add a RIGHT JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function rightJoin($table, $criteria = null, $alias = null) {
        return $this->join($table, $criteria, "RIGHT JOIN", $alias);
    }

    /**
     * Get an ON criteria string joining the specified table and column to the
     * same column of the previous JOIN or FROM table.
     *
     * @param  int $joinIndex index of current join
     * @param  string $table current table name
     * @param  string $column current column name
     * @return string ON join criteria
     */
    private function getJoinCriteriaUsingPreviousTable($joinIndex, $table, $column) {
        $previousJoinIndex = $joinIndex - 1;

        // If the previous table is from a JOIN, use that. Otherwise, use the
        // FROM table.
        if (array_key_exists($previousJoinIndex, $this->join)) {
            $previousTable = $this->join[$previousJoinIndex]['table'];
        }
        else {
            $previousTable = $this->getFrom();
        }

        return $previousTable . "." . $column . " = " . $table . "." . $column;
    }

    /**
     * Get the JOIN portion of the query as a string.
     *
     * @return string JOIN portion of the query
     */
    public function getJoinString() {
        $join = "";

        foreach ($this->join as $i => $currentJoin) {
            $join .= " " . $currentJoin['type'] . " " . $currentJoin['table'];

            if (isset($currentJoin['alias'])) {
                $join .= " AS " . $currentJoin['alias'];
            }

            // Add ON criteria if specified.
            if (isset($currentJoin['criteria'])) {
                $join .= " ON ";

                foreach ($currentJoin['criteria'] as $x => $criterion) {
                    // Logically join each criterion with AND.
                    if ($x != 0) {
                        $join .= " AND ";
                    }

                    // If the criterion does not include an equals sign, assume a
                    // column name and join against the same column from the previous
                    // table.
                    if (strpos($criterion, '=') === false) {
                        $join .= $this->getJoinCriteriaUsingPreviousTable($i, $currentJoin['table'], $criterion);
                    }
                    else {
                        $join .= $criterion;
                    }
                }
            }
        }

        $join = trim($join);

        return $join;
    }

    /**
     * Get the FROM portion of the query, including all JOINs, as a string.
     *
     * @param  bool $includeText optional include 'FROM' text, default true
     * @return string FROM portion of the query
     */
    public function getFromString($includeText = true) {
        $from = "";

        if (!empty($this->from)) {
            $from .= $this->from['table'];

            if (isset($this->from['alias'])) {
                $from .= " AS " . $this->from['alias'];
            }

            // Add any JOINs.
            $from .= " " . $this->getJoinString();
        }

        $from = rtrim($from);

        if ($includeText && !empty($from)) {
            $from = "FROM " . $from;
        }

        return $from;
    }

    /**
     * Add an open bracket for nesting conditions to the specified WHERE or
     * HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    private function openCriteria(array &$criteria, $connector = "AND") {
        $criteria[] = array('bracket'   => '(', 'connector' => $connector);
        return $this;
    }

    /**
     * Add a closing bracket for nesting conditions to the specified WHERE or
     * HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @return QueryBuilder
     */
    private function closeCriteria(array &$criteria) {
        $criteria[] = array('bracket'   => ')', 'connector' => null);
        return $this;
    }

    /**
     * Add a condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    private function criteria(array &$criteria, $column, $value, $operator = "=", $connector = "AND") {
        $criteria[] = array(
            'column'    => $column,
            'value'     => $value,
            'operator'  => $operator,
            'connector' => $connector
        );
        return $this;
    }

    /**
     * Add an OR condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @return QueryBuilder
     */
    private function orCriteria(array &$criteria, $column, $value, $operator = "=") {
        return $this->criteria($criteria, $column, $value, $operator, "OR");
    }

    /**
     * Add an IN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    private function criteriaIn(array &$criteria, $column, array $values, $connector = "AND") {
        return $this->criteria($criteria, $column, $values, "IN", $connector);
    }

    /**
     * Add a NOT IN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    private function criteriaNotIn(array &$criteria, $column, array $values, $connector = "AND") {
        return $this->criteria($criteria, $column, $values, "NOT IN", $connector);
    }

    /**
     * Add a BETWEEN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    private function criteriaBetween(array &$criteria, $column, $min, $max, $connector = "AND") {
        return $this->criteria($criteria, $column, array($min, $max), "BETWEEN", $connector);
    }

    /**
     * Add a NOT BETWEEN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    private function criteriaNotBetween(array &$criteria, $column, $min, $max, $connector = "AND") {
        return $this->criteria($criteria, $column, array($min, $max), "NOT BETWEEN", $connector);
    }

    /**
     * Get the WHERE or HAVING portion of the query as a string.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  array $placeholderValues optional placeholder values array
     * @return string WHERE or HAVING portion of the query
     */
    private function getCriteriaString(array &$criteria, $usePlaceholders = true, array &$placeholderValues = array()) {
        $string            = "";
        $placeholderValues = array();
        $useConnector      = false;

        foreach ($criteria as $i => $currentCriterion) {
            if (array_key_exists('bracket', $currentCriterion)) {
                if (strcmp($currentCriterion['bracket'], "(") == 0) {
                    if ($useConnector) {
                        $string .= " " . $currentCriterion['connector'] . " ";
                    }
                    $useConnector = false;
                } else {
                    $useConnector = true;
                }
                $string .= $currentCriterion['bracket'];
            }
            else {
                if ($useConnector) {
                    $string .= " " . $currentCriterion['connector'] . " ";
                }
                $useConnector = true;
                $value        = $this->getCriteriaTypeString($currentCriterion, $placeholderValues, $usePlaceholders);
                $string      .= $currentCriterion['column'] . " " . $currentCriterion['operator'] . " " . $value;
            }
        }

        return $string;
    }

    /**
     * get string for one criteria
     *
     * @param array $currentCriterion
     * @param array &$placeholderValues
     * @param bool $usePlaceholders
     * @return string $string
     */
    private function getCriteriaTypeString($currentCriterion, &$placeholderValues, $usePlaceholders) {
        switch ($currentCriterion['operator']) {
            case "BETWEEN":
            case "NOT BETWEEN":
                return $this->getBetweenCriteriaString($currentCriterion, $placeholderValues, $usePlaceholders);
                break;
            case "IN":
            case "NOT IN":
                return $this->getInCriteriaString($currentCriterion, $placeholderValues, $usePlaceholders);
                break;
            case "IS":
            case "IS NOT":
                return $currentCriterion['value'];
                break;
            default:
                if ($usePlaceholders) {
                    $placeholderValues[] = $currentCriterion['value'];
                    return "?";
                }
                else {
                    return $this->quote($currentCriterion['value']);
                }
                break;
        }
    }

    /**
     * Get BETWEEN criteria string
     *
     * @param $currentCriterion
     * @param $placeholderValues
     * @param $usePlaceholders
     * @return string
     */
    private function getBetweenCriteriaString($currentCriterion, &$placeholderValues, $usePlaceholders) {
        if ($usePlaceholders) {
            $value = "? AND ?";
            $placeholderValues[] = $currentCriterion['value'][0];
            $placeholderValues[] = $currentCriterion['value'][1];
        }
        else {
            $value = $this->quote($currentCriterion['value'][0])." AND ".$this->quote($currentCriterion['value'][1]);
        }
        return $value;
    }

    /**
     * Get IN criteria string
     *
     * @param $currentCriterion
     * @param $placeholderValues
     * @param $usePlaceholders
     * @return string
     */
    private function getInCriteriaString($currentCriterion, &$placeholderValues, $usePlaceholders) {
        if ($usePlaceholders) {
            $value = "(".substr(str_repeat('?, ', count($currentCriterion['value'])), 0, -2).")";
            $placeholderValues = array_merge($placeholderValues, $currentCriterion['value']);
        }
        else {
            $value = "(";
            foreach ($currentCriterion['value'] as $currentValue) {
                $value .= $this->quote($currentValue) . ", ";
            }
            $value  = substr($value, 0, -2);
            $value .= ")";
        }
        return $value;
    }

    /**
     * Add an open bracket for nesting WHERE conditions.
     *
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function openWhere($connector = "AND") {
        return $this->openCriteria($this->where, $connector);
    }

    /**
     * Add a closing bracket for nesting WHERE conditions.
     *
     * @return QueryBuilder
     */
    public function closeWhere() {
        return $this->closeCriteria($this->where);
    }

    /**
     * Add a WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function where($column, $value, $operator = "=", $connector = "AND") {
        return $this->criteria($this->where, $column, $value, $operator, $connector);
    }

    /**
     * Add a SET condition.
     *
     * @param  string $column column name or array with column => value pairs
     * @param  mixed $value value
     * @internal param string $operator optional comparison operator, default =
     * @return QueryBuilder
     */
    public function set($column, $value = null) {
        if (is_array($column)) {
            $this->set = array_merge($this->set, $column);
        } else {
            $this->set[$column] = $value;
        }
        return $this;
    }

    /**
     * Add an AND WHERE condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @return QueryBuilder
     */
    public function andWhere($column, $value, $operator = "=") {
        return $this->criteria($this->where, $column, $value, $operator, "AND");
    }

    /**
     * Add an OR WHERE condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @return QueryBuilder
     */
    public function orWhere($column, $value, $operator = "=") {
        return $this->orCriteria($this->where, $column, $value, $operator, "OR");
    }

    /**
     * Add an IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function whereIn($column, array $values, $connector = "AND") {
        return $this->criteriaIn($this->where, $column, $values, $connector);
    }

    /**
     * Add a NOT IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function whereNotIn($column, array $values, $connector = "AND") {
        return $this->criteriaNotIn($this->where, $column, $values, $connector);
    }

    /**
     * Add a BETWEEN WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function whereBetween($column, $min, $max, $connector = "AND") {
        return $this->criteriaBetween($this->where, $column, $min, $max, $connector);
    }

    /**
     * Add a NOT BETWEEN WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function whereNotBetween($column, $min, $max, $connector = "AND") {
        return $this->criteriaNotBetween($this->where, $column, $min, $max, $connector);
    }

    /**
     * Get the WHERE portion of the query as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  bool $includeText optional include 'WHERE' text, default true
     * @return string WHERE portion of the query
     */
    public function getWhereString($usePlaceholders = true, $includeText = true) {
        $where = $this->getCriteriaString($this->where, $usePlaceholders, $this->wherePlaceholderValues);

        if ($includeText && !empty($where)) {
            $where = "WHERE " . $where;
        }

        return $where;
    }

    /**
     * Get the SET portion of the query as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string WHERE portion of the query
     */
    public function getSetString($usePlaceholders = true) {

        $set = "";
        foreach ($this->set as $column => $value) {
            if ($usePlaceholders) {
                $setValue = "?";
                $this->setPlaceholderValues[] = $value;
            }
            else {
                $setValue = $this->quote($value);
            }
            $set .= $column . " = " . $setValue . ", ";
        }

        $set = substr($set, 0, -2);
        $set = "SET " . $set;
        return $set;
    }

    /**
     * Get the WHERE placeholder values when
     * {@link QueryBuilder::getWhereString()} is called with the parameter to
     * use placeholder values.
     *
     * @return array WHERE placeholder values
     */
    public function getWherePlaceholderValues() {
        return $this->wherePlaceholderValues;
    }

    /**
     * Get the SET placeholder values when
     * {@link QueryBuilder::getSetString()} is called with the parameter to
     * use placeholder values.
     *
     * @return array SET placeholder values
     */
    public function getSetPlaceholderValues() {
        return $this->setPlaceholderValues;
    }

    /**
     * Add a GROUP BY column.
     *
     * @param  string $column column name
     * @param  string $order optional order direction, default ASC
     * @return QueryBuilder
     */
    public function groupBy($column, $order = "ASC") {
        $this->groupBy[] = array(
            'column' => $column,
            'order'  => $order
        );
        return $this;
    }

    /**
     * Get the GROUP BY portion of the query as a string.
     *
     * @param  bool $includeText optional include 'GROUP BY' text, default true
     * @return string GROUP BY portion of the query
     */
    public function getGroupByString($includeText = true) {
        $groupBy = "";

        foreach ($this->groupBy as $currentGroupBy) {
            $groupBy .= $currentGroupBy['column'] . " " . $currentGroupBy['order'] . ", ";
        }

        $groupBy = substr($groupBy, 0, -2);

        if ($includeText && !empty($groupBy)) {
            $groupBy = "GROUP BY " . $groupBy;
        }

        return $groupBy;
    }

    /**
     * Add an open bracket for nesting HAVING conditions.
     *
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function openHaving($connector = "AND") {
        return $this->openCriteria($this->having, $connector);
    }

    /**
     * Add a closing bracket for nesting HAVING conditions.
     *
     * @return QueryBuilder
     */
    public function closeHaving() {
        return $this->closeCriteria($this->having);
    }

    /**
     * Add a HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function having($column, $value, $operator = "=", $connector = "AND") {
        return $this->criteria($this->having, $column, $value, $operator, $connector);
    }

    /**
     * Add an AND HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @return QueryBuilder
     */
    public function andHaving($column, $value, $operator = "=") {
        return $this->criteria($this->having, $column, $value, $operator, "AND");
    }

    /**
     * Add an OR HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @return QueryBuilder
     */
    public function orHaving($column, $value, $operator = "=") {
        return $this->orCriteria($this->having, $column, $value, $operator, "OR");
    }

    /**
     * Add an IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function havingIn($column, array $values, $connector = "AND") {
        return $this->criteriaIn($this->having, $column, $values, $connector);
    }

    /**
     * Add a NOT IN HAVING condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function havingNotIn($column, array $values, $connector = "AND") {
        return $this->criteriaNotIn($this->having, $column, $values, $connector);
    }

    /**
     * Add a BETWEEN HAVING condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function havingBetween($column, $min, $max, $connector = "AND") {
        return $this->criteriaBetween($this->having, $column, $min, $max, $connector);
    }

    /**
     * Add a NOT BETWEEN HAVING condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function havingNotBetween($column, $min, $max, $connector = "AND") {
        return $this->criteriaNotBetween($this->having, $column, $min, $max, $connector);
    }

    /**
     * Get the HAVING portion of the query as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  bool $includeText optional include 'HAVING' text, default true
     * @return string HAVING portion of the query
     */
    public function getHavingString($usePlaceholders = true, $includeText = true) {
        $having = $this->getCriteriaString($this->having, $usePlaceholders, $this->havingPlaceholderValues);

        if ($includeText && !empty($having)) {
            $having = "HAVING " . $having;
        }

        return $having;
    }

    /**
     * Get the HAVING placeholder values when
     * {@link QueryBuilder::getHavingString()} is called with the parameter to
     * use placeholder values.
     *
     * @return array HAVING placeholder values
     */
    public function getHavingPlaceholderValues() {
        return $this->havingPlaceholderValues;
    }

    /**
     * Add a column to ORDER BY.
     *
     * @param  string $column column name
     * @param  string $order optional order direction, default ASC
     * @return QueryBuilder
     */
    public function orderBy($column, $order = "ASC") {
        $this->orderBy[] = array(
            'column' => $column,
            'order'  => $order
        );
        return $this;
    }

    /**
     * Get the ORDER BY portion of the query as a string.
     *
     * @param  bool $includeText optional include 'ORDER BY' text, default true
     * @return string ORDER BY portion of the query
     */
    public function getOrderByString($includeText = true) {
        $orderBy = "";

        foreach ($this->orderBy as $currentOrderBy) {
            $orderBy .= $currentOrderBy['column'] . " " . $currentOrderBy['order'] . ", ";
        }

        $orderBy = substr($orderBy, 0, -2);

        if ($includeText && !empty($orderBy)) {
            $orderBy = "ORDER BY " . $orderBy;
        }

        return $orderBy;
    }

    /**
     * Set the LIMIT on number of rows to return with optional offset.
     *
     * @param  int|string $limit number of rows to return
     * @param  int|string $offset optional row number to start at, default 0
     * @return QueryBuilder
     */
    public function limit($limit, $offset = 0) {
        $this->limit['limit'] = $limit;
        $this->limit['offset'] = $offset;

        return $this;
    }

    /**
     * Get the LIMIT on number of rows to return.
     *
     * @return int|string LIMIT on number of rows to return
     */
    public function getLimit() {
        return $this->limit['limit'];
    }

    /**
     * Get the LIMIT row number to start at.
     *
     * @return int|string LIMIT row number to start at
     */
    public function getLimitOffset() {
        return $this->limit['offset'];
    }

    /**
     * Get the LIMIT portion of the query as a string.
     *
     * @param  bool $includeText optional include 'LIMIT' text, default true
     * @return string LIMIT portion of the query
     */
    public function getLimitString($includeText = true) {
        $limit = "";

        if (!empty($this->limit)) {
            if(!empty($this->limit['offset'])) {
                $limit .= $this->limit['offset'] . ", " . $this->limit['limit'];
            }
            else {
                $limit .= $this->limit['limit'];
            }
        }

        if ($includeText && !empty($limit)) {
            $limit = "LIMIT " . $limit;
        }

        return $limit;
    }

    /**
     * Get the full query string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @throws Exception
     * @return string full query string
     */
    public function getQueryString($usePlaceholders = true) {
        $query = "";

        // SELECT. Only return the full query string if a SELECT value is set.
        if (!empty($this->select)) {
            $query .= $this->getSelectString();
            $query .= $this->getSelectCriteriaQueryString($usePlaceholders);
        }
        // INSERT
        if (!empty($this->insert)) {
            $query .= $this->getInsertString();
            $query .= $this->getInsertCriteriaQueryString($usePlaceholders);
        }
        // UPDATE
        if (!empty($this->update)) {
            $query .= $this->getUpdateString();
            $query .= $this->getUpdateCriteriaQueryString($usePlaceholders);
        }
        // DELETE
        if (!empty($this->delete)) {
            $query .= $this->getDeleteString();
            $query .= $this->getDeleteCriteriaQueryString($usePlaceholders);
        }

        return $query;
    }

    /**
     * Get the criteria query string for SELECT statements
     *
     * @param $usePlaceholders
     * @return string
     */
    private function getSelectCriteriaQueryString($usePlaceholders) {
        $query = "";

        if (!empty($this->from)) {
            $query .= " " . $this->getFromString();
        }

        if (!empty($this->where)) {
            $query .= " " . $this->getWhereString($usePlaceholders);
        }

        if (!empty($this->groupBy)) {
            $query .= " " . $this->getGroupByString();
        }

        if (!empty($this->having)) {
            $query .= " " . $this->getHavingString($usePlaceholders);
        }

        if (!empty($this->orderBy)) {
            $query .= " " . $this->getOrderByString();
        }

        if (!empty($this->limit)) {
            $query .= " " . $this->getLimitString();
        }

        return $query;
    }

    /**
     * Get the criteria query string for INSERT statements
     *
     * @param $usePlaceholders
     * @return string
     */
    private function getInsertCriteriaQueryString($usePlaceholders) {
        $query = "";

        if (!empty($this->set)) {
            $query .= " " . $this->getSetString($usePlaceholders);
        }

        if (!empty($this->limit)) {
            $query .= " " . $this->getLimitString();
        }

        return $query;
    }

    /**
     * Get the criteria query string for UPDATE statements
     *
     * @param $usePlaceholders
     * @throws Exception
     * @return string
     */
    private function getUpdateCriteriaQueryString($usePlaceholders) {
        $query = "";

        if (!empty($this->set)) {
            $query .= " " . $this->getSetString($usePlaceholders);
        }

        if (empty($this->where)) {
            throw new Exception("A WHERE statement is required for UPDATE operations");
        } else {
            $query .= " " . $this->getWhereString($usePlaceholders);
        }

        if (!empty($this->limit)) {
            $query .= " " . $this->getLimitString();
        }

        return $query;
    }

    /**
     * Get the criteria query string for DELETE statements
     *
     * @param $usePlaceholders
     * @throws Exception
     * @return string
     */
    private function getDeleteCriteriaQueryString($usePlaceholders) {
        $query = "";

        if (empty($this->where)) {
            throw new Exception("A WHERE statement is required for DELETE operations");
        } else {
            $query .= " " . $this->getWhereString($usePlaceholders);
        }

        if (!empty($this->limit)) {
            $query .= " " . $this->getLimitString();
        }

        return $query;
    }


    /**
     * Get all placeholder values when {@link QueryBuilder::getQueryString()}
     * is called with the parameter to use placeholder values.
     *
     * @return array all placeholder values
     */
    public function getPlaceholderValues() {

        return array_merge($this->getSetPlaceHolderValues(),
            $this->getWherePlaceholderValues(),
            $this->getHavingPlaceholderValues());
    }

    /**
     * Execute the query using the PDO database connection.
     *
     * @return PDOStatement|false executed query or false is failed
     */
    public function query() {
        $pdo = $this->getpdo();

        // If no PDO database connection is set, the query cannot be executed.
        if (!isset($pdo)) {
            return false;
        }

        $queryString = $this->getQueryString();

        // Only execute if a query is set.
        if (!empty($queryString)) {

            $PdoStatement = $pdo->prepare($queryString);
            $PdoStatement->execute($this->getPlaceholderValues());

            return $PdoStatement;
        }
        else {
            return false;
        }
    }

    /**
     * Shortcut to $this->query()->fetchAll(PDO::FETCH_OBJ)
     * @return [type]
     */
    public function fetchObjects() {
        $query = $this->query();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Shortcut to $this->query()->fetch(PDO::FETCH_OBJ)
     * @return [type]
     */
    public function fetchObject() {
        $query = $this->query();
        return $query->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get the full query string without value placeholders.
     *
     * @return string full query string
     */
    public function __toString() {
        return $this->getQueryString(false);
    }

}
