<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.0.8.99 at 2009-10-14 12:05:33                      *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/
/* $Id$ */

	final class UserActionDAO extends AutoUserActionDAO
	{
		public function getTable()
		{
			return TABLE_PREFIX.parent::getTable();
		}

		public function getSequence()
		{
			return TABLE_PREFIX.parent::getSequence();
		}

		public function getFirstDate()
		{
			$firstAction =
				Criteria::create($this)->
				addOrder(new DBField('date', $this->getTable()))->
				setLimit(1)->
				get();

			return
				$firstAction
				? $firstAction->getDate()
				: Date::makeToday();
		}

		public function dropByUser(User $user)
		{
			$db = DBPool::me()->getByDao($this);

			$query =
				OSQL::delete()->
				from($this->getTable())->
				where(
					Expression::eqId(
						new DBField('user_id', $this->getTable()),
						$user
					)
				);

			$db->query($query);

			$this->uncacheLists();
		}
	}
?>