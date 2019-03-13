<?php
/**
 * Inserts wp post and category into the local database, creates a user.
 */
class insert_post
{
    public function __construct(){
		add_action('init', array($this, 'create_post'));
	}

	function insert_category() {
		
		wp_insert_term(
			$_POST['blog_category'],
			'category',
			array(
			  'description'	=> $_POST['description'],
			  'slug' 		=> $_POST['blog_category']
			)
		);
	}

	/**
	 * Create a user for author
	 */
	public function create_blog_user(){
		$username = trim($_POST['blog_author']);
		$password = trim($_POST['blog_password']);
		$email = trim($_POST['blog_email']);

		// First check for existing user.
		$user_exists = username_exists($username);
		if($user_exists) {
			return $user_exists;
		}

		/**
		 * Create user array, for inserting in DB
		 */
		$userdata = array(
			'ID'                    => 0,    //(int) User ID. If supplied, the user will be updated.
			'user_pass'             => $password,   //(string) The plain-text user password.
			'user_login'            => $username,   //(string) The user's login username.
			'user_nicename'         => '',   //(string) The URL-friendly user name.
			'user_url'              => '',   //(string) The user URL.
			'user_email'            => $email,   //(string) The user email address.
			'display_name'          => '',   //(string) The user's display name. Default is the user's username.
			'nickname'              => '',   //(string) The user's nickname. Default is the user's username.
			'first_name'            => '',   //(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
			'last_name'             => '',   //(string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.
			'description'           => '',   //(string) The user's biographical description.
			'rich_editing'          => '',   //(string|bool) Whether to enable the rich-editor for the user. False if not empty.
			'syntax_highlighting'   => '',   //(string|bool) Whether to enable the rich code editor for the user. False if not empty.
			'comment_shortcuts'     => '',   //(string|bool) Whether to enable comment moderation keyboard shortcuts for the user. Default false.
			'admin_color'           => '',   //(string) Admin color scheme for the user. Default 'fresh'.
			'use_ssl'               => '',   //(bool) Whether the user should always access the admin over https. Default false.
			'user_registered'       => '',   //(string) Date the user registered. Format is 'Y-m-d H:i:s'.
			'show_admin_bar_front'  => '',   //(string|bool) Whether to display the Admin Bar for the user on the site's front end. Default true.
			'role'                  => 'contributor',   //(string) User's role.
			'locale'                => '',   //(string) User's locale. Default empty.
		);

		$user_created_id =  wp_insert_user($userdata );

		wp_update_user( array ('ID' => $user_created_id, 'role' => 'contributor') ) ;

		return $user_created_id;
	}

	// Gets $_POST from wp_blog_exporter, to avoid executing more than once
	public function create_post($data){
		$running = false;
		// Get input
		if(isset($data['blog_title'])) {

			$user_inserted_id = $this->create_blog_user();

			$category_description = $data['description'];
			$blog_title = $data['blog_title'];
			$blog_category = $data['blog_category'];
			$user_site = $data['blog_sites'];
			$user_input = $data['wp_blog_exporter_editor'];

			// Check user input
			$user_sanitize = wp_kses_post($user_input);
			
			global $user_ID;
			
			$new_post = array(
				'post_title' => $blog_title,
				'post_content' => $user_sanitize,
				'post_category' => array($data['blog_category']),
				'post_status' => 'private',
				'post_date' => date('Y-m-d H:i:s'),
				'post_author' => $user_inserted_id,
				'post_type' => 'post',
				'comment_status' => 'closed'
			 );
			
			$post_id = wp_insert_post($new_post);
			wp_set_object_terms( $post_id, $data['blog_category'], 'category', false );

			$running = true;
		}
		if (isset($post_id) && $running === true){

				// Redirect user, scripts runs after this. Comment this out for testing.
				// $url = admin_url('wp_blog_exporter');
				// wp_redirect($url);

				// Create a db object of user selected site to send to db_write_post_data
				$check_site = new check_site();
				$db_object = $check_site->user_defined_site($user_site);

				$db_write_post_data = new db_write_post_data();
				$db_write_post_data->connect_to_db($user_site, $db_object, $user_inserted_id, $new_post);

				$is_running = false;

			return $new_post;
			exit;
		}
	}
}