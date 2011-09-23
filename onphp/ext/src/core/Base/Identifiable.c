/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

#include "onphp.h"

static ONPHP_ARGINFO_ONE;

zend_function_entry onphp_funcs_Identifiable[] = {
	ONPHP_ABSTRACT_ME(Identifiable, getId, NULL, ZEND_ACC_PUBLIC)
	ONPHP_ABSTRACT_ME(Identifiable, setId, arginfo_one, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
