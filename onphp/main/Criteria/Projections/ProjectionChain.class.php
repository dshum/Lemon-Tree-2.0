<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: ProjectionChain.class.php 3888 2007-07-27 11:20:49Z voxus $ */

	/**
	 * @ingroup Projections
	**/
	final class ProjectionChain implements ObjectProjection
	{
		private $list = array();
		
		/**
		 * @return ProjectionChain
		**/
		public function add(ObjectProjection $projection, $name = null)
		{
			if ($name) {
				Assert::isFalse(isset($this->list[$name]));
				
				$this->list[$name] = $projection;
			} else {
				$this->list[] = $projection;
			}
			
			return $this;
		}
		
		/**
		 * @return JoinCapableQuery
		**/
		public function process(Criteria $criteria, JoinCapableQuery $query)
		{
			foreach ($this->list as $projection)
				$projection->process($criteria, $query);
			
			return $query;
		}
	}
?>