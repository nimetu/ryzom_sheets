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
use ArrayAccess;
use Iterator;

/**
 * Allows access to files inside .bnp container
 */
class BnpFile implements Iterator {

	private $filename;

	/**
	 * Is header loaded yet?
	 *
	 * @var bool
	 */
	private $started;

	/**
	 * Associated list of files in this bnp
	 * key - filename
	 * val - file info array
	 *
	 * @var array
	 */
	private $header;

	/**
	 * List of files in this bnp
	 *
	 * @var array
	 */
	private $headerFiles;

	/**
	 * Count of files in this bnp
	 *
	 * @var int
	 */
	private $headerCount;

	/**
	 * Counter for iterator position
	 *
	 * @var int
	 */
	private $headerPosition;

	/**
	 * fopen file handle
	 *
	 * @var resource
	 */
	private $bnp;

	/** @var string */
	private $fileName;

	/** @var int */
	private $fileStart;

	/** @var int */
	private $fileSize;

	/** @var int */
	private $filePos;

	/**
	 * Prepares new bnp file for reading
	 *
	 * @param string $filename full path and filename
	 *
	 * @throws RuntimeException if file not found
	 */
	function __construct($filename) {
		if (!file_exists($filename)) {
			throw new RuntimeException("File [$filename] not found.");
		}

		$this->filename = $filename;

		$this->started = false;
		$this->header = false;
		$this->headerCount = 0;
		$this->headerFiles = array();

		$this->fileName = null;
		$this->fileStart = null;
		$this->fileSize = null;
		$this->filePos = null;
	}

	function __destruct() {
		if ($this->started) {
			fclose($this->bnp);
		}
	}

	/**
	 * Read header from bnp file
	 */
	private function initialize() {
		if ($this->header !== false) {
			return;
		}
		$this->bnp = fopen($this->filename, 'r');

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
		$this->headerCount = count($this->header);
		$this->headerFiles = array_keys($this->header);
		$this->headerPosition = 0;
	}

	/**
	 * Iterator: return current element (file content)
	 *
	 * @return null|string
	 */
	public function current() {
		if (!$this->started) {
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
	public function key() {
		if (!$this->started) {
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
	public function next() {
		if (!$this->started) {
			$this->initialize();
		}
		$this->headerPosition++;
	}

	/**
	 * Iterator: reset iterator to first element
	 */
	public function rewind() {
		if (!$this->started) {
			$this->initialize();
		}
		$this->headerPosition = 0;
	}

	/**
	 * Itarator: check if current position is valid
	 *
	 * @return bool
	 */
	public function valid() {
		if (!$this->started) {
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
	function hasFile($filename) {
		if (!$this->started) {
			$this->initialize();
		}
		return isset($this->header[$filename]);
	}

	/**
	 * Return filenames found in this bnp collection
	 *
	 * @return string[]
	 */
	function getFileNames() {
		if (!$this->started) {
			$this->initialize();
		}
		return array_keys($this->header);
	}

	/**
	 * Return file stats from bnp header (size, start position)
	 *
	 * @param string $filename
	 *
	 * @return int
	 */
	function getFileStat($filename) {
		if (!$this->started) {
			$this->initialize();
		}
		return $this->hasFile($filename) ? $this->header[$filename] : false;
	}

	/**
	 * Return file size
	 *
	 * @param string $filename
	 *
	 * @return int
	 */
	function getFileSize($filename) {
		if (!$this->started) {
			$this->initialize();
		}
		return $this->hasFile($filename) ? $this->header[$filename]['size'] : false;
	}

	/**
	 * Return position in .bnp file where file starts
	 *
	 * @param $filename
	 *
	 * @return int
	 */
	function getFileStart($filename) {
		if (!$this->started) {
			$this->initialize();
		}
		return $this->hasFile($filename) ? $this->header[$filename]['start'] : false;
	}

	/**
	 * Selects filename from bnp and seeks to file start
	 *
	 * @param $filename
	 *
	 * @return bool
	 */
	function select($filename) {
		if ($this->hasFile($filename)) {
			$this->fileName = $filename;
			$this->fileStart = $this->getFileStart($filename);
			$this->fileSize = $this->getFileSize($filename);
			$this->filePos = $this->fileStart;

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
	function seek($pos) {
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
	function read($bytes = null) {
		if ($this->fileName === null) {
			throw new RuntimeException('No file is selected. Need to use select() method first');
		}
		$bytesLeft = $this->fileSize - ($this->filePos - $this->fileStart);
		if ($bytes === null | $bytes < 0 || $bytes > $bytesLeft) {
			$bytes = $bytesLeft;
		}

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
	function readFile($file) {
		if ($this->select($file)) {
			return $this->read();
		}
		return null;
	}
}
