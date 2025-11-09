<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Controller
 * Handles user authentication, registration, and password reset
 */
class Auth extends CI_Controller
{
	private $email_config = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->model('User_model');

		// Load email configuration from .env
		$this->email_config = $this->_load_email_config();
	}

	/**
	 * Load email configuration from environment
	 */
	private function _load_email_config()
	{
		$env_file = APPPATH . '../.env';
		if (file_exists($env_file)) {
			$env = parse_ini_file($env_file);
		} else {
			$env = array();
		}

		return array(
			'protocol' => isset($env['EMAIL_PROTOCOL']) ? $env['EMAIL_PROTOCOL'] : 'smtp',
			'smtp_host' => isset($env['EMAIL_SMTP_HOST']) ? $env['EMAIL_SMTP_HOST'] : 'ssl://smtp.googlemail.com',
			'smtp_user' => isset($env['EMAIL_SMTP_USER']) ? $env['EMAIL_SMTP_USER'] : '',
			'smtp_pass' => isset($env['EMAIL_SMTP_PASS']) ? $env['EMAIL_SMTP_PASS'] : '',
			'smtp_port' => isset($env['EMAIL_SMTP_PORT']) ? $env['EMAIL_SMTP_PORT'] : 465,
			'mailtype' => 'html',
			'charset' => 'utf-8',
			'newline' => "\r\n",
			'from_name' => isset($env['EMAIL_FROM_NAME']) ? $env['EMAIL_FROM_NAME'] : 'LAPKEU System'
		);
	}

	/**
	 * Login page and process
	 */
	public function index()
	{
		try {
			if ($this->session->userdata('email')) {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Anda tidak bisa mengakses halaman ini</div>');
				redirect('admin');
			}

			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
			$this->form_validation->set_rules('password', 'Password', 'trim|required');

			if ($this->form_validation->run() == false) {
				$data['judul'] = "Halaman Login";
				$data['validation_errors'] = validation_errors();
				$this->load->view('templates/auth_header', $data);
				$this->load->view('auth/login', $data);
				$this->load->view('templates/auth_footer');
			} else {
				$this->_login();
			}
		} catch (Exception $e) {
			$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Terjadi kesalahan: ' . $e->getMessage() . '</div>');
			redirect('auth');
		}
	}

	/**
	 * Process login
	 */
	private function _login()
	{
		try {
			$email = $this->input->post('email', TRUE);
			$password = $this->input->post('password', TRUE);

			$user = $this->User_model->get_user_by_email($email);

			if (!$user) {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email belum terdaftar</div>');
				redirect('auth');
				return;
			}

			if ($user['is_active'] != 1) {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email belum teraktivasi</div>');
				redirect('auth');
				return;
			}

			if (!password_verify($password, $user['password'])) {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Password anda salah</div>');
				redirect('auth');
				return;
			}

			// Set session data
			$session_data = array(
				'email' => $user['email'],
				'role_id' => $user['role_id'],
				'user_id' => $user['id'],
				'nama' => $user['nama']
			);

			$this->session->set_userdata($session_data);
			$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Selamat datang, ' . $user['nama'] . '!</div>');
			redirect('admin');

		} catch (Exception $e) {
			log_message('error', 'Login error: ' . $e->getMessage());
			$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Terjadi kesalahan saat login</div>');
			redirect('auth');
		}
	}

	/**
	 * User registration
	 */
	public function register()
	{
		try {
			if ($this->session->userdata('email')) {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Anda tidak bisa mengakses halaman ini</div>');
				redirect('admin');
			}

			// Set validation rules
			$this->form_validation->set_rules('nama', 'Nama', 'required|trim|min_length[3]|max_length[100]');
			$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
				'is_unique' => 'Email telah terdaftar'
			]);
			$this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[8]|matches[password2]', [
				'matches' => 'Password tidak cocok!',
				'min_length' => 'Password minimal 8 karakter'
			]);
			$this->form_validation->set_rules('password2', 'Konfirmasi Password', 'required|trim|matches[password1]');

			if ($this->form_validation->run() == false) {
				$data['judul'] = 'Registrasi';
				$data['validation_errors'] = validation_errors();
				$this->load->view('templates/auth_header', $data);
				$this->load->view('auth/register', $data);
				$this->load->view('templates/auth_footer');
				return;
			}

			// Handle image upload
			$new_image = 'default.jpg';
			if (!empty($_FILES['gambar']['name'])) {
				$upload_config = array(
					'allowed_types' => 'gif|jpg|jpeg|png',
					'max_size' => 2048,
					'upload_path' => './assets/img/profile/',
					'file_name' => uniqid() . '_'
				);

				$this->load->library('upload', $upload_config);

				if ($this->upload->do_upload('gambar')) {
					$new_image = $this->upload->data('file_name');
				} else {
					log_message('error', 'Upload error: ' . $this->upload->display_errors());
					// Continue with default image
				}
			}

			// Prepare user data
			$user_data = array(
				'nama' => $this->input->post('nama', TRUE),
				'email' => $this->input->post('email', TRUE),
				'gambar' => $new_image,
				'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
				'role_id' => 2,
				'is_active' => 1,
				'date_created' => date('Y-m-d H:i:s'),
				'created_at' => date('Y-m-d H:i:s')
			);

			// Insert user
			if ($this->User_model->insert_user($user_data)) {
				$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Selamat, anda sudah terdaftar! Silakan login.</div>');
				redirect('auth');
			} else {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal mendaftar, silakan coba lagi</div>');
				redirect('auth/register');
			}

		} catch (Exception $e) {
			log_message('error', 'Registration error: ' . $e->getMessage());
			$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Terjadi kesalahan saat registrasi</div>');
			redirect('auth/register');
		}
	}

	/**
	 * Send email for password reset
	 */
	private function _sendEmail($token, $type)
	{
		try {
			$this->load->library('email');
			$this->email->initialize($this->email_config);

			$recipient_email = $this->input->post('email', TRUE);
			$recipient_name = $this->db->select('nama')->where('email', $recipient_email)->limit(1)->get('user')->row()->nama ?? 'User';

			$this->email->from($this->email_config['smtp_user'], $this->email_config['from_name']);
			$this->email->to($recipient_email);

			if ($type == 'forgot') {
				$reset_link = base_url('auth/resetpassword') . '?email=' . urlencode($recipient_email) . '&token=' . urlencode($token);

				$this->email->subject('Reset Password - LAPKEU System');
				$html_body = '<p>Halo ' . $recipient_name . ',</p>';
				$html_body .= '<p>Anda meminta untuk mereset password Anda. Klik link dibawah untuk melanjutkan:</p>';
				$html_body .= '<p><a href="' . $reset_link . '">Reset Password</a></p>';
				$html_body .= '<p>Link ini berlaku selama 1 jam.</p>';
				$html_body .= '<p>Jika Anda tidak meminta reset password, abaikan email ini.</p>';

				$this->email->message($html_body);
			}

			if ($this->email->send()) {
				return true;
			} else {
				log_message('error', 'Email send error: ' . $this->email->print_debugger());
				return false;
			}

		} catch (Exception $e) {
			log_message('error', 'Email exception: ' . $e->getMessage());
			return false;
		}
	}

	public function logout ()
	{
		$this->session->unset_userdata('email');
		$this->session->unset_userdata('role_id');

		$this->session->set_flashdata('message','<div class="alert alert-success" role="alert"> Anda berhasil Logout ! </div>');
		redirect('auth');
	}

	public function lupas()
	{	
		if ($this->session->userdata('email')) {

			$this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">Anda tidak bisa mengakses halaman ini</div>');
			redirect('admin');
		}

		$data['judul'] = "Halaman Lupa Password";
		
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

		if ($this->form_validation->run() == false) {
			$this->load->view('templates/auth_header', $data);
			$this->load->view('auth/lupas');
			$this->load->view('templates/auth_footer');	
		} else {
			$email = $this->input->post('email');
			$user = $this->db->get_where('user',['email' => $email, 'is_active' => 1])->row_array();

			if ($user) {
				
				$token = base64_encode(random_bytes(32));

				$user_token = [
					'email' => $email,
					'token' => $token,
					'date_created' => time()
				];

				$this->db->insert('user_token', $user_token);
				$this->_sendEmail($token, 'forgot');

				$this->session->set_flashdata('message','<div class="alert alert-success" role="alert">Cek email untuk memulihkan password anda</div>');
				redirect('auth/lupas');


			} else {
				$this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">Email belum terdaftar atau belum aktif</div>');
				redirect('auth/lupas');
			}

		}

		
	}

	public function resetpassword()
	{	
		$email = $this->input->get('email');
		$token = $this->input->get('token');

		$user = $this->db->get_where('user',['email' => $email])->row_array();

		if ($user) {
			$user_token = $this->db->get_where('user_token',['token' => $token])->row_array();

			if ($user_token) {
				$this->session->set_userdata('reset_email', $email);
				$this->gantipassword();
			} else {
			$this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">Token Salah</div>');
			redirect('auth');		
			}
		} else {
			$this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">Reset password gagal</div>');
			redirect('auth');
		}
	}

	public function gantipassword()
	{
		if (!$this->session->userdata('reset_email')) {
			redirect('auth');
		}

		$this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]',
			[
				'matches' => 'password tidak cocok!',
				'min_length' => 'password terlalu pendek'
			]);
		$this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

		if ($this->form_validation->run() == false) {
			$data['judul'] = "Ganti Password";
			$this->load->view('templates/auth_header', $data);
			$this->load->view('auth/gantipassword');
			$this->load->view('templates/auth_footer');	
		} else {

			$password = password_hash($this->input->post('password1'), PASSWORD_DEFAULT);
			$email = $this->session->userdata('reset_email');

			$this->db->set('password', $password);
			$this->db->where('email',$email);
			$this->db->update('user');

			$this->session->unset_userdata('reset_email');

			$this->session->set_flashdata('message','<div class="alert alert-success" role="alert">Password berhasil diganti</div>');
			redirect('auth');
		}
		
	}

}
