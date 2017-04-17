<?php

declare(strict_types=1);

class TextTable
{
	#[private]
	private $row = 0;
	private $column = 0;
	private $padding = 1;
	
	#[private]
	private $data = [];
	private $aligned = [];
	private $chunked = [];
	private $column_sizes = [];
	
	#[serializable] bool
	public $border_top = true;
	public $border_bottom = true;
	public $border_left = true;
	public $border_right = true;
	
	#[serializable] string
	public $border_ud = '-';
	public $border_lr = '|';
	public $joint = '+';
	public $space = ' ';
	public $new_line = '<br/>';
	
	#param int
	#param int
	#param int
	public function __construct(int $row, int $column, int $padding = 1)
	{
		$this->row = $row;
		$this->column = $column;
		$this->padding = $padding * $padding;
	}
    
	#return array
	private function rule(): array
	{
		$col_rules = [];
		$row_rules = [];
		for ($r = 1; $r <= $this->row; $r++)
		{
			$max_row_size = 1.0;
			for ($c = 1; $c <= $this->column; $c++)
			{
				$key = $r . '_' . $c;
				if (isset($this->chunked[$key])) {
					$content = $this->chunked[$key];
					$max_row_size = max(ceil(strlen($this->data[$key]) / $content), $max_row_size);
					
				}
				else {
					if ( ! isset($this->data[$key])) {
						$this->data[$key] = '';
					}
					$content = strlen($this->data[$key]);
				}
				$last = isset($col_rules[$c]) ? $col_rules[$c] : 0;
				$col_rules[$c] = max($last, $content);
			}
			$row_rules[$r] = (int) $max_row_size;
		}
		return array($row_rules, $col_rules);
	}
	
	#param array
	#return string
	private function border(array $column_size): string
	{
		$border = $this->joint;
		foreach ($column_size as $key => $size) {
			$this->column_sizes[$key] = $size + $this->padding;
			$border .= str_repeat($this->border_ud, $size + $this->padding) . $this->joint;
		}
		return $border . $this->new_line;
	}
	
	#param string
	#param int
	#param int
	#return string
	private function content(string $content, int $row, int $column): string
	{
		$postion = 2;
		$key = $row . '_' . $column;
		if (isset($this->aligned[$key])) {
			$_position = $this->aligned[$key][0]; #get first character from postion
			if ($_position == 'l' || $_position == 'L') {
				$postion = 1;
			}
			elseif ($_position == 'r' || $_position == 'R') {
				$postion = 0;
			}
		}
		return str_pad($content, $this->column_sizes[$column], $this->space, $postion);
	}
	
	#param int
	#param int
	#param int
	#return string
	private function start(int $current, int $size, int $line): string
	{
		if ($current == 1) {
			if ($line == ceil($size / 2)) {
				return true;	
			}
		}
		else {
			$new_size = ceil($size / $current);
			if ($new_size == 2 && $size - $new_size < 2) {
				return true;	
			}
			elseif ($line >= $new_size) {
				return true;	
			}
		}
		return false;
	}
	
	
	#param array
	#return string
	private function foundation(array $row_column): string
	{
		$row_size = $row_column[0];
		$column_size = $row_column[1];
		$row_cont = null;
		$data = null;
		$start = false;
		$border = $this->border($column_size);
		
		if ($this->border_top) {
			$row_cont .= $border;
		}
		
		$border_l = ($this->border_left) ? $this->border_lr : '';
		$border_r = ($this->border_right) ? $this->border_lr : '';
					
		for ($r = 1; $r <= count($row_size); $r++)
		{
			$last_chunked = 0;
			$new_chunked = 0;
			for ($line = 1; $line <= $row_size[$r]; $line++) {
				
				$row_cont .= $border_l;
				for ($c = 1; $c <= count($column_size); $c++)
				{
					$key = $r . '_' . $c;
					$data  = $this->data[$key];
					if (isset($this->chunked[$key])) {
						
						$chunk = $this->chunked[$key];
						if (intval(ceil(strlen($data) / $chunk)) == $row_size[$r]) {
							$start = true;
							$data = $this->content(substr($data, $last_chunked, $chunk), $r, $c);
							$last_chunked += $chunk;
						}
						else {
							$begin = intval(ceil(strlen($data) / $chunk));
							if ($this->start($begin, $row_size[$r], $line)) {
								$start = true;
								$data = $this->content(substr($data, $new_chunked, $chunk), $r, $c);
								$new_chunked += $chunk;
							}				
						}
					}
					else
					{
						$data = $this->content($data, $r, $c);
						if ($row_size[$r] > 3) {
							if (floor($row_size[$r] / 2) + 1 == $line) {
								$start = true;	
							}
						}
						elseif ($row_size[$r] > 1) {
							if (floor($row_size[$r] / 2) == $line) {
								$start = true;	
							}
						}
						else {
							$start = true;
						}	
					}
					
					$row_cont .= ($start) ? $data . $border_l : $this->content('', $r, $c) . $border_r;
					$start = false;
				}
				$row_cont .= $this->new_line;
			}
			if ($this->border_bottom) {
				$row_cont .= $border;	
			}
		}
		return $row_cont;
	}
	
	#param bool
	#return string
	public function render(bool $to_file = false): string
	{
		$table = $this->foundation($this->rule());
		if ($to_file) {
			$table = str_replace($this->new_line, PHP_EOL, $table);
		}
		else {
			$table = '<code>' . str_replace($this->space, '&nbsp;', $table) . '</code>';	
		}
		return $table;
	}
	
	
	#param int
	#return TextTable
	public function chunk(int $limit): TextTable
	{
		$last_key = array_keys($this->data);
		$last_key = end($last_key);
		$this->chunked[$last_key] = $limit;
		return $this;
	}
	
	
	#param string
	#return TextTable
	public function align(string $position): TextTable
	{
		$last_key = array_keys($this->data);
		$last_key = end($last_key);
		$this->aligned[$last_key] = $position;
		return $this;
	}
	
	
	#param string
	#param string
	#return TextTable
	public function put(string $row_column, string $contents): TextTable
	{
		list($row, $column) = explode(',', $row_column);
		
		if ((int) $row > $this->row) {
			$format = '%s exceeds specified row. limit(%s)';
			throw new InvalidArgumentException(sprintf($format, $row, $this->row));	
		}
		elseif ((int) $column > $this->column) {
			$format = '%s exceeds specified column. limit(%s)';
			throw new InvalidArgumentException(sprintf($format, $column, $this->column));	
		}
		$this->data[$row . '_' . $column] = $contents;
		
		return $this;
	}
}