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
/* $Id: DateUtils.class.php 5479 2008-09-02 16:27:53Z sherman $ */

	/**
	 * Utilities for playing with dates and time
	 * 
	 * @ingroup Utils
	**/
	final class DateUtils extends StaticFactory
	{
		public static function getAgeByBirthDate(
			Date $birthDate, /* Date*/ $actualDate = null
		)
		{
			if ($actualDate)
				Assert::isInstance($actualDate, 'Date');
			else
				$actualDate = Date::makeToday();
			
			$result = $actualDate->getYear() - $birthDate->getYear();
			
			if (
				$actualDate->getMonth() < $birthDate->getMonth()
				|| (
					$actualDate->getMonth() == $birthDate->getMonth()
					&& $actualDate->getDay() < $birthDate->getDay()
				)
			) {
					// - Happy birthday?
					// - Happy go to hell. Not yet in this year.
					--$result;
			}
			
			return $result;
		}
		
		public static function makeFirstDayOfMonth(Date $date)
		{
			return
				Timestamp::create(
					mktime(0, 0, 0, $date->getMonth(), 1, $date->getYear())
				);
		}
		
		public static function makeLastDayOfMonth(Date $date)
		{
			return
				Timestamp::create(
					mktime(0, 0, 0, $date->getMonth() + 1, 0, $date->getYear())
				);
		}
	}
?>