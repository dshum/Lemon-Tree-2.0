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
/* $Id: CountProjection.class.php 3888 2007-07-27 11:20:49Z voxus $ */

	/**
	 * @ingroup Projections
	**/
	abstract class CountProjection extends BaseProjection
	{
		/**
		 * @return JoinCapableQuery
		**/
		public function process(Criteria $criteria, JoinCapableQuery $query)
		{
			return $query->get($this->getFunction($criteria, $query));
		}
		
		/**
		 * @return SQLFunction
		**/
		protected function getFunction(
			Criteria $criteria,
			JoinCapableQuery $query
		)
		{
			Assert::isNotNull($this->property);
			
			return
				SQLFunction::create(
					'count',
					$this->property
						? $criteria->getDao()->guessAtom($this->property, $query)
						: $criteria->getDao()->getIdName()
				)->
				setAlias($this->alias);
		}
	}
?>