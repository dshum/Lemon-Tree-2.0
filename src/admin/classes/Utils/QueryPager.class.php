<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	final class QueryPager extends BasePager
	{
		private $dao = null;
		private $query = null;

		public static function create(ElementDAO $dao, SelectQuery $query)
		{
			return new self($dao, $query);
		}

		public function __construct(ElementDAO $dao, SelectQuery $query)
		{
			$this->dao = $dao;
			$this->query = $query;
		}

		public function getCountOnly($expires = Cache::DO_NOT_CACHE)
		{
			$this->process($expires);

			return $this->total;
		}

		public function getList($expires = Cache::DO_NOT_CACHE)
		{
			$this->process($expires);

			return
				$this->total > 0
				? $this->dao->getListByQuery($this->query, $expires)
				: array();
		}

		public function getCustomList($expires = Cache::DO_NOT_CACHE)
		{
			$this->process($expires);

			return
				$this->total > 0
				? $this->dao->getCustomList($this->query, $expires)
				: array();
		}

		public function getCustomRowList($expires = Cache::DO_NOT_CACHE)
		{
			$this->process($expires);

			return
				$this->total > 0
				? $this->dao->getCustomRowList($this->query, $expires)
				: array();
		}

		private function process($expires = Cache::DO_NOT_CACHE)
		{
			$query = clone $this->query;

			$query->
			dropFields()->
			dropOrder()->
			get(
				new SQLFunction(
					'COUNT',
					new DBField('id', $this->dao->getTable())
				),
				'count'
			);

			$custom = $this->dao->getCustom($query, $expires);

			$this->total = $custom['count'];

			if($this->perpage) {

				$this->pageCount = ceil($this->total / $this->perpage);

				if($this->offset) {
					$correction =
						$this->offset > 0
						? ceil($this->offset / $this->perpage)
						: floor($this->offset / $this->perpage);
					$this->pageCount -= $correction;
				}

				if($this->currentPage > $this->pageCount) {
					$this->currentPage = $this->pageCount;
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

				if($this->offset + $offset > 0) {
					$offset += $this->offset;
				}

				$this->query->limit($this->perpage, $offset);
			}
		}
	}
?>