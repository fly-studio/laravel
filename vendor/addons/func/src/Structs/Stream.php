<?php

namespace Addons\Func\Structs;

use RuntimeException;
use BadMethodCallException;
use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface {

	private $stream;
	private $size;
	private $seekable;
	private $readable;
	private $writable;
	private $uri;
	private $customMetadata;

	/** @var array Hash of readable and writable stream types */
	private static $readWriteHash = [
		'read' => [
			'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
			'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
			'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
			'x+t' => true, 'c+t' => true, 'a+' => true
		],
		'write' => [
			'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
			'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
			'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
			'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true
		]
	];

	public static function create($resource) {
		$self = new static;
		$self->load($resource);
		return $self;
	}

	/**
	 * This constructor accepts an associative array of options.
	 *
	 * - size: (int) If a read stream would otherwise have an indeterminate
	 *   size, but the size is known due to foreknowledge, then you can
	 *   provide that size, in bytes.
	 * - metadata: (array) Any additional metadata to return when the metadata
	 *   of the stream is accessed.
	 *
	 * @param array    $options Associative array of options.
	 *
	 * @throws \InvalidArgumentException if the stream is not a stream resource
	 */
	public function __construct($name = 'php://temp', $options = [])
	{
		if (isset($options['size'])) {
			$this->size = $options['size'];
		}

		$this->customMetadata = isset($options['metadata'])
			? $options['metadata']
			: [];

		$this->stream = fopen($name, 'w+');
		$meta = stream_get_meta_data($this->stream);
		$this->seekable = $meta['seekable'];
		$this->readable = isset(self::$readWriteHash['read'][$meta['mode']]);
		$this->writable = isset(self::$readWriteHash['write'][$meta['mode']]);
		$this->uri = $this->getMetadata('uri');
	}

	public function __get($name)
	{
		if ($name == 'stream') {
			throw new RuntimeException('The stream is detached');
		}

		throw new BadMethodCallException('No value for ' . $name);
	}

	/**
	 * Closes the stream when the destructed
	 */
	public function __destruct()
	{
		$this->close();
	}

	public function __toString()
	{
		try {
			$this->rewind();
			return (string) stream_get_contents($this->stream);
		} catch (\Exception $e) {
			return '';
		}
	}

	public function getContents()
	{
		$contents = stream_get_contents($this->stream);
		if ($contents === false) {
			throw new RuntimeException('Unable to read stream contents');
		}

		return $contents;
	}

	public function getTemporaryContents()
	{
		$off = $this->tell();
		$contents = stream_get_contents($this->stream);

		$this->seek($off);

		return $contents ?: '';
	}

	public function offset()
	{
		return $this->tell();
	}

	public function data()
	{
		return $this->__toString();
	}

	public function length()
	{
		return $this->getSize();
	}

	public function close()
	{
		if (isset($this->stream)) {
			if (is_resource($this->stream)) {
				fclose($this->stream);
			}
			$this->detach();
		}
	}

	public function detach()
	{
		if (!isset($this->stream)) {
			return null;
		}

		$result = $this->stream;
		unset($this->stream);
		$this->size = $this->uri = null;
		$this->readable = $this->writable = $this->seekable = false;

		return $result;
	}

	public function getSize()
	{
		if ($this->size !== null) {
			return $this->size;
		}

		if (!isset($this->stream)) {
			return null;
		}

		// Clear the stat cache if the stream has a URI
		if ($this->uri) {
			clearstatcache(true, $this->uri);
		}

		$stats = fstat($this->stream);
		if (isset($stats['size'])) {
			$this->size = $stats['size'];
			return $this->size;
		}

		return null;
	}

	public function isReadable()
	{
		return $this->readable;
	}

	public function isWritable()
	{
		return $this->writable;
	}

	public function isSeekable()
	{
		return $this->seekable;
	}

	public function eof()
	{
		return !$this->stream || feof($this->stream);
	}

	public function tell()
	{
		$result = ftell($this->stream);

		if ($result === false) {
			throw new RuntimeException('Unable to determine stream position');
		}

		return $result;
	}

	public function truncate($size)
	{
		if (!$this->writable) {
			throw new RuntimeException('Cannot write to a non-writable stream');
		}

		if (is_null($size))
			return false;

		return ftruncate($this->stream, $size);
	}

	public function rewind()
	{
		$this->seek(0);
	}

	public function seek($offset, $whence = SEEK_SET)
	{
		if (!$this->seekable) {
			throw new RuntimeException('Stream is not seekable');
		} elseif (fseek($this->stream, $offset, $whence) === -1) {
			throw new RuntimeException('Unable to seek to stream position '
				. $offset . ' with whence ' . var_export($whence, true));
		}
	}

	public function read($length, $offset = null)
	{
		if (!$this->readable) {
			throw new RuntimeException('Cannot read from non-readable stream');
		}
		if ($length < 0) {
			throw new RuntimeException('Length parameter cannot be negative');
		}

		if (!is_null($offset))
			$this->seek($offset);

		if (0 === $length) {
			return '';
		}

		$string = fread($this->stream, $length);
		if (false === $string) {
			throw new RuntimeException('Unable to read from stream');
		}

		return $string;
	}

	public function write($string)
	{
		if (!$this->writable) {
			throw new RuntimeException('Cannot write to a non-writable stream');
		}

		// We can't know the size after writing anything
		$this->size = null;
		$result = fwrite($this->stream, $string);

		if ($result === false) {
			throw new RuntimeException('Unable to write to stream');
		}

		return $result;
	}

	public function getMetadata($key = null)
	{
		if (!isset($this->stream)) {
			return $key ? null : [];
		} elseif (!$key) {
			return $this->customMetadata + stream_get_meta_data($this->stream);
		} elseif (isset($this->customMetadata[$key])) {
			return $this->customMetadata[$key];
		}

		$meta = stream_get_meta_data($this->stream);

		return isset($meta[$key]) ? $meta[$key] : null;
	}

	public function clear()
	{
		$this->truncate(0);
	}

	public function apply($resource)
	{
		if (is_null($resource))
		{
			$this->truncate(0);
		}
		else if (is_scalar($resource))
		{
			$resource = strval($resource);
			$this->truncate(strlen($resource));
			$this->rewind();
			$this->write($resource);
		}
		else if (is_resource($resource))
		{
			$this->close();
			$this->stream = $resource;
		}
		else if ($resource instanceof StreamInterface)
		{
			if ($resource != $this)
			{
				$this->rewind();
				$resource->rewind();
				if ($resource->getSize() < $this->getSize()) $this->truncate($resource->getSize());
				while(!$resource->eof())
					$this->write($resource->read(1024));
			}
		}
		else
		{
			return false;
		}

		$this->rewind();

		return true;
	}

	public function load($resource)
	{
		return $this->apply($resource);
	}

	public function copy($start = 0)
	{
		$stream = new static;

		$off = $this->tell();
		$this->seek($start);
		while(!$this->eof())
			$stream->write($this->read(1024));
		$this->seek($off);

		return $stream;
	}

}
