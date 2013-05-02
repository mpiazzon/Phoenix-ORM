<?php
/*
 * This file is part of the Phoenix package
 *
 * (c) 2011 Martin Piazzon
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Validation
{
	protected $_errors = array();

	protected $_status = true;

	static public function factory($rules,$data,$model)
	{
		$validator = new self;
		$validator->_validate($rules,$data,$model);
		return $validator;
	}

	protected function _validate($rules,$data,$model)
	{
		foreach ($rules as $r) {
			$v = Model::getOptions($r);
			switch ($v['type']) {
				case 'required':
					$this->_presence($data[$v['field']],$v);
					break;
				case 'length':
					$this->_length($data[$v['field']],$v);
					break;
				case 'email':
					$this->_email($data[$v['field']],$v);
					break;
				case 'number':
					$this->_number($data[$v['field']],$v);
					break;
				case 'url':
					$this->_url($data[$v['field']],$v);
					break;
				case 'format':
					$this->_format($data[$v['field']],$v);
					break;
				case 'unique':
					$this->_unique($data[$v['field']],$v,$model);
					break;
				case 'custom':
					$this->_custom($data[$v['field']],$v);
					break;

				default:
					# code...
					break;
			}

		}

		if (count($this->_errors) > 0)
		{
			$this->_status = false;
		}
		else
			$this->_status = false;
	}

	public function getErrors()
	{
		return $this->_errors;
	}

	public function getStatus()
	{
		return $this->_status;
	}

	protected function _presence($value,$opts)
	{
		if (trim($value) == '')
			$this->_errors[] = $opts['msg'];
	}

	protected function _length($value,$opts)
	{
		$range = explode('-',$opts['range'],2);
		if ((strlen($value) < trim($range[0])) or (strlen($value) > trim($range[1])))
			$this->_errors[] = $opts['msg'];
	}

	protected function _number($value,$opts)
	{
		if (!is_numeric($value))
			$this->_errors[] = $opts['msg'];
	}

	protected function _email($value,$opts)
	{
		if (!filter_var($value, FILTER_VALIDATE_EMAIL))
			$this->_errors[] = $opts['msg'];
	}

	protected function _url($value,$opts)
	{
		if (!filter_var($value, FILTER_VALIDATE_URL))
			$this->_errors[] = $opts['msg'];
	}

	protected function _format($value,$opts)
	{
		if (!filter_var($value, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>'"'.$opts['pattern'].'"'))))
			$this->_errors[] = $opts['msg'];
	}

	protected function _unique($value,$opts,$model)
	{
		$un = $model::find(array("where" => $opts['field'] . " = '{$value}'"));
		if (count($un) > 0)
			$this->_errors[] = $opts['msg'];
	}

	protected function _custom($value,$opts)
	{

	}
}
?>