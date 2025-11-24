<?php

namespace App\Exceptions;

use Exception;

/**
 * Base Application Exception
 *
 * All custom application exceptions should extend this class.
 */
class AppException extends Exception
{
	/**
	 * Error code for the exception
	 *
	 * @var string
	 */
	protected $errorCode;

	/**
	 * User-friendly error message
	 *
	 * @var string
	 */
	protected $userMessage;

	/**
	 * HTTP status code
	 *
	 * @var int
	 */
	protected $statusCode;

	public function __construct(
		string $message = '',
		string $errorCode = 'INTERNAL_ERROR',
		int $statusCode = 500,
		string $userMessage = null
	) {
		parent::__construct($message);
		$this->errorCode = $errorCode;
		$this->statusCode = $statusCode;
		$this->userMessage = $userMessage ?? $message;
	}

	public function getErrorCode(): string
	{
		return $this->errorCode;
	}

	public function getUserMessage(): string
	{
		return $this->userMessage;
	}

	public function getStatusCode(): int
	{
		return $this->statusCode;
	}
}
