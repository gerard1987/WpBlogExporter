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
                    'db_host' => '185.182.59.41',
                    'db_user' => 'advanxi310_wp42',
                    'db_pass' => 'Sa(r67D1p!',
                    'db_name' => 'advanxi310_wp42',
                    'db_port' => '3306',
                    'db_prefix' => 'wpv5_'
                ];             
                break;
            case $site_two:
                // echo 'site_two and User_site Match !';
                // Blogtwo
                $db_object = (object) [
                    'db_host' => '185.182.59.41',
                    'db_user' => 'advanxi310_wp865',
                    'db_pass' => '5Stm-46p-0',
                    'db_name' => 'advanxi310_wp865',
                    'db_port' => '3306',
                    'db_prefix' => 'wpmt_'
                ];
                break;
            case $site_three:
                // echo 'site_three and User_site Match !';
                // Blogthree
                $db_object = (object) [
                    'db_host' => '185.182.59.41',
                    'db_user' => 'advanxi310_wp742',
                    'db_pass' => ']7P1XSpp1.',
                    'db_name' => 'advanxi310_wp742',
                    'db_port' => '3306',
                    'db_prefix' => 'wp8i_'
                ];
                break;
            case $site_four:
                // echo 'site_four and User_site Match !';
                // Blogfour
                $db_object = (object) [
                    'db_host' => '185.182.59.41',
                    'db_user' => 'advanxi310_test',
                    'db_pass' => 'YicxTHakw',
                    'db_name' => 'advanxi310_test',
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