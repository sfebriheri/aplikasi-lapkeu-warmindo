<?php

namespace App\Exceptions;

/**
 * Resource Not Found Exception
 *
 * Thrown when a requested resource cannot be found
 */
class ResourceNotFoundException extends AppException
{
	public function __construct(
		string $resourceType = 'Resource',
		int $resourceId = null,
		string $errorCode = 'NOT_FOUND'
	) {
		$message = "{$resourceType} not found";
		if ($resourceId !== null) {
			$message .= " (ID: {$resourceId})";
		}

		parent::__construct($message, $errorCode, 404, "The requested {$resourceType} could not be found.");
	}
}
