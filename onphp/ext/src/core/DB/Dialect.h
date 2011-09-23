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

#ifndef ONPHP_CORE_DIALECT_H
#define ONPHP_CORE_DIALECT_H

#define ONPHP_ARGINFO_DIALECT \
	ZEND_BEGIN_ARG_INFO(arginfo_dialect, 0) \
		ZEND_ARG_OBJ_INFO(0, dialect, Dialect, 0) \
	ZEND_END_ARG_INFO()

#define ONPHP_ARGINFO_DBCOLUMN \
	ZEND_BEGIN_ARG_INFO(arginfo_dbcolumn, 0) \
		ZEND_ARG_OBJ_INFO(0, column, DBColumn, 0) \
	ZEND_END_ARG_INFO();

#define ONPHP_ARGINFO_DATATYPE \
	ZEND_BEGIN_ARG_INFO(arginfo_datatype, 0) \
		ZEND_ARG_OBJ_INFO(0, type, DataType, 0) \
	ZEND_END_ARG_INFO();

ONPHP_STANDART_CLASS(Dialect);

#endif /* ONPHP_CORE_DIALECT_H */
