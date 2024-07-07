<?php
//
// RyzomSheets - https://github.com/nimetu/ryzom_sheets
// Copyright (c) 2012 Meelis Mägi <nimetu@gmail.com>
//
// This file is part of RyzomSheets.
//
// RyzomSheets is free software; you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// (at your option) any later version.
//
// RyzomSheets is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program; if not, write to the Free Software Foundation,
// Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
//

namespace Nel\Misc;

use RuntimeException;
use Iterator;

/**
 * Allows access to files inside .bnp container
 *
 * @template-implements \Iterator<string,string>
 */
class BnpFile implements Iterator {

	/** @var string */
	private $bnpFileName;

	/**
	 * Associated list of files in this bnp
	 * key - filename
	 * val - file info array
	 *
	 * @var array
	 */
	private $header = array();

	/**
	 * List of files in this bnp
	 *
	 * @var array
	 */
	private $headerFiles = array();

	/**
	 * Counter for iterator position
	 *
	 * @var int
	 */
	private $headerPosition = 0;

	/**
	 * fopen file handle
	 *
	 * @var resource|null
	 */
	private $bnp;

	/** @var string|null */
	private $fileName = null;

	/** @var int */
	private $fileStart = 0;

	/** @var int */
	private $fileSize = 0;

	/** @var int */
	private $filePos = 0;

	/**
	 * Prepares new bnp file for reading
	 *
	 * @param string $bnpFileName
	 *
	 * @throws \RuntimeException if file not found
	 */
	function __construct($bnpFileName) {
		if (!file_exists($bnpFileName)) {
			throw new RuntimeException("File [$bnpFileName] not found.");
		}

		$this->bnpFileName = $bnpFileName;
	}

	function __destruct() {
		if ($this->bnp) {
			fclose($this->bnp);
		}
	}

	/**
	 * Read header from bnp file
	 *
	 * @return void
	 */
	private function initialize() {
		if ($this->bnp) {
			return;
		}
		$this->bnp = fopen($this->bnpFileName, 'r');

		$stats = fstat($this->bnp);
		$size = $stats['size'];

		$s = new MemStream();

		// read last <int> to get position for FAT block
		fseek($this->bnp, $size - 4);
		$buf = fread($this->bnp, 4);
		$s->setBuffer($buf);
		$s->serial_uint32($pos);

		// now read FAT block
		fseek($this->bnp, $pos);
		$fat = fread($this->bnp, $size - $pos);
		$s->setBuffer($fat);
		$s->serial_uint32($nbFiles);

		$this->header = array();
		for ($i = 0; $i < $nbFiles; $i++) {
			$s->serial_byte_string($name);
			$s->serial_uint32($size);
			$s->serial_uint32($start);

			$this->header[$name] = array(
				'size' => $size,
				'start' => $start,
			);
		}

		$this->headerFiles = array_keys($this->header);
		$this->headerPosition = 0;
	}

	/**
	 * Iterator: return current element (file content)
	 *
	 * @return null|string
	 */
	#[\ReturnTypeWillChange]
	public function current() {
		if (!$this->bnp) {
			$this->initialize();
		}
		$file = $this->headerFiles[$this->headerPosition];
		return $this->readFile($file);
	}

	/**
	 * Iterator: return current element (file name)
	 *
	 * @return null|string
	 */
	#[\ReturnTypeWillChange]
	public function key() {
		if (!$this->bnp) {
			$this->initialize();
		}
		if ($this->valid()) {
			return $this->headerFiles[$this->headerPosition];
		}
		return null;
	}

	/**
	 * Iterator: move forward to next element
	 */
	#[\ReturnTypeWillChange]
	public function next() {
		if (!$this->bnp) {
			$this->initialize();
		}
		$this->headerPosition++;
	}

	/**
	 * Iterator: reset iterator to first element
	 */
	#[\ReturnTypeWillChange]
	public function rewind() {
		if (!$this->bnp) {
			$this->initialize();
		}
		$this->headerPosition = 0;
	}

	/**
	 * Itarator: check if current position is valid
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function valid() {
		if (!$this->bnp) {
			$this->initialize();
		}
		return isset($this->headerFiles[$this->headerPosition]);
	}

	/**
	 * Check if $file is in this bnp collection
	 *
	 * @param string $filename
	 *
	 * @return bool
	 */
	public function hasFile($filename) {
		if (!$this->bnp) {
			$this->initialize();
		}
		return isset($this->header[$filename]);
	}

	/**
	 * Return filenames found in this bnp collection
	 *
	 * @return string[]
	 */
	public function getFileNames() {
		if (!$this->bnp) {
			$this->initialize();
		}
		return array_keys($this->header);
	}

	/**
	 * Return file stats from bnp header (size, start position)
	 *
	 * @param string $filename
	 *
	 * @return int|false
	 */
	public function getFileStat($filename) {
		if (!$this->bnp) {
			$this->initialize();
		}
		return $this->hasFile($filename) ? $this->header[$filename] : false;
	}

	/**
	 * Return file size
	 *
	 * @param string $filename
	 *
	 * @return int|false
	 */
	public function getFileSize($filename) {
		if (!$this->bnp) {
			$this->initialize();
		}
		return $this->hasFile($filename) ? $this->header[$filename]['size'] : false;
	}

	/**
	 * Return position in .bnp file where file starts
	 *
	 * @param string $filename
	 *
	 * @return int|false
	 */
	public function getFileStart($filename) {
		if (!$this->bnp) {
			$this->initialize();
		}
		return $this->hasFile($filename) ? $this->header[$filename]['start'] : false;
	}

	/**
	 * Selects filename from bnp and seeks to file start
	 *
	 * @param string $filename
	 *
	 * @return bool
	 */
	public function select($filename) {
		if ($this->hasFile($filename)) {
			$this->fileName = $filename;
			$this->fileStart = $this->header[$filename]['start'];
			$this->fileSize = $this->header[$filename]['size'];
			$this->filePos = $this->fileStart;

			/** @psalm-suppress PossiblyNullArgument */
			fseek($this->bnp, $this->fileStart);
			return true;
		} else {
			$this->fileName = null;
			return false;
		}
	}

	/**
	 * Sets read buffer position for selected file
	 *
	 * @param int $pos
	 *
	 * @return bool
	 */
	public function seek($pos) {
		if ($pos < 0 || $pos > $this->fileSize) {
			return false;
		}
		$this->filePos = $this->fileStart + $pos;
		return true;
	}

	/**
	 * Read next chunk from selected file
	 *
	 * @param int $bytes
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	public function read($bytes = null) {
		if ($this->fileName === null) {
			throw new RuntimeException('No file is selected. Need to use select() method first');
		}
		$bytesLeft = $this->fileSize - ($this->filePos - $this->fileStart);
		if ($bytes === null || $bytes <= 0 || $bytes > $bytesLeft) {
			$bytes = $bytesLeft;
		}

		/** @psalm-suppress PossiblyNullArgument */
		$result = fread($this->bnp, $bytes);
		if ($result !== false) {
			$this->filePos += strlen($result);
		}
		return $result;
	}

	/**
	 * Return entire file in one go
	 *
	 * @param string $file
	 *
	 * @return mixed content of requested file or NULL when file was not found
	 */
	public function readFile($file) {
		if ($this->select($file)) {
			return $this->read();
		}
		return null;
	}
}
