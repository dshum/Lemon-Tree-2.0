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
/* $Id: GroupByPropertyProjection.class.php 3888 2007-07-27 11:20:49Z voxus $ */

	/**
	 * @ingroup Projections
	**/
	final class GroupByPropertyProjection extends BaseProjection
	{
		/**
		 * @return JoinCapableQuery
		**/
		public function process(Criteria $criteria, JoinCapableQuery $query)
		{
			Assert::isNotNull($this->property);
			
			return
				$query->groupBy(
					$criteria->getDao()->guessAtom($this->property, $query)
				);
		}
	}
?>