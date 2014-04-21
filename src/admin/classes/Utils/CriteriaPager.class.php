<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	final class CriteriaPager extends BasePager
	{
		private $criteria = null;

		public static function create(Criteria $criteria)
		{
			return new self($criteria);
		}

		public function __construct(Criteria $criteria)
		{
			$this->criteria = $criteria;
		}

		public function getCriteria()
		{
			return $this->criteria;
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
				? $this->criteria->getList()
				: array();
		}

		private function process()
		{
			$criteria = clone $this->criteria;

			$query =
				$criteria->toSelectQuery()->
				dropFields()->
				dropOrder()->
				get(
					new SQLFunction(
						'COUNT',
						new DBField('id', $criteria->getDao()->getTable())
					),
					'count'
				)->
				limit(1);

			$custom = $criteria->getDao()->getCustom($query);

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

				if($this->getOffset() + $offset > 0) {
					$offset += $this->getOffset();
				}

				$this->criteria->setLimit($this->perpage)->setOffset($offset);

			}
		}
	}
?>