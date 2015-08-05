<?php


	namespace LiftKit\Database\Query\Condition;

	use LiftKit\Database\Query\Exception\UnsupportedFeature;


	class MsSql extends Condition
	{



		public function search ($fields, $termString)
		{
			$condition = new self($this->database);

			$terms = preg_split('#(\s+)#', $termString);
			$terms = array_filter($terms);

			if (!empty($fields) && !empty($terms)) {
				foreach ($terms as $term) {
					$innerCondition = new self($this->database);

					foreach ($fields as $field) {
						$innerCondition->orLike(
							$field,
							'[^A-Za-z0-9]' . $term . '%'
						);

						$innerCondition->orLike(
							$field,
							$term . '%'
						);
					}

					$condition->condition($innerCondition);
				}

				$this->condition($condition);
			} else {
				$this->raw('1 = 1');
			}
		}


		public function regexp ($value, $pattern)
		{
			return $this->like($value, $pattern);
		}


		public function orRegexp ($value, $pattern)
		{
			return $this->orLike($value, $pattern);
		}


		public function notRegexp ($value, $pattern)
		{
			return $this->notLike($value, $pattern);
		}


		public function orNotRegexp ($value, $pattern)
		{
			return $this->orNotLike($value, $pattern);
		}
	}