<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2009                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	final class MailUtils
	{
		private $model = null;
		private $view = null;
		private $encoding = null;
		private $attachments = array();

		private $body = null;

		private $header = array(
			'Content-type' => 'text/plain',
			'To' => null,
			'Cc' => null,
			'Bcc' => null,
			'From' => null,
			'Reply-to' => null,
			'Subject' => null,
		);

		public static function create()
		{
			return new self;
		}

		public function setModel(Model $model)
		{
			$this->model = $model;

			return $this;
		}

		public function setView($view)
		{
			$this->view = $view;

			return $this;
		}

		public function setEncoding($encoding)
		{
			$this->encoding = $encoding;

			return $this;
		}

		public function addAttachment(
			$path,
			$name,
			$description,
			$id,
			$inline = false,
			$mimetype = 'application/octet-stream'
		)
		{
			if(!$path || !is_readable($path)) return $this;

			$name = $name ? $name : basename($path);
			$id = $id ? $id : $name;
			$part =
				MimePart::create()->
				setEncoding(MailEncoding::base64())->
				loadBodyFromFile($path)->
				setContentType($mimetype.'; name="'.$name.'"')->
				setContentId($id)->
				setFilename($name)->
				setDescription($description)->
				setInline($inline);
			$this->attachments[] = $part;

			return $this;
		}

		private function encodeAddress($address, $encoding)
		{
			if(strpos($address, ' ') !== false) {
				$list = explode(' ', $address);
				$email = array_pop($list);
				$name = implode(' ', $list);
				$name = trim($name, '"');
				$name = mb_convert_encoding($name, $encoding);
				$name =
					"=?".$encoding."?B?"
					.base64_encode($name)
					."?=";
				$email = trim($email, '<>');
				$address = $name.' <'.$email.'>';
			}

			return $address;
		}

		public function send()
		{
			$this->prepareContext();

			if(!isset($this->header['To'])) {
				throw new WrongArgumentException('mail to: is not specified');
			}

			$contentType = $this->header['Content-type'];

			$siteEncoding = mb_get_info('internal_encoding');

			$to = $this->header['To'];
			$cc = $this->header['Cc'];
			$bcc = $this->header['Bcc'];
			$from = $this->header['From'];
			$replyTo = $this->header['Reply-to'];

			if(!$this->encoding || $this->encoding == $siteEncoding) {

				$encoding = $siteEncoding;

				$subject =
					"=?".$encoding."?B?"
					.base64_encode($this->header['Subject'])
					."?=";

				$body = $this->body;

			} else {

				$encoding = $this->encoding;

				$body = $this->body;

				if(
					!mb_check_encoding($body, $siteEncoding)
					&& mb_check_encoding($body, 'Windows-1251')
				) {
					$body = mb_convert_encoding($body, $siteEncoding, 'Windows-1251');
				}

				$subject =
					"=?".$encoding."?B?"
					.base64_encode(
						iconv(
							$siteEncoding,
							$encoding.'//IGNORE',
							$this->header['Subject']
						)
					)."?=";

				$body = iconv(
					$siteEncoding,
					$encoding.'//IGNORE',
					$body
				);
			}

			$to = $this->encodeAddress($to, $encoding);
			$cc = $this->encodeAddress($cc, $encoding);
			$bcc = $this->encodeAddress($bcc, $encoding);
			$from = $this->encodeAddress($from, $encoding);
			$replyTo = $this->encodeAddress($replyTo, $encoding);

			$headers = null;

			if($from) {
				$headers .= 'From: '.$from.EOL;
				$headers .= 'Return-Path: '.$from.EOL;
			}

			if($replyTo) {
				$headers .= 'Reply-To: '.$replyTo.EOL;
			}

			if($cc) {
				$headers .= 'Cc: '.$cc.EOL;
			}

			if($bcc) {
				$headers .= 'Bcc: '.$bcc.EOL;
			}

			$headers .= 'Date: '.date('r').EOL;
			$headers .= 'Message-Id: '.md5(rand()).'@'.HTTP_HOST.EOL;

			if(sizeof($this->attachments)) {

				$mimeMail = new MimeMail();

				$part =
					MimePart::create()->
					setBody($body)->
					setContentType($contentType)->
					setCharset($encoding)->
					setEncoding(MailEncoding::eight());
				$mimeMail->addPart($part);

				foreach($this->attachments as $part) {
					$mimeMail->addPart($part);
				}

				$mimeMail->build();

				$headers .= $mimeMail->getHeaders();

				$body = $mimeMail->getEncodedBody();

			} else {

				$headers .= 'Content-type: '.$contentType.'; charset='.$encoding.EOL;
				$headers .= 'Content-Transfer-Encoding: 8bit'.EOL;

			}

			mail($to, $subject, $body, $headers);
		}

		private function prepareContext()
		{
			$viewResolver =
				MultiPrefixPhpViewResolver::create()->
				setViewClassName('SimplePhpView')->
				addPrefix(PATH_ADMIN_TEMPLATES)->
				addPrefix(PATH_USER_TEMPLATES)->
				setPostfix('.php.eml');

			$view = $viewResolver->resolveViewName($this->view);

			$text = $view->toString($this->model);

			list($header, $body) = preg_split("/\r?\n\r?\n/s", $text, 2);
			$this->body = trim($body);
			foreach(preg_split("/\n/s", $header) as $line) {
				$pos = strpos($line, ':');
				$name = ucfirst(strtolower(substr($line, 0, $pos)));
				$value = trim(substr($line, $pos + 1));
				$this->header[$name] = rtrim($value, ';');
			}

			return $this;
		}
	}
?>