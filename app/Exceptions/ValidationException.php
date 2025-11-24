<?php

namespace App\Exceptions;

/**
 * Validation Exception
 *
 * Thrown when data validation fails
 */
class ValidationException extends AppException
{
	/**
	 * Validation errors
	 *
	 * @var array
	 */
	protected $errors = [];

	public function __construct(
		string $message = 'Validation failed',
		array $errors = [],
		string $errorCode = 'VALIDATION_ERROR'
	) {
		parent::__construct($message, $errorCode, 422, $message);
		$this->errors = $errors;
	}

	public function getErrors(): array
	{
		return $this->errors;
	}
}
