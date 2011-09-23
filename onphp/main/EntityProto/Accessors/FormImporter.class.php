<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: FormImporter.class.php 4965 2008-03-21 17:12:14Z dedmajor $ */

	final class FormImporter extends FormMutator
	{
		public function set($name, $value)
		{
			if (!isset($this->mapping[$name]))
				throw new WrongArgumentException(
					"knows nothing about property '{$name}'"
				);
			
			$primitive = $this->mapping[$name];
			
			if ($primitive instanceof PrimitiveForm)
				// inner form(s) has been already imported
				$this->object->importValue($primitive->getName(), $value);
				
			else
				$this->object->importOne(
					$primitive->getName(),
					array($primitive->getName() => $value)
				);
			
			return $this;
		}
	}
?>