<?php
	/***************************************************************************
	 *   Copyright Denis Shumeev 2008-2009                                     *
	 *   denis@lemon-tree.ru                                                   *
	 ***************************************************************************/
	/* $Id$ */

	final class RussianTypograph extends BaseFilter
	{
		const TAG = "\xAC";
		const LAQUO = "\xAB";
		const RAQUO = "\xBB";
		const LDQUO = "\x84";
		const RDQUO = "\x93";
		const MDASH = "\x97";
		const NDASH = "\x96";
		const HELLIP = "\x85";

		/**
		 * @return RussianTypograph
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function apply($text)
		{
			$text = mb_convert_encoding($text, 'windows-1251', 'utf-8');

			$refs = array();
			$text = preg_replace('{<!--.*?-->}es', "\$this->xxxTypo('\\0',\$refs,'c')", $text);
			$privateTags = array("script", "style", "pre", "title");
			foreach($privateTags as $tag) {
				$text = preg_replace(
					'{<\s*'.$tag.'[\s>].*?<\s*/\s*'.$tag.'\s*>}ies',
					"\$this->xxxTypo('\\0',\$refs)", $text
				);
			}
			$text = preg_replace(
				'{<(?:[^\'"\>]+|".*?"|\'.*?\')+>}es',
				"\$this->xxxTypo('\\0',\$refs)", $text
			);

			$text = preg_replace('/&quot;/', "\"", $text);
			$text = preg_replace('/&nbsp;/', " ", $text);
			$text = preg_replace('/'.chr(160).'/', " ", $text);
			$text = preg_replace('/«/', "\"", $text);
			$text = preg_replace('/»/', "\"", $text);
			$text = preg_replace('/„/', "\"", $text);
			$text = preg_replace('/“/', "\"", $text);
			$text = preg_replace('/”/', "\"", $text);
			$text = preg_replace('/…/', "...", $text);
			$text = preg_replace('/–/', "-", $text);
			$text = preg_replace('/—/', "-", $text);

		    $text = preg_replace(
		    	'{(\s|^|\(|\{|\[|")"}s',
		    	"\\1".self::LAQUO, $text
		    );
		    $text = preg_replace(
		    	'{(\s|^|\(|\{|\[|")((?:'.self::TAG.'\d+'.self::TAG.')+)?"}s',
		    	"\\1\\2".self::LAQUO, $text
		    );
			$text = preg_replace(
				'{(\S)"}s',
				"\\1".self::RAQUO, $text
			);
			$text = preg_replace(
				'{(^|[^'.self::RAQUO.'])'.self::LAQUO.self::RAQUO.'}s',
				"\\1".self::LAQUO.self::LAQUO, $text
			);
			$text = preg_replace(
				'{(^|[^'.self::LAQUO.'])'.self::RAQUO.self::LAQUO.'}s',
				"\\1".self::RAQUO.self::RAQUO, $text
			);

			$i = 0;
			while(
				($i++ < 10)
				&& preg_match("{".self::LAQUO."([^".self::RAQUO."]*?)".self::LAQUO."}s", $text)
			) {
				$text = preg_replace(
					"{".self::LAQUO."([^".self::RAQUO."]*?)".self::LAQUO."(.*?)".self::RAQUO."}s",
					self::LAQUO."\\1".self::LDQUO."\\2".self::RDQUO, $text
				);
			}

			$i = 0;
			while(
				($i++ < 10)
				&& preg_match("{".self::RAQUO."([^".self::LAQUO."]*?)".self::RAQUO."}s", $text)
			) {
				$text = preg_replace(
					"{".self::RAQUO."([^".self::LAQUO."]*?)".self::RAQUO."}s",
					self::RDQUO."\\1".self::RAQUO, $text
				);
			}

			$text = preg_replace('{(^|\s|'.self::TAG.')-(\s)}s', "\\1".self::MDASH."\\2",$text);
			$text = preg_replace('{ '.self::MDASH.'}', "&nbsp;".self::MDASH, $text);
//			$text = preg_replace('{(\d)-(\d)}s', "\\1".self::NDASH."\\2", $text);
			$text = preg_replace('{\.\.\.}s', self::HELLIP, $text);

			$text = preg_replace('/\"/', "&quot;", $text);
			$text = preg_replace('{\'}s', "&#146;", $text);

			while(preg_match('{'.self::TAG.'\d'.self::TAG.'}', $text)) {
				$text = preg_replace('{'.self::TAG.'(\d+)'.self::TAG.'}e', "\$refs[\\1]", $text);
			}

			$text = preg_replace('{'.self::LAQUO.'}s', "&laquo;", $text);
			$text = preg_replace('{'.self::RAQUO.'}s', "&raquo;", $text);
			$text = preg_replace('{'.self::LDQUO.'}s', "&bdquo;", $text);
			$text = preg_replace('{'.self::RDQUO.'}s', "&ldquo;", $text);
			$text = preg_replace('{'.self::MDASH.'}s', "&#151;", $text);
			$text = preg_replace('{'.self::NDASH.'}s', "&#150;", $text);
			$text = preg_replace('{'.self::HELLIP.'}s', "&hellip;", $text);

			$text = preg_replace('{\(c\)}i', "&copy;", $text);
			$text = preg_replace('{\(r\)}i', "&reg;", $text);
			$text = preg_replace('{\(tm\)}i', "&trade;", $text);
			$text = preg_replace('{№}s', "&#8470;", $text);

			$text = mb_convert_encoding($text, 'utf-8', 'windows-1251');

			return $text;
		}

		private function xxxTypo($x, &$refs)
		{
			$refs[] = stripslashes($x);

			return self::TAG.(sizeof($refs) - 1).self::TAG;
		}
	}
?>