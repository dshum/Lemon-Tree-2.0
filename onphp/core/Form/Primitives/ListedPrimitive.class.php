<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: ListedPrimitive.class.php 5105 2008-05-02 10:35:01Z voxus $ */

	/**
	 * @ingroup Primitives
	 * @ingroup Module
	**/
	interface ListedPrimitive
	{
		/// @return plain array of possible primitive choices
		public function getList();
		public function setList(array $list);
		
		public function getChoiceValue();
		public function getActualChoiceValue();
	}
?>