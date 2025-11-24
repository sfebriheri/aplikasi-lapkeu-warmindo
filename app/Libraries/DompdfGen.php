<?php

namespace App\Libraries;

use Dompdf\Dompdf;

/**
 * DompdfGen Library for CodeIgniter 4
 *
 * Wrapper library for DOMPDF to convert HTML to PDF
 *
 * @author Jd Fiscus
 * @link https://github.com/iamfiscus/Codeigniter-DOMPDF/
 */
class DompdfGen
{
	protected $dompdf;

	public function __construct()
	{
		$this->dompdf = new Dompdf();
	}

	/**
	 * Set the paper size and orientation
	 *
	 * @param string $size    Paper size (A4, Letter, etc.)
	 * @param string $orient  Orientation (portrait or landscape)
	 * @return void
	 */
	public function setPaper($size = 'A4', $orient = 'portrait')
	{
		$this->dompdf->setPaper($size, $orient);
	}

	/**
	 * Load HTML content
	 *
	 * @param string $html HTML content
	 * @return void
	 */
	public function loadHtml($html)
	{
		$this->dompdf->loadHtml($html);
	}

	/**
	 * Render the PDF
	 *
	 * @return void
	 */
	public function render()
	{
		$this->dompdf->render();
	}

	/**
	 * Stream the PDF to browser
	 *
	 * @param string $filename   Filename for download
	 * @param array  $options    Stream options
	 * @return void
	 */
	public function stream($filename = 'document.pdf', $options = [])
	{
		$this->dompdf->stream($filename, $options);
	}

	/**
	 * Get PDF as string
	 *
	 * @return string
	 */
	public function output()
	{
		return $this->dompdf->output();
	}

	/**
	 * Get the Dompdf instance
	 *
	 * @return Dompdf
	 */
	public function getDompdf()
	{
		return $this->dompdf;
	}
}
