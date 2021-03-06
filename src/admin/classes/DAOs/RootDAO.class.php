<?php
/*****************************************************************************
 *   Copyright (C) 2006-2008, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1 at 2008-09-25 13:26:14                           *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/
/* $Id$ */

	class RootDAO extends AutoRootDAO
	{
		public function getTable()
		{
			return TABLE_PREFIX.parent::getTable();
		}

		public function getSequence()
		{
			return TABLE_PREFIX.parent::getSequence();
		}

		public function getById($id)
		{
			switch($id) {
				case Root::RootID:
					return
						Root::create()->
						setId(Root::RootID)->
						setElementName('Корень сайта')->
						setStatus('root');
				case Root::TrashID:
					return
						Root::create()->
						setId(Root::TrashID)->
						setElementName('Корзина')->
						setStatus('trash');
				case Root::HellID:
					return
						Root::create()->
						setId(Root::HellID)->
						setElementName('Пекло')->
						setStatus('hell');
				default:
					throw new ObjectNotFoundException();
			}
		}
	}
?>