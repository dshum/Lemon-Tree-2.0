<?php
	final class Site
	{
		private static $sqlreport = array();
		private static $currentElement = null;
		private static $levels = array();
		private static $lastModified = null;
		private static $initMicroTime = array(0, 0);

		public static function init()
		{
			Item::dao()->setItemList();
			Site::printMicroTime('After item list');
			Property::dao()->setPropertyList();
			Site::printMicroTime('After property list');
			Parameter::dao()->setParameterList();
			Site::printMicroTime('After parameter list');

			// self::checkDomain();

			// self::setLastModified();
		}

		public static function setLastModified()
		{
			try {
				self::$lastModified = filemtime(PATH_LTDATA.'last-modified');
			} catch (BaseException $e) {
				self::updateLastModified();
			}
		}

		public static function setCurrentElement($currentElement)
		{
			self::$currentElement = $currentElement;
		}

		public static function getCurrentElement()
		{
			return self::$currentElement;
		}

		public static function setLevels($levels)
		{
			self::$levels = $levels;
		}

		public static function getLevels()
		{
			return self::$levels;
		}

		public static function getLevel($level)
		{
			return
				isset(self::$levels[$level])
				? self::$levels[$level]
				: null;
		}

		public static function updateLastModified()
		{
			self::$lastModified = time();
			touch(PATH_LTDATA.'last-modified', self::$lastModified);
		}

		public static function getLastModified()
		{
			return self::$lastModified;
		}

		public static function getModifiedSince()
		{
			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
				$modifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			} else {
				$modifiedSince = 0;
			}

			return $modifiedSince;

		}

		public static function setQuery($queryString, $time)
		{
			self::$sqlreport[] = array($queryString, $time);

			// $filename = ONPHP_TEMP_PATH.basename($_SERVER['PHP_SELF']).'.counter';
			// $f = fopen($filename, 'a+');
			// fprintf($f, $queryString.EOL.EOL);
			// fprintf($f, '+');
			// fclose($f);
		}

		public static function getSQLReport()
		{
			return self::$sqlreport;
		}

		public static function initMicroTime()
		{
			self::$initMicroTime = explode(' ', microtime());
		}

		public static function getMicroTime()
		{
			list($usec1, $sec1) = explode(' ', microtime());
			list($usec0, $sec0) = self::$initMicroTime;
			$time = (float)$sec1 + (float)$usec1 - (float)$sec0 - (float)$usec0;

			return $time;
		}

		public static function printMicroTime($label = 'Now')
		{
			if(isset($_GET['sqlreport'])) {
				$time = self::getMicroTime();
				echo
					$label.': '.round($time, 6)
					.' - '.round(memory_get_usage() / 1024 / 1024, 2).' Mb<br>';
			}
		}

		public static function generateAuto()
		{
			if(!defined('ONPHP_META_BUSINESS_DIR')) {
				define('ONPHP_META_BUSINESS_DIR', PATH_USER_CLASSES.'Business'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_DAO_DIR')) {
				define('ONPHP_META_DAO_DIR', PATH_USER_CLASSES.'DAOs'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_PROTO_DIR')) {
				define('ONPHP_META_PROTO_DIR', PATH_USER_CLASSES.'Proto'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_DIR')) {
				define('ONPHP_META_AUTO_DIR', PATH_USER_CLASSES_AUTO);
			}
			if(!defined('ONPHP_META_AUTO_BUSINESS_DIR')) {
				define('ONPHP_META_AUTO_BUSINESS_DIR', ONPHP_META_AUTO_DIR.'Business'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_DAO_DIR')) {
				define('ONPHP_META_AUTO_DAO_DIR', ONPHP_META_AUTO_DIR.'DAOs'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_PROTO_DIR')) {
				define('ONPHP_META_AUTO_PROTO_DIR', ONPHP_META_AUTO_DIR.'Proto'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_DTO_DIR')) {
				define('ONPHP_META_AUTO_DTO_DIR', ONPHP_META_AUTO_DIR.'DTOs'.DIRECTORY_SEPARATOR);
			}

			if(!is_dir(ONPHP_META_DAO_DIR)) {
				mkdir(ONPHP_META_DAO_DIR, 0755, true);
			}
			if(!is_dir(ONPHP_META_AUTO_DTO_DIR)) {
				mkdir(ONPHP_META_AUTO_DTO_DIR, 0755, true);
			}
			if(!is_dir(ONPHP_META_AUTO_DIR)) {
				mkdir(ONPHP_META_AUTO_DIR, 0755, true);
			}
			if(!is_dir(ONPHP_META_AUTO_BUSINESS_DIR)) {
				mkdir(ONPHP_META_AUTO_BUSINESS_DIR, 0755);
			}
			if(!is_dir(ONPHP_META_AUTO_PROTO_DIR)) {
				mkdir(ONPHP_META_AUTO_PROTO_DIR, 0755);
			}
			if(!is_dir(ONPHP_META_AUTO_DAO_DIR)) {
				mkdir(ONPHP_META_AUTO_DAO_DIR, 0755);
			}
			if(!is_dir(ONPHP_META_BUSINESS_DIR)) {
				mkdir(ONPHP_META_BUSINESS_DIR, 0755, true);
			}
			if(!is_dir(ONPHP_META_PROTO_DIR)) {
				mkdir(ONPHP_META_PROTO_DIR, 0755, true);
			}

			self::writeConfig();

			ob_start();
			$out = new TextOutput();
			$out = new MetaOutput($out);
			$meta = MetaConfiguration::me()->
				setOutput($out)->
				load(ONPHP_META_PATH.'internal.xml', false)->
				load(PATH_META.CONFIG_ADMIN_XML, false)->
				load(PATH_META.CONFIG_USER_XML);
			$meta->buildClasses();
			$meta->buildContainers();
			$meta->buildSchema();
			ob_end_clean();
		}

		public static function generateAutoAdmin()
		{
			if(!defined('ONPHP_META_BUSINESS_DIR')) {
				define('ONPHP_META_BUSINESS_DIR', PATH_ADMIN_CLASSES.'Business'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_DAO_DIR')) {
				define('ONPHP_META_DAO_DIR', PATH_ADMIN_CLASSES.'DAOs'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_PROTO_DIR')) {
				define('ONPHP_META_PROTO_DIR', PATH_ADMIN_CLASSES.'Proto'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_DIR')) {
				define('ONPHP_META_AUTO_DIR', PATH_ADMIN_CLASSES.'Auto'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_BUSINESS_DIR')) {
				define('ONPHP_META_AUTO_BUSINESS_DIR', ONPHP_META_AUTO_DIR.'Business'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_DAO_DIR')) {
				define('ONPHP_META_AUTO_DAO_DIR', ONPHP_META_AUTO_DIR.'DAOs'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_PROTO_DIR')) {
				define('ONPHP_META_AUTO_PROTO_DIR', ONPHP_META_AUTO_DIR.'Proto'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_DTO_DIR')) {
				define('ONPHP_META_AUTO_DTO_DIR', ONPHP_META_AUTO_DIR.'DTOs'.DIRECTORY_SEPARATOR);
			}

			if(!is_dir(ONPHP_META_DAO_DIR)) {
				mkdir(ONPHP_META_DAO_DIR, 0755, true);
			}
			if(!is_dir(ONPHP_META_AUTO_DTO_DIR)) {
				mkdir(ONPHP_META_AUTO_DTO_DIR, 0755, true);
			}
			if(!is_dir(ONPHP_META_AUTO_DIR)) {
				mkdir(ONPHP_META_AUTO_DIR, 0755, true);
			}
			if(!is_dir(ONPHP_META_AUTO_BUSINESS_DIR)) {
				mkdir(ONPHP_META_AUTO_BUSINESS_DIR, 0755);
			}
			if(!is_dir(ONPHP_META_AUTO_PROTO_DIR)) {
				mkdir(ONPHP_META_AUTO_PROTO_DIR, 0755);
			}
			if(!is_dir(ONPHP_META_AUTO_DAO_DIR)) {
				mkdir(ONPHP_META_AUTO_DAO_DIR, 0755);
			}
			if(!is_dir(ONPHP_META_BUSINESS_DIR)) {
				mkdir(ONPHP_META_BUSINESS_DIR, 0755, true);
			}
			if(!is_dir(ONPHP_META_PROTO_DIR)) {
				mkdir(ONPHP_META_PROTO_DIR, 0755, true);
			}

			echo ONPHP_META_AUTO_PROTO_DIR;

			self::writeConfig();

			ob_start();
			$out = new TextOutput();
			$out = new MetaOutput($out);
			$meta = MetaConfiguration::me()->
				setOutput($out)->
				load(ONPHP_META_PATH.'internal.xml', false)->
				load(PATH_META.CONFIG_ADMIN_XML);
			$meta->buildClasses();
			$meta->buildContainers();
			$meta->buildSchema();
			ob_end_clean();
		}

		public static function dropAuto(Item $item)
		{
			if(!defined('ONPHP_META_BUSINESS_DIR')) {
				define('ONPHP_META_BUSINESS_DIR', PATH_USER_CLASSES.'Business'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_DAO_DIR')) {
				define('ONPHP_META_DAO_DIR', PATH_USER_CLASSES.'DAOs'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_PROTO_DIR')) {
				define('ONPHP_META_PROTO_DIR', PATH_USER_CLASSES.'Proto'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_DIR')) {
				define('ONPHP_META_AUTO_DIR', PATH_USER_CLASSES.'Auto'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_BUSINESS_DIR')) {
				define('ONPHP_META_AUTO_BUSINESS_DIR', ONPHP_META_AUTO_DIR.'Business'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_DAO_DIR')) {
				define('ONPHP_META_AUTO_DAO_DIR', ONPHP_META_AUTO_DIR.'DAOs'.DIRECTORY_SEPARATOR);
			}
			if(!defined('ONPHP_META_AUTO_PROTO_DIR')) {
				define('ONPHP_META_AUTO_PROTO_DIR', ONPHP_META_AUTO_DIR.'Proto'.DIRECTORY_SEPARATOR);
			}

			try {

				$date = date('Y-m-d_H:i:s');

				if(file_exists(ONPHP_META_BUSINESS_DIR.$item->getItemName().EXT_CLASS)) {
					rename(
						ONPHP_META_BUSINESS_DIR.$item->getItemName().EXT_CLASS,
						ONPHP_META_BUSINESS_DIR.$item->getItemName().EXT_CLASS.'.'.$date
					);
				}
				if(file_exists(ONPHP_META_DAO_DIR.$item->getItemName().'DAO'.EXT_CLASS)) {
					rename(
						ONPHP_META_DAO_DIR.$item->getItemName().'DAO'.EXT_CLASS,
						ONPHP_META_DAO_DIR.$item->getItemName().'DAO'.EXT_CLASS.'.'.$date
					);
				}
				if(file_exists(ONPHP_META_PROTO_DIR.'Proto'.$item->getItemName().EXT_CLASS)) {
					rename(
						ONPHP_META_PROTO_DIR.'Proto'.$item->getItemName().EXT_CLASS,
						ONPHP_META_PROTO_DIR.'Proto'.$item->getItemName().EXT_CLASS.'.'.$date
					);
				}
				if(file_exists(ONPHP_META_AUTO_BUSINESS_DIR.'Auto'.$item->getItemName().EXT_CLASS)) {
					unlink(ONPHP_META_AUTO_BUSINESS_DIR.'Auto'.$item->getItemName().EXT_CLASS);
				}
				if(file_exists(ONPHP_META_AUTO_DAO_DIR.'Auto'.$item->getItemName().'DAO'.EXT_CLASS)) {
					unlink(ONPHP_META_AUTO_DAO_DIR.'Auto'.$item->getItemName().'DAO'.EXT_CLASS);
				}
				if(file_exists(ONPHP_META_AUTO_PROTO_DIR.'Auto'.'Proto'.$item->getItemName().EXT_CLASS)) {
					unlink(ONPHP_META_AUTO_PROTO_DIR.'Auto'.'Proto'.$item->getItemName().EXT_CLASS);
				}

			} catch (BaseException $e) {}
		}

		private static function checkDomain()
		{
			$h = is_callable('getallheaders') ? getallheaders() : array();
			$http_host = $h['Host'] ? $h['Host'] : HTTP_HOST;
			$http_host = strtolower($http_host);
			$http_host = str_replace('www.', '', $http_host);
			$http_host = str_replace(':80', '', $http_host);
			$http_host = trim($http_host, '.');
			$tmp = explode('.', $http_host);
			$d1 = array_pop($tmp);
			$d2 = array_pop($tmp);
			$d = array($d2, $d1);
			$domain = implode('.', $d);

			$multiDomainList = array(
				'lemon-tree.ru',
			);
			$singleDomainList = array(
				'future.ru',
			);

			$wrong = true;
			foreach($multiDomainList as $domain) {
				if(preg_match('/^(.*)'.$domain.'$/i', $http_host)) {
					$wrong = false;
				}
			}
			if(in_array($http_host, $singleDomainList)) {
				$wrong = false;
			}

			if($wrong) {
				// Send mail and display notice
			}
		}

		private static function writeConfig()
		{
			try {

				$f = fopen(PATH_META.CONFIG_ADMIN_XML, 'w');
				$content = self::buildConfigAdmin();
				fwrite($f, $content);
				fclose($f);

				$f = fopen(PATH_META.CONFIG_USER_XML, 'w');
				$content = self::buildConfigUser();
				fwrite($f, $content);
				fclose($f);

			} catch (BaseException $e) {
				throw $e;
			}
		}

		private static function buildConfigAdmin()
		{
			$out = '';
			$out .= <<<XML
<?xml version="1.0"?>
<!DOCTYPE metaconfiguration SYSTEM "meta.dtd">
<metaconfiguration>
	<classes>

		<class name="Item" table="cytrus_item">
			<properties>
				<identifier type="Integer" />
				<property name="itemName" type="String" size="50" required="true" />
				<property name="itemDescription" type="String" size="255" required="true" />
				<property name="itemOrder" type="Integer" required="false" />
				<property name="classType" type="String" size="50" required="false" />
				<property name="parentClass" type="String" size="50" required="false" />
				<property name="classSource" type="String" size="50" required="false" />
				<property name="tableName" type="String" size="255" required="false" />
				<property name="mainPropertyDescription" type="String" size="255" required="false" />
				<property name="controllerName" type="String" size="50" required="false" />
				<property name="pluginName" type="String" size="50" required="false" />
				<property name="filterName" type="String" size="50" required="false" />
				<property name="beforeInsert" type="String" size="50" required="false" />
				<property name="afterInsert" type="String" size="50" required="false" />
				<property name="beforeUpdate" type="String" size="50" required="false" />
				<property name="afterUpdate" type="String" size="50" required="false" />
				<property name="beforeDelete" type="String" size="50" required="false" />
				<property name="afterDelete" type="String" size="50" required="false" />
				<property name="isFolder" type="Boolean" default="false" required="false" />
				<property name="pathPrefix" type="String" size="50" required="false" />
				<property name="orderField" type="String" size="50" required="false" />
				<property name="orderDirection" type="Boolean" required="false" />
				<property name="perPage" type="Integer" default="0" />
			</properties>
			<pattern name="DictionaryClass" />
		</class>

		<class name="Property" type="final" table="cytrus_property">
			<properties>
				<identifier type="Integer" />
				<property name="item" type="Item" relation="OneToOne" fetch="lazy" required="true" />
				<property name="propertyName" type="String" size="50" required="true" />
				<property name="propertyClass" type="String" size="50" required="true" />
				<property name="propertyDescription" type="String" size="255" required="true" />
				<property name="propertyOrder" type="Integer" required="false" />
				<property name="isRequired" type="Boolean" default="false" />
				<property name="isShow" type="Boolean" default="false" />
				<property name="fetchClass" type="String" size="50" required="false" />
				<property name="fetchStrategyId" type="Integer" required="false" />
				<property name="onDelete" type="String" size="50" required="false" />
				<property name="isParent" type="Boolean" required="false" />
			</properties>
			<pattern name="DictionaryClass" />
		</class>

		<class name="Parameter" type="final" table="cytrus_parameter">
			<properties>
				<identifier type="Integer" />
				<property name="property" type="Property" relation="OneToOne" fetch="lazy" required="true" />
				<property name="parameterName" type="String" size="50" required="true" />
				<property name="parameterValue" type="String" size="255" required="false" />
			</properties>
			<pattern name="DictionaryClass" />
		</class>

		<class name="Bind2Item" type="final" table="cytrus_bind_to_item">
			<properties>
				<identifier type="Integer" />
				<property name="item" type="Item" relation="OneToOne" fetch="lazy" required="true" />
				<property name="bindItem" type="Item" relation="OneToOne" fetch="lazy" required="true" />
			</properties>
			<pattern name="DictionaryClass" />
		</class>

		<class name="Bind2Element" type="final" table="cytrus_bind_to_element">
			<properties>
				<identifier type="Integer" />
				<property name="elementId" type="String" size="50" required="true" />
				<property name="bindItem" type="Item" relation="OneToOne" fetch="lazy" required="true" />
			</properties>
			<pattern name="DictionaryClass" />
		</class>

		<class name="Group" type="final" table="cytrus_group">
			<properties>
				<identifier type="Integer" />
				<property name="parent" type="Group" relation="OneToOne" fetch="lazy" required="false" />
				<property name="groupDescription" type="String" size="255" required="true" />
				<property name="ownerPermission" type="Integer" default="0" required="true" />
				<property name="groupPermission" type="Integer" default="0" required="true" />
				<property name="worldPermission" type="Integer" default="0" required="true" />
				<property name="isDeveloper" type="Boolean" default="false" required="true" />
				<property name="isAdmin" type="Boolean" default="false" required="true" />
			</properties>
			<pattern name="DictionaryClass" />
		</class>

		<class name="User" type="final" table="cytrus_user">
			<properties>
				<identifier type="Integer" />
				<property name="group" type="Group" relation="OneToOne" fetch="lazy" required="true" />
				<property name="userName" type="String" size="50" required="true" />
				<property name="userPassword" type="String" size="255" required="true" />
				<property name="userDescription" type="String" size="255" required="true" />
				<property name="userEmail" type="String" size="255" required="false" />
				<property name="registrationDate" type="Timestamp" required="true" />
				<property name="loginDate" type="Timestamp" required="true" />
			</properties>
			<pattern name="DictionaryClass" />
		</class>

		<class name="ItemPermission" type="final" table="cytrus_item_permission">
			<properties>
				<identifier type="Integer" />
				<property name="group" type="Group" relation="OneToOne" fetch="lazy" required="true" />
				<property name="item" type="Item" relation="OneToOne" fetch="lazy" required="true" />
				<property name="ownerPermission" type="Integer" default="0" required="true" />
				<property name="groupPermission" type="Integer" default="0" required="true" />
				<property name="worldPermission" type="Integer" default="0" required="true" />
			</properties>
			<pattern name="DictionaryClass" />
		</class>

		<class name="ElementPermission" type="final" table="cytrus_element_permission">
			<properties>
				<identifier type="Integer" />
				<property name="group" type="Group" relation="OneToOne" fetch="lazy" required="true" />
				<property name="elementId" type="String" size="50" required="true" />
				<property name="permission" type="Integer" default="0" required="true" />
			</properties>
			<pattern name="DictionaryClass" />
		</class>

		<class name="RewriteRule" type="final" table="cytrus_rewrite_rule">
			<properties>
				<identifier type="Integer" />
				<property name="url" type="String" size="255" required="true" />
				<property name="level" type="Integer" required="true" />
				<property name="item" type="Item" relation="OneToOne" fetch="lazy" required="true" />
				<property name="initialPath" type="String" size="255" required="false" />
				<property name="rewriteRuleOrder" type="Integer" required="false" />
			</properties>
			<pattern name="DictionaryClass" />
		</class>

		<class name="Element" type="abstract">
			<properties>
				<identifier type="Integer" />
				<property name="elementName" type="String" size="255" required="true" />
				<property name="elementOrder" type="Integer" required="false" />
				<property name="status" type="String" size="50" required="true" />
				<property name="elementPath" type="String" size="255" required="true" />
				<property name="controllerName" type="String" size="50" required="false" />
				<property name="pluginName" type="String" size="50" required="false" />
				<property name="group" type="Group" relation="OneToOne" fetch="lazy" required="false" />
				<property name="user" type="User" relation="OneToOne" fetch="lazy" required="false" />
			</properties>
			<pattern name="AbstractClass" />
 		</class>

 		<class name="Root" extends="Element">
 			<properties></properties>
 			<pattern name="StraightMapping" />
 		</class>
	</classes>
</metaconfiguration>
XML;
			return $out;
		}

		private static function buildConfigUser()
		{
			$out = '';
			$out .= <<<XML
<?xml version="1.0"?>
<!DOCTYPE metaconfiguration SYSTEM "meta.dtd">
<metaconfiguration>
	<classes>

XML;
			$itemList = Item::dao()->getItemList();

			foreach($itemList as $item) {
				switch($item->getClassType()) {
					case 'abstract':
						$type = ' type="abstract"'; break;
					default:
						$type = ''; break;
				}
				$extends =
					$item->getParentClass()
					? $item->getParentClass()
					: 'Element';
				$tableName =
					$item->getTableName()
					? $item->getTableName()
					: $item->getDefaultTableName();
				$pattern = $type == 'abstract' ? 'AbstractClass' : 'StraightMapping';
				$out .= <<<XML
		<class name="{$item->getItemName()}"$type extends="$extends" table="$tableName">
			<properties>

XML;
				try {
					$parentItem = Item::dao()->getItemByName($item->getParentClass());
				} catch (ObjectNotFoundException $e) {
					$parentItem = null;
				}
				$propertyList = Property::dao()->getPropertyList($item);
				foreach($propertyList as $property) {
					if($parentItem) {
						try {
							Property::dao()->getPropertyByName($parentItem, $property->getPropertyName());
							continue;
						} catch (ObjectNotFoundException $e) {}
					}
					try {
						$out .= <<<XML
				{$property->getClass(null)->meta()}

XML;
					} catch (BaseException $e) {
						continue;
					}
				}
				$out .= <<<XML
			</properties>
			<pattern name="$pattern" />
		</class>

XML;
			}
			$out .= <<<XML
	</classes>
</metaconfiguration>
XML;
			return $out;
		}
	}
?>