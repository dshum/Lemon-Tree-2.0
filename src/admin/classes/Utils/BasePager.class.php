<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	abstract class BasePager
	{
		protected $perpage = 0;
		protected $currentPage = 1;
		protected $total = 0;
		protected $prevPage = null;
		protected $nextPage = null;
		protected $hasPrevPage = false;
		protected $hasNextPage = false;
		protected $pageCount = 0;
		protected $pageList = array();
		protected $pageLimit = null;
		protected $firstPageLimit = 0;
		protected $offset = null;

		public function setPerpage($perpage)
		{
			$this->perpage = $perpage;

			return $this;
		}

		public function setCurrentPage($currentPage)
		{
			$this->currentPage =
				$currentPage === 0 || $currentPage > 1
				? $currentPage
				: 1;

			return $this;
		}

		public function setPageLimit($pageLimit)
		{
			$this->pageLimit = $pageLimit;

			return $this;
		}

		public function setFirstPageLimit($firstPageLimit)
		{
			$this->firstPageLimit = $firstPageLimit;

			return $this;
		}

		public function setOffset($offset)
		{
			$this->offset = $offset;

			return $this;
		}

		public function getPerpage()
		{
			return $this->perpage;
		}

		public function getCurrentPage()
		{
			return $this->currentPage;
		}

		public function getPageLimit()
		{
			return $this->pageLimit;
		}

		public function getFirstPageLimit()
		{
			return $this->firstPageLimit;
		}

		public function getOffset()
		{
			return $this->offset;
		}

		public function getTotal()
		{
			return $this->total;
		}

		public function getPageCount()
		{
			return $this->pageCount;
		}

		public function getPageList()
		{
			return $this->pageList;
		}

		public function getLimitedPageList()
		{
			if($this->pageLimit && $this->pageCount > $this->pageLimit) {
				$start = max($this->currentPage - floor($this->pageLimit / 2), 1);
				$end = min($this->currentPage + floor($this->pageLimit / 2), $this->pageCount);

				$limitedPageList = array();

				for($i = $start; $i <= $end; $i++) {
					$limitedPageList[] = $i;
				}

				return $limitedPageList;
			}

			return $this->pageList;
		}

		public function hasPrevPage()
		{
			return $this->hasPrevPage;
		}

		public function getPrevPage()
		{
			return $this->prevPage;
		}

		public function hasNextPage()
		{
			return $this->hasNextPage;
		}

		public function getNextPage()
		{
			return $this->nextPage;
		}

		public function hasPrevPart()
		{
			return
				$this->pageLimit
				&& $this->pageCount > $this->pageLimit
				&& $this->currentPage - floor($this->pageLimit / 2) > 1
				? true
				: false;
		}

		public function getPrevPart()
		{
			return
				$this->pageLimit
				&& $this->pageCount > $this->pageLimit
				&& $this->currentPage - floor($this->pageLimit / 2) > 1
				? $this->currentPage - floor($this->pageLimit / 2) - 1
				: 1;
		}

		public function hasNextPart()
		{
			return
				$this->pageLimit
				&& $this->pageCount > $this->pageLimit
				&& $this->currentPage + floor($this->pageLimit / 2) < $this->pageCount
				? true
				: false;
		}

		public function getNextPart()
		{
			return
				$this->pageLimit
				&& $this->pageCount > $this->pageLimit
				&& $this->currentPage + floor($this->pageLimit / 2) < $this->pageCount
				? $this->currentPage + floor($this->pageLimit / 2) + 1
				: $this->pageCount;
		}
	}
?>