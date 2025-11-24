<?php

namespace App\Requests;

use CodeIgniter\HTTP\Request;
use App\Exceptions\ValidationException;

/**
 * Form Request
 *
 * Base class for form requests with built-in validation
 */
abstract class FormRequest
{
	/**
	 * The request instance
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 * Custom error messages
	 *
	 * @var array
	 */
	protected $messages = [];

	/**
	 * Constructor
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request = null)
	{
		$this->request = $request ?? service('request');
	}

	/**
	 * Get validation rules
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return $this->rules;
	}

	/**
	 * Get custom error messages
	 *
	 * @return array
	 */
	public function messages(): array
	{
		return $this->messages;
	}

	/**
	 * Validate the request
	 *
	 * @return bool
	 * @throws ValidationException
	 */
	public function validate(): bool
	{
		$validator = service('validation');
		$validator->setRules($this->rules(), $this->messages());

		if (!$validator->run((array) $this->request->getPost())) {
			throw new ValidationException('Validation failed', $validator->getErrors());
		}

		return true;
	}

	/**
	 * Get validated data
	 *
	 * @return array
	 */
	public function validated(): array
	{
		$this->validate();
		$validator = service('validation');
		return $validator->getValidated();
	}

	/**
	 * Get request data
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get(string $key = null, mixed $default = null): mixed
	{
		if ($key === null) {
			return $this->request->getPost();
		}
		return $this->request->getPost($key) ?? $default;
	}
}
