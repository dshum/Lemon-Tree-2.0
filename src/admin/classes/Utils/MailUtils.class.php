<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	final class MailUtils
	{
		private $model = null;
		private $view = null;

		private $header = array(
			'From' => null,
			'Reply-to' => null,
			'To' => null,
			'Cc' => null,
			'Bcc' => null,
			'Content-type' => null,
			'Subject' => null,
		);
		private $body = null;
		private $attachments = array();

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

		public function addAttachment($path, $name, $description, $id, $inline = false, $mimetype = 'application/octet-stream')
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

		public function send()
		{
			$encoding = mb_get_info('internal_encoding');

			$this->prepareContext();

			$to = $this->header['To'];
			$subject =
					"=?".$encoding."?B?"
					.base64_encode($this->header['Subject'])
					."?=";
			$body = null;
			$headers = null;

			if($this->header['From']) {
				$headers .= "From: ".$this->header['From']."\n";
				$headers .= "Return-Path: ".$this->header['From']."\n";
			}

			if($this->header['Reply-to']) {
				$headers .= "Reply-to: ".$this->header['Reply-to']."\n";
			}

			if($this->header['Cc']) {
				$headers .= "Cc: ".$this->header['Cc']."\n";
			}

			if($this->header['Bcc']) {
				$headers .= "Bcc: ".$this->header['Bcc']."\n";
			}

			$headers .= "Date: ".date('r')."\n";

			if(sizeof($this->attachments)) {

				$mimeMail = new MimeMail();

				$part =
					MimePart::create()->
					setBody($this->body)->
					setContentType($this->header['Content-type'])->
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

				if($this->header['Content-type']) {
					$headers .= "Content-type: ".$this->header['Content-type']."; charset=".$encoding."\n";
				} else {
					$headers .= "Content-type: text/plain; charset=".$encoding."\n";
				}

				$headers .= "Content-Transfer-Encoding: 8bit\n";

				$body = $this->body;
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
			$this->body = trim($body)."\n";
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