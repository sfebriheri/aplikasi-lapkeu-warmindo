<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

/**
 * Auth Controller
 * Handles user authentication, registration, and password reset
 */
class Auth extends Controller
{
    protected $userModel;
    protected $session;
    protected $email;
    protected $validation;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
        $this->email = \Config\Services::email();
        $this->validation = \Config\Services::validation();
        helper(['form', 'url']);
    }

    /**
     * Login page and process
     */
    public function index()
    {
        // Redirect if already logged in
        if ($this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">You are already logged in</div>');
            return redirect()->to(base_url('admin'));
        }

        if ($this->request->getMethod() === 'post') {
            return $this->processLogin();
        }

        $data = [
            'judul' => 'Login Page',
            'validation' => $this->validation
        ];

        return view('templates/auth_header', $data)
            . view('auth/login', $data)
            . view('templates/auth_footer');
    }

    /**
     * Process login
     */
    private function processLogin()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->index();
        }

        $email = $this->request->getPost('email', FILTER_SANITIZE_EMAIL);
        $password = $this->request->getPost('password');

        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Email not registered</div>');
            return redirect()->to(base_url('auth'));
        }

        if ($user['is_active'] != 1) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Your account is not activated</div>');
            return redirect()->to(base_url('auth'));
        }

        if (!password_verify($password, $user['password'])) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Incorrect password</div>');
            return redirect()->to(base_url('auth'));
        }

        // Set session data
        $sessionData = [
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'user_id' => $user['id'],
            'nama' => $user['nama'],
            'logged_in' => true
        ];

        $this->session->set($sessionData);
        $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Welcome, ' . esc($user['nama']) . '!</div>');

        return redirect()->to(base_url('admin'));
    }

    /**
     * User registration
     */
    public function register()
    {
        // Redirect if already logged in
        if ($this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">You cannot access this page</div>');
            return redirect()->to(base_url('admin'));
        }

        if ($this->request->getMethod() === 'post') {
            return $this->processRegistration();
        }

        $data = [
            'judul' => 'Registration',
            'validation' => $this->validation
        ];

        return view('templates/auth_header', $data)
            . view('auth/register', $data)
            . view('templates/auth_footer');
    }

    /**
     * Process registration
     */
    private function processRegistration()
    {
        $rules = [
            'nama' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|is_unique[user.email]',
            'password1' => 'required|min_length[8]|matches[password2]',
            'password2' => 'required|matches[password1]'
        ];

        $errors = [
            'email' => [
                'is_unique' => 'Email is already registered'
            ],
            'password1' => [
                'matches' => 'Passwords do not match!',
                'min_length' => 'Password must be at least 8 characters'
            ]
        ];

        if (!$this->validate($rules, $errors)) {
            return $this->register();
        }

        // Handle image upload
        $newImage = 'default.jpg';
        $imageFile = $this->request->getFile('gambar');

        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $validationRule = [
                'gambar' => [
                    'rules' => 'uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png,image/gif]',
                ],
            ];

            if ($this->validate($validationRule)) {
                $newImage = $imageFile->getRandomName();
                $imageFile->move(WRITEPATH . 'uploads/profile', $newImage);
            }
        }

        // Prepare user data
        $userData = [
            'nama' => $this->request->getPost('nama', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'email' => $this->request->getPost('email', FILTER_SANITIZE_EMAIL),
            'gambar' => $newImage,
            'password' => $this->request->getPost('password1'),
            'role_id' => 2,
            'is_active' => 1,
            'date_created' => date('Y-m-d H:i:s')
        ];

        try {
            if ($this->userModel->insert($userData)) {
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Congratulations! Your account has been created. Please login.</div>');
                return redirect()->to(base_url('auth'));
            } else {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Registration failed. Please try again.</div>');
                return redirect()->to(base_url('auth/register'));
            }
        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">An error occurred during registration</div>');
            return redirect()->to(base_url('auth/register'));
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->session->remove(['email', 'role_id', 'user_id', 'nama', 'logged_in']);
        $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">You have been logged out successfully!</div>');
        return redirect()->to(base_url('auth'));
    }

    /**
     * Forgot password page
     */
    public function lupas()
    {
        if ($this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">You cannot access this page</div>');
            return redirect()->to(base_url('admin'));
        }

        if ($this->request->getMethod() === 'post') {
            return $this->processForgotPassword();
        }

        $data = [
            'judul' => 'Forgot Password',
            'validation' => $this->validation
        ];

        return view('templates/auth_header', $data)
            . view('auth/lupas', $data)
            . view('templates/auth_footer');
    }

    /**
     * Process forgot password
     */
    private function processForgotPassword()
    {
        $rules = ['email' => 'required|valid_email'];

        if (!$this->validate($rules)) {
            return $this->lupas();
        }

        $email = $this->request->getPost('email', FILTER_SANITIZE_EMAIL);

        $db = \Config\Database::connect();
        $user = $db->table('user')
            ->where('email', $email)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        if ($user) {
            $token = base64_encode(random_bytes(32));

            $userToken = [
                'email' => $email,
                'token' => $token,
                'date_created' => time(),
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
            ];

            $db->table('user_token')->insert($userToken);
            $this->sendResetEmail($token, $email, $user['nama']);

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Check your email to reset your password</div>');
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Email is not registered or not active</div>');
        }

        return redirect()->to(base_url('auth/lupas'));
    }

    /**
     * Send reset password email
     */
    private function sendResetEmail(string $token, string $email, string $nama)
    {
        try {
            $resetLink = base_url('auth/resetpassword') . '?email=' . urlencode($email) . '&token=' . urlencode($token);

            $this->email->setFrom(env('email.fromEmail', 'noreply@lapkeu.com'), env('email.fromName', 'LAPKEU Warmindo'));
            $this->email->setTo($email);
            $this->email->setSubject('Reset Password - LAPKEU System');

            $message = view('emails/reset_password', [
                'nama' => $nama,
                'reset_link' => $resetLink
            ]);

            $this->email->setMessage($message);

            if (!$this->email->send()) {
                log_message('error', 'Email send error: ' . $this->email->printDebugger());
            }
        } catch (\Exception $e) {
            log_message('error', 'Email exception: ' . $e->getMessage());
        }
    }

    /**
     * Reset password
     */
    public function resetpassword()
    {
        $email = $this->request->getGet('email');
        $token = $this->request->getGet('token');

        $db = \Config\Database::connect();
        $user = $db->table('user')->where('email', $email)->get()->getRowArray();

        if ($user) {
            $userToken = $db->table('user_token')->where('token', $token)->get()->getRowArray();

            if ($userToken) {
                // Check if token is expired
                if (strtotime($userToken['expires_at']) < time()) {
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Reset token has expired</div>');
                    return redirect()->to(base_url('auth'));
                }

                $this->session->set('reset_email', $email);
                return redirect()->to(base_url('auth/gantipassword'));
            } else {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Invalid token</div>');
                return redirect()->to(base_url('auth'));
            }
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Password reset failed</div>');
            return redirect()->to(base_url('auth'));
        }
    }

    /**
     * Change password page
     */
    public function gantipassword()
    {
        if (!$this->session->get('reset_email')) {
            return redirect()->to(base_url('auth'));
        }

        if ($this->request->getMethod() === 'post') {
            return $this->processChangePassword();
        }

        $data = [
            'judul' => 'Change Password',
            'validation' => $this->validation
        ];

        return view('templates/auth_header', $data)
            . view('auth/gantipassword', $data)
            . view('templates/auth_footer');
    }

    /**
     * Process change password
     */
    private function processChangePassword()
    {
        $rules = [
            'password1' => 'required|min_length[8]|matches[password2]',
            'password2' => 'required|matches[password1]'
        ];

        $errors = [
            'password1' => [
                'matches' => 'Passwords do not match!',
                'min_length' => 'Password is too short'
            ]
        ];

        if (!$this->validate($rules, $errors)) {
            return $this->gantipassword();
        }

        $password = $this->request->getPost('password1');
        $email = $this->session->get('reset_email');

        if ($this->userModel->updatePassword($email, $password)) {
            $this->session->remove('reset_email');
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Password has been changed successfully</div>');
            return redirect()->to(base_url('auth'));
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Failed to change password</div>');
            return $this->gantipassword();
        }
    }
}
