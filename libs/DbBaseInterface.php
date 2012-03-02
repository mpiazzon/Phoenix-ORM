<?php
/*
 * This file is part of the Phoenix package
 *
 * (c) 2011 Martin Piazzon
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
interface DbBaseInterface
{
    public function connect ($config);
    public function query ($sql);
    public function fetchArray ($resultQuery = '', $opt = '');
    public function close ();
    public function numRows ($resultQuery = '');
    public function error ($err = '');
    public function noError ();
    public function inQuery ($sql);
    public function insert ($table, $fields, $values);
    public function update ($table, $fields, $values, $where_condition = NULL);
    public function delete ($table, $where_condition);
    public function listTables ();
    public function describeTable ($table);
    public function lastInsertId ($table = '', $primary_key = '');
    public function dropTable ($table, $if_exists = false);
    public function tableExists ($table);
}