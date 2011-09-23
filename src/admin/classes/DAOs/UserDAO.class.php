<?php
/*****************************************************************************
 *   Copyright (C) 2006-2007, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-0.10.8 at 2008-02-13 18:07:03                        *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/
/* $Id$ */

	final class UserDAO extends AutoUserDAO
	{
		public function getTable()
		{
			return TABLE_PREFIX.parent::getTable();
		}

		public function getSequence()
		{
			return TABLE_PREFIX.parent::getSequence();
		}

		public function getCountByGroup(Group $group)
		{
			$query =
				OSQL::select()->
				get(
					new SQLFunction('COUNT', 'id'),
					'count'

				)->
				from(
					$this->getTable()
				)->
				where(
					Expression::eqId(
						new DBField('group_id', $this->getTable()),
						$group
					)
				);

			$custom = $this->getCustom($query);

			return $custom['count'];
		}
	}
?>