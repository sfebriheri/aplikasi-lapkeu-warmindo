<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
	/**
	 * Do whatever processing this filter needs to do.
	 * By default it should not change the request or response,
	 * unless it needs to.
	 *
	 * @param RequestInterface $request
	 * @param array|null       $arguments
	 *
	 * @return RequestInterface|ResponseInterface|string|void
	 */
	public function before(RequestInterface $request, $arguments = null)
	{
		// Check if user is logged in via session
		if (!session()->get('email')) {
			session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
			return redirect()->to(base_url('auth'));
		}
	}

	/**
	 * Allows After filters to inspect and modify the response
	 * object as needed. This method does not need to do anything
	 * by default. Most of the docs use cases populate the response
	 * itself.
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param array|null        $arguments
	 *
	 * @return void
	 */
	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
	}
}
