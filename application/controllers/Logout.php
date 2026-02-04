okay is this work now ?
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logout extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load_info();
	}

	public function index()
	{
		$session_id = session_id();

		// Log logout
		$this->log_logout_activity($session_id);

		// Delete session file
		$this->delete_session_file($session_id);

		// Destroy session
		$this->session->sess_destroy();

		// Clear cookies
		$this->clear_session_cookies();

		// Optional: Cleanup old sessions occasionally
		$this->cleanup_expired_sessions();

		redirect(base_url('login'));
	}

	private function log_logout_activity($session_id)
	{
		if ($this->session->userdata('inv_userid')) {
			log_message('info', "User logout - ID: " .
				$this->session->userdata('inv_userid') .
				", Session: $session_id");
		}
	}

	private function delete_session_file($session_id)
	{
		if (empty($session_id)) return;

		$driver = $this->config->item('sess_driver');
		$save_path = $this->config->item('sess_save_path');

		if ($driver !== 'files' || empty($save_path) || !is_dir($save_path)) {
			return;
		}

		// CORRECTED: Use session cookie name as prefix
		$cookie_name = $this->config->item('sess_cookie_name');
		$session_file = rtrim($save_path, '/') . '/' . $cookie_name . $session_id;

		if (file_exists($session_file)) {
			if (@unlink($session_file)) {
				log_message('info', "Deleted session file: $session_file");
			}
		}
	}

	private function clear_session_cookies()
	{
		$cookie_name = $this->config->item('sess_cookie_name');
		$cookie_path = $this->config->item('cookie_path');
		$cookie_domain = $this->config->item('cookie_domain');
		$cookie_secure = $this->config->item('cookie_secure');

		if ($cookie_name) {
			setcookie(
				$cookie_name,
				'',
				time() - 3600,
				$cookie_path,
				$cookie_domain,
				$cookie_secure,
				true
			);
		}
	}

	// Optional cleanup method (5% probability)
	private function cleanup_expired_sessions()
	{
		if (rand(1, 100) > 5) return;

		$save_path = $this->config->item('sess_save_path');
		$expiration = (int) $this->config->item('sess_expiration');
		$cookie_name = $this->config->item('sess_cookie_name');

		if ($expiration === 0 || !is_dir($save_path)) return;

		$files = glob(rtrim($save_path, '/') . '/' . $cookie_name . '*');
		$expire_time = time() - $expiration;
		$deleted = 0;

		foreach ($files as $file) {
			if (is_file($file) && filemtime($file) <= $expire_time) {
				if (@unlink($file)) $deleted++;
			}
		}

		if ($deleted > 0) {
			log_message('info', "Cleanup: Deleted $deleted expired sessions");
		}
	}
}
