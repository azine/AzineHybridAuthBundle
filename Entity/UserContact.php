<?php

namespace Azine\HybridAuthBundle\Entity;

class UserContact {

		/**
		 * @var string
		 */
		public $provider = NULL;

		/**
		 * @var array
		 */
		protected $fields = [];
	
    /**
     * @param string $provider
     */
    public function __construct($provider)
		{
				$this->provider = $provider;
		}

		/**
		 * @param mixed $key
		 * @param mixed $value
		 *
		 * @return UserContact
		 */
		public function setField($key, $value)
		{
				$this->fields[$key] = $value;
				return $this;
		}

		/**
		 * @param mixed $key
		 *
		 * @return mixed|null
		 */
		public function getField($key)
		{
				if (array_key_exists($key, $this->fields))
						return $this->fields[$key];
		}

		/**
		 * @return array
		 */
		public function getFields()
		{
				return $this->fields;
		}

}
