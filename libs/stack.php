<?php

namespace libs;

class stack {
	private array $stack=array();

	public function init():void {
		$this->stack=array();
	}
	public function push($value):void {
		$n=$this->count();
		$this->stack[$n]=$value;
	}
	public function pop() {
		$n=$this->count();
		if($n==0) return null;
		$key=$n-1;
		$item = $this->stack[$key];
		unset($this->stack[$n-1]);
		return $item;
	}
	public function count():int {
		return count($this->stack);
	}

}
