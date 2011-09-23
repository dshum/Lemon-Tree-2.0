<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: MappableObjectProjection.class.php 4181 2007-09-12 06:32:11Z myserg $ */

	/**
	 * @ingroup Projections
	**/
	final class MappableObjectProjection implements ObjectProjection
	{
		private $mappable	= null;
		private $alias		= null;
		
		public function __construct(MappableObject $mappable, $alias = null)
		{
			$this->mappable = $mappable;
			$this->alias = $alias;
		}
		
		/**
		 * @return JoinCapableQuery
		**/
		public function process(Criteria $criteria, JoinCapableQuery $query)
		{
			return $query->get(
				$this->mappable->toMapped($criteria->getDao(), $query),
				$this->alias
			);
		}
	}
?>