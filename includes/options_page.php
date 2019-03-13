<?php
/**
 * Create user interface..
 */
class options_page 
{
	// Constants
	const site_one = 'thelocksmith';
	const site_two = 'sloten-webshop';
	const site_three = 'deslotenexpert';
	const site_four = 'slotenmakerbelgie';

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'the_editor', array($this, 'add_required_attribute_to_wp_editor'));
	}

	function admin_menu() {
		add_options_page(
			'Blogpub',
			'blogPub options',
			'manage_options',
			'blogpub',
			array(		
				$this,
				'settings_page'
			)
		);
	}
	
	function  settings_page() {

		$options_page = new options_page();
	
		$content = '';
		$editor_id = 'blogpubeditor';
		
		// Get category list
		$category_list = new category_list();
		$category_list = $category_list->get_cat_list();
		
		echo 	'
				<form id="blogPub_options" method="post" action="" style="width:100%; display:flex; flex-wrap:wrap; justify-content:center;">
		 	
					<h1>Please upload your blog</h1>
					<label style="width:100%;">Text version of youre blog</label><br>
					<p>Blog title</p>
					<input type="text"  name="blog_title" style="width:100%;" required></input>
					<p>Blog Author</p>
					<input type="text"  name="blog_author" style="width:100%;" required></input>
					<p>Blog E-mail</p>
					<input type="text"  name="blog_email" style="width:100%;" required></input>
					<p>Blog Password</p>
					<input type="text"  name="blog_password" style="width:100%;" required></input>
					<p>Blog Category</p>
					<select required name="blog_category" style="width:100%;">
					<option value="">Choose a category</option>
					';
					foreach ($category_list as $item){
						echo ' <option value=" '; echo trim($item); echo '">'; echo trim($item); echo '</option>';
					};
					
		echo		'
					</select>

						<p>Select target site</p>
						<select required name="blog_sites" style="width:100%;">
						<option value="">[Choose Site Option Below]</option>
						<option value="'; echo $options_page::site_one; echo '">'; echo $options_page::site_one; echo ' </option>
						<option value="'; echo $options_page::site_two ; echo '">'; echo $options_page::site_two; echo '</option>
						<option value="'; echo $options_page::site_three; echo '">'; echo $options_page::site_three; echo '</option>
						<option value="'; echo $options_page::site_four; echo '">'; echo $options_page::site_four; echo '</option>
						</select> 
				';

				wp_editor( $content, $editor_id, $settings = array('textarea_rows'=> '10') );
				$_POST['blogpubeditor'];

		echo 	'
					<input type="submit" name="button" value="Submit" style="width:100%;"/>
					</form>
				';		
	}
	function add_required_attribute_to_wp_editor( $editor ) {
		$editor = str_replace( '<textarea', '<textarea required="required"', $editor );
		return $editor;
	}
}