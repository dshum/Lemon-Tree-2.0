<?php
	final class ElementParameter extends BaseParameter
	{
		public function toRaw($value)
		{
			if($value instanceof Element) {
				return $value->getPolymorphicId();
			} elseif(is_string($value)) {
				return $value;
			} else {
				return null;
			}
		}

		public function printOnEdit()
		{
			$value =
				$this->value instanceof Element
				? $this->value->getPolymorphicId()
				: $this->value;
			$str = '<input class="item-name" type="text" name="'.$this->name.'" value="'.$value.'">';

			return $str;
		}

		public function setValue($raw)
		{
			switch($raw) {
				case 'this': case 'parent':
				case 'level1': case 'level2': case 'level3': case 'level4':
				case 'level5': case 'level6': case 'level7': case 'level8':
					$this->value = $raw;
					break;
				default:
					if(!$raw) {
						$this->value = Root::me();
						break;
					}
					$this->value = $raw;
					try {
						list($className, $id) = explode(
							PrimitivePolymorphicIdentifier::DELIMITER,
							$raw
						);
						if(
							ClassUtils::isClassName($className)
							&& ClassUtils::isInstanceOf($className, 'Element')
						) {
							$class = new $className;
							$this->value = $class->setId($id)->setStatus('root');
						}

					} catch (BaseException $e) {}
					break;
			}

			return $this;
		}
	}
?>