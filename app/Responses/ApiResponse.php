<?php

namespace App\Responses;

use CodeIgniter\HTTP\Response;

/**
 * API Response Builder
 *
 * Provides a fluent interface for building JSON API responses
 */
class ApiResponse
{
	/**
	 * Response instance
	 *
	 * @var Response
	 */
	protected $response;

	/**
	 * Response data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * HTTP status code
	 *
	 * @var int
	 */
	protected $statusCode = 200;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->response = service('response');
	}

	/**
	 * Set response status
	 *
	 * @param int $code
	 * @return self
	 */
	public function status(int $code): self
	{
		$this->statusCode = $code;
		return $this;
	}

	/**
	 * Add data to response
	 *
	 * @param mixed $data
	 * @param string $key
	 * @return self
	 */
	public function data(mixed $data, string $key = 'data'): self
	{
		$this->data[$key] = $data;
		return $this;
	}

	/**
	 * Success response
	 *
	 * @param mixed $data
	 * @param string $message
	 * @param int $code
	 * @return array
	 */
	public function success(mixed $data = null, string $message = 'Success', int $code = 200): array
	{
		$response = [
			'success' => true,
			'message' => $message,
			'status_code' => $code,
		];

		if ($data !== null) {
			$response['data'] = $data;
		}

		return $this->response
			->setStatusCode($code)
			->setJSON($response)
			->getJSON();
	}

	/**
	 * Error response
	 *
	 * @param string $message
	 * @param int $code
	 * @param string $errorCode
	 * @param array $errors
	 * @return array
	 */
	public function error(
		string $message = 'Error',
		int $code = 500,
		string $errorCode = 'INTERNAL_ERROR',
		array $errors = []
	): array {
		$response = [
			'success' => false,
			'message' => $message,
			'error_code' => $errorCode,
			'status_code' => $code,
		];

		if (!empty($errors)) {
			$response['errors'] = $errors;
		}

		return $this->response
			->setStatusCode($code)
			->setJSON($response)
			->getJSON();
	}

	/**
	 * Paginated response
	 *
	 * @param array $data
	 * @param int $total
	 * @param int $page
	 * @param int $perPage
	 * @param string $message
	 * @return array
	 */
	public function paginated(
		array $data,
		int $total,
		int $page = 1,
		int $perPage = 15,
		string $message = 'Success'
	): array {
		$lastPage = ceil($total / $perPage);

		$response = [
			'success' => true,
			'message' => $message,
			'data' => $data,
			'pagination' => [
				'total' => $total,
				'per_page' => $perPage,
				'current_page' => $page,
				'last_page' => $lastPage,
				'from' => (($page - 1) * $perPage) + 1,
				'to' => min($page * $perPage, $total),
			],
		];

		return $this->response
			->setStatusCode(200)
			->setJSON($response)
			->getJSON();
	}
}
