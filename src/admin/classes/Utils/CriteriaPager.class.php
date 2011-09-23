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

		public function getList()
		{
			$this->process();

			return $this->total > 0 ? $this->criteria->getList() : array();
		}

		private function process()
		{
			$offset =
				$this->currentPage > 1
				? ($this->currentPage - 1) * $this->perpage
				: 0;

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
				);

			$custom = $criteria->getDao()->getCustom($query);

			$this->total = $custom['count'];

			if($this->perpage) {

				$this->pageCount = ceil($this->total / $this->perpage);

				for($i = 1; $i <= $this->pageCount; $i++) {
					$this->pageList[] = $i;
				}

				$this->prevPage =
					$this->currentPage > 1
					? $this->currentPage - 1
					: 0;

				$this->nextPage = $this->currentPage + 1;

				$this->hasPrevPage = $this->currentPage > 1 ? true : false;

				$this->hasNextPage = $this->currentPage < $this->pageCount ? true : false;

				$this->criteria->setLimit($this->perpage)->setOffset($offset);

			}
		}
	}
?>