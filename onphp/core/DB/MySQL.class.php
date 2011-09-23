<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: MySQL.class.php 5018 2008-03-29 01:42:27Z serg $ */

	/**
	 * MySQL DB connector.
	 *
	 * @see http://www.mysql.com/
	 * @see http://www.php.net/mysql
	 *
	 * @ingroup DB
	**/
	final class MySQL extends Sequenceless
	{
		/**
		 * @return MyDialect
		**/
		public static function getDialect()
		{
			return MyDialect::me();
		}

		/**
		 * @return MySQL
		**/
		public function setDbEncoding()
		{
			mysql_query("SET NAMES '{$this->encoding}'", $this->link);

			return $this;
		}

		/**
		 * @return MySQL
		**/
		public function connect()
		{
			$this->link =
				$this->persistent
					?
						mysql_pconnect(
							$this->hostname,
							$this->username,
							$this->password,
							// 2 == CLIENT_FOUND_ROWS
							2
						)
					:
						mysql_connect(
							$this->hostname,
							$this->username,
							$this->password,
							true,
							// 2 == CLIENT_FOUND_ROWS
							2
						);

			if (
				!$this->link
				|| (
					$this->basename
					&& !mysql_select_db($this->basename, $this->link)
				)
			)
				throw new DatabaseException(
					'can not connect to MySQL server: '.mysql_error($this->link),
					mysql_errno($this->link)
				);

			if ($this->encoding)
				$this->setDbEncoding();

			return $this;
		}

		/**
		 * @return MySQL
		**/
		public function disconnect()
		{
			if ($this->isConnected())
				mysql_close($this->link);

			return $this;
		}

		/**
		 * Same as query, but returns number of
		 * affected rows in insert/update queries
		**/
		public function queryCount(Query $query)
		{
			$this->queryNull($query);

			return mysql_affected_rows($this->link);
		}

		public function queryRow(Query $query)
		{
			$res = $this->query($query);

			if ($this->checkSingle($res))
				return mysql_fetch_assoc($res);
			else
				return null;
		}

		public function queryColumn(Query $query)
		{
			$res = $this->query($query);

			if ($res) {
				$array = array();

				while ($row = mysql_fetch_row($res))
					$array[] = $row[0];

				return $array;
			} else
				return null;
		}

		public function querySet(Query $query)
		{
			$res = $this->query($query);

			if ($res) {
				$array = array();

				while ($row = mysql_fetch_assoc($res))
					$array[] = $row;

				return $array;
			} else
				return null;
		}

		public function queryRaw($queryString)
		{
			list($usec0, $sec0) = explode(' ', microtime());

			if (!$result = mysql_query($queryString, $this->link)) {

				$code = mysql_errno($this->link);

				if ($code == 1062)
					$e = 'DuplicateObjectException';
				else
					$e = 'DatabaseException';

				throw new $e(
					mysql_error($this->link).' - '.$queryString,
					$code
				);
			}

			list($usec1, $sec1) = explode(' ', microtime());
			$time = (float)$sec1 + (float)$usec1 - (float)$sec0 - (float)$usec0;

			Site::setQuery($queryString, $time);

			return $result;
		}

		public function getTableInfo($table)
		{
			static $types = array(
				'tinyint'	=> DataType::SMALLINT,
				'int'		=> DataType::INTEGER,
				'bigint'	=> DataType::BIGINT,

				'varchar'	=> DataType::VARCHAR,
				'text'		=> DataType::TEXT,

				'timestamp' => DataType::TIMESTAMP,
				'date'		=> DataType::DATE
			);

			$result = $this->queryRaw('SHOW COLUMNS FROM '.$table);

			if (!(mysql_num_rows($result) > 0)) {
				throw new ObjectNotFoundException(
					"no info for table '{$table}'"
				);
			}

			$table = new DBTable($table);

			while ($row = mysql_fetch_assoc($result)) {
				$name = strtolower($row['Field']);
				$matches = array();
				$info = array('type' => null, 'extra' => null);
				if (
					preg_match(
						'~(\w+)(\((\d+?)\)){0,1}\s*(\w*)~',
						strtolower($row['Type']),
						$matches
					)
				) {
					$info['type'] = $matches[1];
					$info['size'] = $matches[3];
					$info['extra'] = $matches[4];
				}

				Assert::isTrue(
					array_key_exists($info['type'], $types),

					'unknown type "'
					.$types[$info['type']]
					.'" found in column "'.$name.'"'
				);

				if (empty($types[$info['type']]))
					continue;

				$column = DBColumn::create(
					DataType::create($types[$info['type']])->
						setUnsigned(
							strtolower($info['extra']) == 'unsigned'
						)->
						setNull(strtolower($row['Null']) == 'yes'),

					$name
				)->
				setAutoincrement(strtolower($row['Extra']) == 'auto_increment')->
				setPrimaryKey(strtolower($row['Key']) == 'pri');

				if ($row['Default'])
					$column->setDefault($row['Default']);

				$table->addColumn($column);
			}

			return $table;
		}

		public function hasQueue()
		{
			return false;
		}

		protected function getInsertId()
		{
			return mysql_insert_id($this->link);
		}

		private function checkSingle($result)
		{
			if (mysql_num_rows($result) > 1)
				throw new TooManyRowsException(
					'query returned too many rows (we need only one)'
				);

			return $result;
		}
	}
?>