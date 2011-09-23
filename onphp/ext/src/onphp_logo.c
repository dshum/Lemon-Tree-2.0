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

// http://onphp.org/pix/logo.png

#define ONPHP_LOGO_GUID "PHPE1C85E90-600D-C0DE-DADA-B622A1EF5492"

#define ONPHP_EXCEPTIONS_LIST "\
BaseException, \
BusinessLogicException, \
ClassNotFoundException, \
DatabaseException, \
DuplicateObjectException, \
FileNotFoundException, \
IOException, \
IOTimedOutException, \
MissingElementException, \
NetworkException, \
ObjectNotFoundException, \
SecurityException, \
TooManyRowsException, \
UnimplementedFeatureException, \
UnsupportedMethodException, \
WrongArgumentException, \
WrongStateException."

#define ONPHP_INTERFACES_LIST "\
Aliased, \
DAOConnected, \
DialectString, \
FullTextDAO, \
Identifiable, \
Instantiatable, \
ListedPrimitive, \
LogicalObject, \
Named, \
Prototyped, \
Query, \
SegmentHandler, \
Stringable, \
SQLTableName, \
ViewResolver, \
LogicalObject."

#define ONPHP_CLASSES_LIST "\
BaseFilter, \
BasePrimitive, \
Castable, \
Cdata, \
ComplexPrimitive, \
Dialect, \
DBBinary, \
DBField, \
DBValue, \
DropTableQuery, \
Enumeration, \
ExtractPart, \
FieldTable, \
FiltrablePrimitive, \
Filtrator, \
Form, \
FormField, \
FromTable, \
FullText, \
GroupBy, \
Joiner, \
IdentifiableObject, \
Identifier, \
ImaginaryDialect, \
NamedObject, \
OrderBy, \
PlainForm, \
PrimitiveNumber, \
Query, \
QueryIdentification, \
QuerySkeleton, \
RegulatedForm, \
RangedPrimitive, \
SelectField, \
SgmlEndTag, \
SgmlTag, \
SgmlToken, \
Singleton, \
StaticFactory, \
Ternary."

static const unsigned char onphp_logo[] = {
 137,   80,   78,   71,   13,   10,   26,   10,    0,    0,    0,   13,   73,
  72,   68,   82,    0,    0,    0,  202,    0,    0,    0,   93,    8,    6,
   0,    0,    0,  228,    6,  249,   83,    0,    0,    0,    6,   98,   75,
  71,   68,    0,  255,    0,  255,    0,  255,  160,  189,  167,  147,    0,
   0,    0,    9,  112,   72,   89,  115,    0,    0,   11,   19,    0,    0,
  11,   19,    1,    0,  154,  156,   24,    0,    0,    0,    7,  116,   73,
  77,   69,    7,  214,    4,    1,   21,   26,   15,  127,  240,   18,  230,
   0,    0,    6,  251,   73,   68,   65,   84,  120,  218,  237,  157,  219,
 113,  226,   72,   20,  134,  255,  157,  218,  119,  145,    1,  100,   32,
  50,  128,  141,    0,   38,    2,  216,    8,  240,   68,   32,   54,    2,
 179,   17,   88,   19,    1,   76,    4,   48,   17,   24,   34,   48,  142,
   0,   28,  129,  247,  193,   71,  229,  118,  111,   75,  125,   81,   11,
  36,  248,  191,   42,   85,  217,   32,  245,   69,   58,  167,  207,   69,
 221,   13,   64,    8,   33,  132,   16,   66,    8,   33,  132,   16,   66,
   8,   33,  132,   16,   66,    8,   33,  132,   16,   66,    8,   33,  132,
  16,   66,    8,   33,  132,   16,   66,    8,   33,  132,   16,   66,    8,
  33,  132,   16,   66,    8,   33,  132,   16,   66,    8,   33,  132,   16,
  66,  200,   61,  146,    2,   72,   58,  212,  222,    4,  192,    2,  192,
  44,   66,   89,  163,   46,   63,  184,   63,   40,  187,  141,   40,  195,
  16,  192,   28,  192,  184,  226,  188,   35,  128,   92,  249,  127,  108,
  57,   63,  151,  107,  142,    0,  126,   90,  132,  123,   37,  245,  187,
  48,    4,  112,  208,   62,  123,  114,  188,  126,   39,  199,   70,  218,
  53,  149,   62,  216,  174,   93,   42,  127,   15,   44,  231,   23,  117,
   0,  192,   63,   20,  175,  219,   98,    2,  224,    4,  224,  189,  161,
  99,   11,   32,    3,  208,   47,   81,  148,  103,  199,  114,   78,   82,
 142,  106,  229,   30,   61,  174,  127,   23,  139,  147,  202,  181,  125,
   0,   47,   13,  246,  251,   89,  148,   56,  165,   69,  185,   45,  203,
  50,   85,  254,  239,    1,  120,  168,   56,  127,    3,   96,  239,   80,
 110,   79,   70,  224,  158,   98,  137,  126,   27,  148,  101,   40,  163,
 245,   74,   57,   23,    0,  206,   98,  157,  206,   82,  231,  193,   82,
 223,   66,  234,  152,  106,  159,  175,  100,  164,  255,  101,  168,   91,
 239,  231,  131,  214,    6,  149,  189,  180,  195,  133,  185,  244,  169,
 176,   74,  180,   48,   55,   76,  166,  141,  146,   79,  129,  229,   44,
 196,   34,  156,   44,   35,  172,   94,  223,   40,   66,  187,   67,  218,
  60,   50,   88,  135,  144,  120,  109,  162,   88,  188,   25,  197,  233,
 118,   73,   52,  183,  172,   78,  144,  155,  138,   27,  118,  178,    8,
 221,  115,  164,  250,  234,  150,  177,   85,  202,  200,  106,  180,  163,
  47,   46,   98,  221,  254,   56,  243,  141,  114,  123,  113,  222,   28,
  93,   44,   23,   14,  226,  254,  216,  220,  186,  179,  167,  123,  215,
  20,  187,   72,  229,  188,   42,  238,  218,  146,  138,   66,   92,  216,
 139,   34,   60,   56,    4,  185,  123,   81,  212,   91,  224,   40,  199,
 248,   18,   46,   24,   21,  165,  251,  252,   18,    5,  232,  161,   58,
 189,   28,   75,   56,  219,  194,   43,   62,  211,  235,  211,  166,   43,
 251,  243,    6,  253,  255,   97,  201,  119,  191,   29,  175,   45,  174,
 207,   47,   52,  250,  198,  112,  133,  114,   81,  146,   97,  195,  109,
 205,   35,  186,   58,  251,  136,  237,   25,  222,  139,  162,  140,   34,
 185,   10,   85,   47,  218,  246,  242,  221,  193,   80,  247,   18,   31,
 105,  199,  129,  242,  249,   82,  174,  217,    3,  248,  209,  112,  204,
  18,  107,  164,  159,    3,  248,  187,   35,  131,  218,   57,  146,   85,
 129,   60,  183,  145,  195,   96,  216,   89,   69,  153,   88,   70,  132,
 163,   34,  224,   54,  129,   74,   44,   38,  120,   40,  193,  228,  222,
 240,  185,   41,  199,   95,  184,   50,  197,   72,   61,  109,  200,  194,
 244,  149,    7,  126,   79,   12,  154,   20,  236,   91,   97,    6,  191,
 183,  191,  207,   14,  129,  234,   12,  205,  189,   17,  174,  147,  247,
  55,   41,  116,  172,  244,  176,  106,   21,  139,  242,   76,   20,  105,
 217,  172,  102,   61,   47,  104,   71,  122,   88,   37,  230,  125,  108,
  13,  106,  254,   59,  228,   88,   91,  132,  117,   38,  231,   60,   55,
 164,   44,   89,  100,  161,  142,  245,  128,   11,    1,  124,  180,  124,
 127,   66,  248,  244,  143,   44,   66,  155,   99,  223,  203,   76,   25,
 196,  110,   38,   70,   73,  240,  145,  251,   30,  150,  248,  171,   43,
 229,  255,  105,  201,  121,   83,  113,  159,  198,   37,  110,  208,   79,
 124,   78,   24,   92,  136,   91,  215,  243,   12,   14,  213,  204,  206,
  88,  203,   36,   21,  177,   76,  155,  226,  128,  153,  210,  198,  163,
 229,  220,  158,  231,  253,  104,  251,  160,  187,  116,  236,  119,  167,
  58,   85,   53,  202,  167,   22,   51,  173,   31,   47,  112,  203,  157,
  39,  142,  150,  226,  169,  194,   82,  153,   44,  224,  162,   69,   22,
 229,   81,  185,   39,  125,    7,  151,  103,   27,   24,   75,  110,   91,
 102,   81,   38,  151,  116,  187,   46,  101,   81,  242,   18,   11,  177,
 145,   81,  193,   52,   49,  239,   47,   69,  216,  119,  218,  245,    3,
  41,  115,  143,  234,   73,  125,  111,  138,    5,   42,   75,   22,  228,
 168,  158,   92,  247,   67,  234,   30,  107,   35,  115,   27,   24,  225,
 243,  141,  252,  216,   49,   41,   48,   22,  165,  202,   61,  234,   89,
 182,  112,  224,   45,   60,  144,  249,   45,   37,    5,  202,  166,  138,
  39,   30,   55,  102,   27,   56,  178,  111,   61,   45,  153,   75,   25,
 207,   53,  133,  219,   39,   73,   48,  210,  190,  239,   75,  191,  213,
  54,  157,  106,  220,  131,  144,   35,  134,   69,  177,  197,   75,  169,
 102,   33,   19,  197,  178,  157,    2,  158,   97,  103,   98,   20,  125,
  36,  247,   73,  183,  190,  202,  249,  103,   67,  204,  242,  111,   96,
  27,   30,   96,  159,   98,   94,   70,  157,   23,   92,   61,   67,   89,
  27,  152,  231,   65,   13,  100,  196,  220,   41,  223,  235,  113,  211,
  25,   97,  111,  228,  247,  176,  191,  203,  232,   33,  222,  203,  188,
 196,   80,  246,  166,  194,  178,   45,  241,  117,  113,   91,  113,   47,
  84,  230,   53,  158,   97,  235,   48,  165,  109,  103,   17,   70,  164,
 226,  152,    4,  140,  166,  182,  236,  153,  254,  128,   77,  101,  132,
  10,   75,  134,  184,   41,  235,  212,  179,  253,   47,   37,   22,   33,
 171,   24,  217,   71,   18,  199,  213,  177,   40,  147,  136,  253,  126,
 193,  141,   77,  177,   79,   96,   94,  241,   22,  106,  186,   79,   37,
  66,  239,  171,   40,  190,  196,   82,   20,  147,  235,   21,   34,   36,
 107,  143,  123,  152,  106,  137,  148,  167,    8,  237,   30,   69,   28,
 232,   92,  143,  147,   60,  135,    9,  174,  176,  239,   64,  211,  174,
 215,    3,  190,   78,   11,   41,   76,  126,  104,  240,   53,  199,  255,
  87,  195,  217,    2,  235,  166,    2,  239,   52,  162,  217,  127,  192,
 231,  140,  129,  113,  137,   43,   85,   76,  145,  247,  157,  214,  115,
 208,   92,  172,   60,  176,  141,   77,    4,  204,  185,  197,  181,   42,
 228,  165,   88,  147,  127,  179,   51,   24,  178,    0,   87,  201,  119,
 116,  183,  165,   59,  223,   27,  178,   40,  161,   65,  164,  110,   81,
 178,   11,   60,  135,   24,  169,  221,   89,  100,  215,  121,  219,   37,
  65,  190,  198,   52,  251,  186,  147,  225,  118,  134,   96,   56,  241,
  56,   63,   22,  177,  172,  201,  174,   35,  178,   50,  208,  146,   40,
 247,  210,  239,  171,   41,   74,  108,  124,   93,   17,  223,  233,  221,
   9,   72,  236,  193,  174,  115,  124,  187,  194,   13,  157,   94,  185,
 207,   27,  207,  243,  223,   58,  174,   64,  169,   22,  243,  204,   35,
 148,  121,  164,  162,  196,   23,  202,  179,  193,   85,  170,  131,  175,
 162,   13,   46,  224,  138,  248,    6,  238,  151,  164,  103,    8,  160,
  99,  151,  233,  194,  130,  138,   82,  206,   43,  190,   78,  118,   44,
  20,  165,  206,  219,   84,   93,  209,   86,   87,   80,  148,  115,  141,
  24,  101,  138,  238,   51,  188,  183,  126,   95,   35,   70,  233,   33,
 124,  238,   80,   98,  136,   55,  126,   93,  161,   15,   57,  238,  155,
 221,  189,  117,  248,  219,  149,  110,  234,   20,  254,  105,  202,  196,
  32,  160,  215,  122,   96,  231,   59,   84,  178,   65,  205,  251,  158,
  83,   81,  170,  249,   93,   34,   88,   62,  126,  110,   95,   30,  206,
  84,   19,  214,   85,    7,  239,  121,   23,    3,  225,   52,   66,   18,
 224,   72,   69,    9,  243,   79,  115,  148,  175,  200,   51,  157,   59,
  52,   36,   10,  108,  111,  106,   71,   37,  254,  117,  255,   10,   62,
 122,  211,  201,  133,   50,  198,   13,    4,  239,  243,   14,  244,  187,
 179,  100,   48,  207,  213,  218,   86,    4,  247,  125,  124,  157,  140,
 167,  206,  119,  178,  141,  128,   11,  216,  119,   97,  119,   73,  241,
 150,  237,   76,   31,   50,  231,  200,  180,  144,  172,  169,   69,   71,
   9,   62,  222,  160,  235,  109,   63,    5,  148,  101,  186,  151,  190,
   9,   25,  125,  225,   94,  214,   37,  225,  189,  244,  110,  246,  143,
  48,  167,   71,  207,   18,  152,  175,   20,   55,  109,   88,  225,   90,
  77,   75,  130,  248,   98,   65,  143,  107,  134,  229,   40,  245,  126,
  55,  124,  183,  134,  125,   83,  185,  163,   28,  103,  173,  140,   98,
  65,  149,   75,   59,  202,  118,  115,   63,   35,  108,    9,   65,  177,
 139,  254,  180,  194,  242,  109,  148,  122,   15,   14,   10,  183,   55,
  88,    4,  117,   87,  252,   21,   62,  222,   55,  245,  165,  223,   46,
  41,  240,   42,  215,  185,   40,  239,  174,  121,   66,  189,  223,    5,
 169,   26,  129,  155,  252,  109,   14,  151,  229,  196,   64,  220,  221,
  96,  108,  155,  111,  155,  172,   95,  232,  116,  253,   76,  142,  208,
 141,   57,   98,  255,   38,  204,    8,  196,   91,  152,   92,  119,   15,
  89,   71,   16,  148,  117,   13,   37,   46,  118,  151,  143,  169,  128,
 147,  134,   21,  101,  109,  176,   72,  161,    3,   69,  204,  126,   63,
  82,   77,   62,  221,  164,  204,   98,    5,   76,  191,    8,  229,   34,
  44,  190,  123,  134,   37,   14,   62,  185,  171,  192,  165,   17,  133,
  38,  196,  143,   95,  120,  140,  236,  101,   11,  216,  124,  247,   93,
 123,   68,  220,   69,  105,  107,  180,  108,  138,   80,   91,  126,  113,
 107,   97,  200,  172,  236,   80,  111,   13,  196,  196,  146,  157,  114,
 121,   89,  105,   43,   67,  141,   85,  126,   26,  124,  251,   97,   64,
  31,   98,  109,   13,   90,  220,  211,   24,   47,  101,   19,  137,   81,
  14,  134,  207,   97,  136,   39,   38,    1,  117,  166,  114,   31,  239,
  62,   54,   33,  132,   16,   66,    8,   33,  132,   16,   66,    8,   33,
 132,   16,   66,    8,   33,  132,   16,   66,    8,   33,  132,   16,   66,
   8,   33,  132,   16,   66,    8,   33,  132,   16,   66,    8,   33,  132,
  16,   66,    8,   33,  132,   16,   66,    8,   33,  132,   16,   66,    8,
  33,  132,   16,   66,    8,   33,  164,   21,  252,    7,  230,   69,   62,
 142,   22,  141,  109,  132,    0,    0,    0,    0,   73,   69,   78,   68,
 174,   66,   96,  130
};
