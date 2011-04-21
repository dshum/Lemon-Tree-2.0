<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	final class UnifiedContainerPager extends BasePager
	{
		private $container = null;

		public static function create(UnifiedContainer $container)
		{
			return new self($container);
		}

		public function __construct(UnifiedContainer $container)
		{
			$this->container = $container;
		}

		public function getContainer()
		{
			return $this->container;
		}

		public function getCriteria()
		{
			return $this->container->getCriteria();
		}

		public function getCountOnly()
		{
			$this->process();

			return $this->total;
		}

		public function getList()
		{
			$this->process();

			return
				$this->total > 0
				? $this->container->getList()
				: array();
		}

		private function process()
		{
			$criteria = $this->getCriteria();

			$this->total = $this->container->getCount();

			if($this->perpage) {

				$this->pageCount = ceil($this->total / $this->perpage);

				if($this->currentPage > $this->pageCount) {
					$this->currentPage = 1;
				}

				for($i = 1; $i <= $this->pageCount; $i++) {
					$this->pageList[] = $i;
				}

				$this->prevPage =
					$this->currentPage > 1
					? $this->currentPage - 1
					: 1;

				$this->nextPage = $this->currentPage + 1;

				$this->hasPrevPage = $this->currentPage > 1 ? true : false;

				$this->hasNextPage = $this->currentPage < $this->pageCount ? true : false;

				$offset =
					$this->currentPage > 1
					? ($this->currentPage - 1) * $this->perpage
					: 0;

				$criteria->setLimit($this->perpage)->setOffset($offset);

				$this->container->setCriteria($criteria);

			}
		}
	}
?>