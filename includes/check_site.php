<?php
/**
 * Checks wich site has been selected, gets appropriate host database data, and stores this in db_object
 * @TODO Create admin area for user, where custom db hosts can be added, through admin option page
 */
class check_site
{
    function  user_defined_site($user_site)
    {
        // @TODO create user settings to set blogs from wp admin
        echo 'Blogsite Selected by user :: ' .  $user_site . ' <br>';

        $options_page = new options_page();

        // Set Constants to variables to pass, for site check
        $site_one = $options_page::site_one;
        $site_two = $options_page::site_two;
        $site_three = $options_page::site_three;
        $site_four = $options_page::site_four;

        $db_object = (object) [
                'db_host' => '',
                'db_user' => '',
                'db_pass' => '',
                'db_name' => '',
                'db_port' => '',
                'db_prefix' => ''
        ];

        switch ($user_site)
        {
            case $site_one:
                // echo 'Site one and User_site Match !';
                // Blogone
                $db_object = (object) [
                    'db_host' => '101.101.10.10',
                    'db_user' => 'database_user',
                    'db_pass' => 'database_pass',
                    'db_name' => 'database_name',
                    'db_port' => '3306',
                    'db_prefix' => 'wpv5_'
                ];             
                break;
            case $site_two:
                // echo 'site_two and User_site Match !';
                // Blogtwo
                $db_object = (object) [
                    'db_host' => '101.101.10.10',
                    'db_user' => 'database_user',
                    'db_pass' => 'database_pass',
                    'db_name' => 'database_name',
                    'db_port' => '3306',
                    'db_prefix' => 'wpmt_'
                ];
                break;
            case $site_three:
                // echo 'site_three and User_site Match !';
                // Blogthree
                $db_object = (object) [
                    'db_host' => '101.101.10.10',
                    'db_user' => 'database_user',
                    'db_pass' => 'database_pass',
                    'db_name' => 'database_name',
                    'db_port' => '3306',
                    'db_prefix' => 'wp8i_'
                ];
                break;
            case $site_four:
                // echo 'site_four and User_site Match !';
                // Blogfour
                $db_object = (object) [
                    'db_host' => '101.101.10.10',
                    'db_user' => 'database_user',
                    'db_pass' => 'database_pass',
                    'db_name' => 'database_name',
                    'db_port' => '3306',
                    'db_prefix' => 'wp_'
                ];
                break;
            default:
                exit;
        }
    
    return $db_object;
        
    }
} // End of class