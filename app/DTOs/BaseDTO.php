<?php

namespace App\DTOs;

use ArrayAccess;
use JsonSerializable;

/**
 * Base Data Transfer Object
 *
 * Base class for all DTOs providing common functionality
 */
abstract class BaseDTO implements ArrayAccess, JsonSerializable
{
	/**
	 * Get DTO data as array
	 *
	 * @return array
	 */
	abstract public function toArray(): array;

	/**
	 * Create DTO from array
	 *
	 * @param array $data
	 * @return static
	 */
	public static function fromArray(array $data): static
	{
		return new static($data);
	}

	/**
	 * Implement ArrayAccess interface
	 */
	public function offsetExists(mixed $offset): bool
	{
		return property_exists($this, $offset);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return property_exists($this, $offset) ? $this->$offset : null;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (property_exists($this, $offset)) {
			$this->$offset = $value;
		}
	}

	public function offsetUnset(mixed $offset): void
	{
		if (property_exists($this, $offset)) {
			$this->$offset = null;
		}
	}

	/**
	 * JSON serialization
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * Convert to JSON string
	 */
	public function toJson(int $flags = JSON_UNESCAPED_SLASHES): string
	{
		return json_encode($this->toArray(), $flags);
	}
}
