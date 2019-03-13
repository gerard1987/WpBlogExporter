<?php

/**
 * Connect to local DB and get post and category info. Insert in a external site db defined by user.
 */
class db_retrieve_data 
{

    /**
     * Connect to local DB and get tables to extract post, and cat info
     */
    function connect_to_db($user_site, $db_object, $user_inserted_id, $new_post){
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_status = 'private' and post_type = 'post' ");
        $results_terms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}terms");
        $results_term_tax = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}term_taxonomy");
        $results_users = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}users");
        $results_usersmeta = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}usermeta");
        
        // Export table info for processing into ext server.
        $this->connect_to_ext_serv($results, $user_site, $db_object, $results_terms, $results_term_tax, $results_users, $user_inserted_id, $new_post, $results_usersmeta);
    }

    /**
     * Create connection to external server based on user input.
     * Insert post and category info into taxonomy tables into ext database, and connect the two together. 
     */
    function connect_to_ext_serv($results, $user_site, $db_object, $results_terms, $results_term_tax, $results_users, $user_inserted_id, $new_post, $results_usersmeta ){

        // Define prefixes
        define('BLOGP_DB_HOST', $db_object->db_host);
        define('BLOGP_DB_USER', $db_object->db_user);
        define('BLOGP_DB_PASS', $db_object->db_pass);
        define('BLOGP_DB_NAME', $db_object->db_name);
        define('BLOGP_DB_PORT', $db_object->db_port);
        define('PREFIX', $db_object->db_prefix);

        // Define wp table prefixes for sql, use these instead of hard coding prefixes!
        $user_prefix = PREFIX . 'users';
        $usermeta_prefix = PREFIX . 'usermeta';
        $posts_prefix = PREFIX . 'posts';
        $terms_prefix = PREFIX . 'terms';
        $term_taxonomy_prefix = PREFIX . 'term_taxonomy';
        $term_relationships_prefix = PREFIX . 'term_relationships';    
        
        // Store blogPub local table results in arrays
        $post_array = $results;
        $user_array = $results_users;
        $usermeta_array =$results_usersmeta;
        $terms_array = $results_terms;
        $term_taxonomy = $results_term_tax;

        $local_cat_name = $new_post['post_category'][0];

        // Get blogPub local category terms
        foreach ($terms_array as $item) {
            if (trim($item->name) === trim($local_cat_name)){
                $term_id = $item->term_id;
                $name = $item->name;
                $slug = $item->slug;
            } elseif (trim($item->name) !== trim($local_cat_name)){
                // echo "Name does not match : " . "Name : " . $item->name . "  Local cat name : " . $local_cat_name . "<br>";
            } else {
                // echo "Failed both : " . "Name : " . $item->name . "  Local cat name : " . $local_cat_name . "<br>";
            }
        }

        $name = mb_convert_encoding($name, "HTML-ENTITIES", 'UTF-8');

        // Get the blogPub term_taxonomy for the terms item
        foreach ($term_taxonomy as $item){
            // Get current term id
            if ($term_id === $item->term_taxonomy_id){
                    $term_taxonomy_id = $item->term_taxonomy_id;
                    $term_id = $item->term_id;
                    $taxonomy = $item->taxonomy;
                    $description = $item->description;
                    $parent = $item->parent;
                    $count = $item->count;          
            }
        }

        /**
         * Loop through local user table, Create user variables for insert in ext DB
         */
        foreach ($user_array as $item){

            if (is_int($user_inserted_id)){
                $author_id = strval($user_inserted_id);
            }

            if ( $item->ID === $author_id && is_int($user_inserted_id) ){
                // Assign user to post
                $user_id = $item->ID;
                $user_login = $item->user_login;
                $user_pass = $item->user_pass;
                $user_nicename = $item->user_nicename;
                $user_email = $item->user_email;
                $user_status = $item->user_status;
                $display_name = $item->display_name;
            }

        }
        /**
         * Create usermeta variables, for assigning role to user
         */
        global $wpdb;
        $blogpub_prefix = $wpdb->prefix;
        
        foreach ($usermeta_array as $item){
            if ( $item->user_id == $author_id && ($item->meta_key) == ($blogpub_prefix . 'capabilities') ){
                $usermeta_umeta_id = $item->umeta_id;
                $usermeta_user_id = $item->user_id;
                $usermeta_meta_key = PREFIX . 'capabilities';
                $usermeta_meta_value = $item->meta_value;
            }
        }

        // Create post table variables for insertion to ext db
        foreach ($post_array as $item){

            $post_id = $item->ID;
            $post_author = $item->post_author;
            $post_date = $item->post_date;
            $post_date_gmt = $item->post_date_gmt;
            $post_content = $item->post_content;
            $post_title = $item->post_title;
            $post_excerpt = $item->post_excerpt;
            $item->post_status = 'publish';
            $post_status = $item->post_status;
            $comment_status = $item->comment_status;
            $ping_status = $item->ping_status;
            $post_password = $item->post_password;
            $post_name = $item->post_name;
            $to_ping = $item->to_ping;
            $pinged = $item->pinged;
            $post_modified = $item->post_modified;
            $post_modified_gmt = $item->post_modified_gmt;
            $post_content_filtered = $item->post_content_filtered;
            $post_parent = $item->post_parent;
            $menu_order = $item->menu_order;
            $post_type = $item->post_type;
            $post_mime_type = $item->post_mime_type;
            $comment_count = $item->comment_count;
        }
        

        // Fix for breaking sql statement, if apostrophe is in the content
        $post_content = str_replace("'", "''", $post_content);

        try {
            $connection = new PDO( 'mysql:host=' . BLOGP_DB_HOST . '; dbname=' . BLOGP_DB_NAME, BLOGP_DB_USER, BLOGP_DB_PASS );
            // set charset of DB for proper post_content
            $connection->exec("set names utf8");
            // set the PDO error mode to exception
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // For debugging the connection
            echo 'Connected successfully';

            // Get Current user_login of user, to assign to post
            $existing_users = [];
            $current_user_id = $connection->query("SELECT * FROM `wp_users` ");
            foreach($current_user_id as $item){
                array_push($existing_users, trim( $item[0] ) );
            }
            
            trim($user_id);

            /**
             * Check if current user, exists in target DB
             */
            if (!in_array($user_id, $existing_users) && !empty($user_login)){
                // Insert User into ext DB
                $sql_user = "INSERT IGNORE INTO " . "$user_prefix" . "(
                    ID,
                    user_login,
                    user_pass,
                    user_nicename,
                    user_email,
                    user_status,
                    display_name
                )
                VALUES (
                    '$user_id',              
                    '$user_login',
                    '$user_pass',
                    '$user_nicename',
                    '$user_email',
                    '$user_status',
                    '$display_name'
                )";
                
                // Insert User Role into ext DB
                $sql_usermeta = "INSERT IGNORE INTO " . "$usermeta_prefix" . "(
                    umeta_id,
                    user_id,
                    meta_key,
                    meta_value
                )
                VALUES (
                    '$usermeta_umeta_id',
                    '$usermeta_user_id',
                    '$usermeta_meta_key',
                    '$usermeta_meta_value'
                )";
                
                var_dump($sql_usermeta);

                // //Prepare our statement.
                $statement = $connection->prepare($sql_user);
                $statement_meta = $connection->prepare($sql_usermeta);
                // //Execute the statement and insert our values.
                $inserted = $statement->execute();
                $inserted_meta = $statement_meta->execute();       
            } else if (in_array($user_id, $existing_users) && !empty($user_id)) {
                $post_author = $user_id;
            }
            
            // Insert post
            $sql = "INSERT INTO " . $posts_prefix . "(
                post_author,
                post_date, 
                post_date_gmt, 
                post_content, 
                post_title, 
                post_excerpt, 
                post_status, 
                comment_status, 
                ping_status, 
                post_password, 
                post_name,
                to_ping,
                pinged,
                post_modified,
                post_modified_gmt,
                post_content_filtered,
                post_parent,
                menu_order,
                post_type,
                post_mime_type,
                comment_count
                  )
            VALUES (
                '$post_author',
                '$post_date',
                '$post_date_gmt',
                '$post_content',
                '$post_title',
                '$post_excerpt',
                '$post_status',
                '$comment_status',
                '$ping_status',
                '$post_password',
                '$post_name',
                '$to_ping',
                '$pinged',
                '$post_modified',
                '$post_modified_gmt',
                '$post_content_filtered',
                '$post_parent',
                '$menu_order',
                '$post_type',
                '$post_mime_type',
                '$comment_count'
            )";

            //Prepare our statement.
            $statement = $connection->prepare($sql);
            //Execute the statement and insert our values.
            $inserted = $statement->execute();                     

            // Retrieve local post id from ext db, get correct post
            $sql_local_post = $connection->query("SELECT * FROM `$posts_prefix` WHERE post_type = 'post' ");
            foreach ($sql_local_post as $item){
                if ($item['post_title'] === $post_title){
                    $local_post_id = $item['ID'];
                }
            }

            $sql_external_terms = $connection->query("SELECT * FROM `$terms_prefix`");
            $name_array = [];

            // create compare array, wether category already exists in wp_terms insert if not          
            foreach ($sql_external_terms as $item){
                array_push($name_array, $item["name"]);
            }

            // Check wether category already exists, insert if not
            if (!in_array($name, $name_array)){

                // Insert category in wp terms
                $sql_cat = "INSERT IGNORE INTO " . "$terms_prefix" . "(
                    term_id,
                    name,
                    slug
                )
                VALUES (
                    '$term_id',
                    '$name',
                    '$slug'
                )";

                $statement_cat = $connection->prepare($sql_cat);
                $inserted_cat = $statement_cat->execute();

                // insert category into wp term_taxonomy
                $description = $name;
                $sql_term_taxonomy = "INSERT INTO " . "$term_taxonomy_prefix" . "(
                    term_taxonomy_id,
                    term_id,
                    taxonomy,
                    description,
                    parent,
                    count
                     )
                     VALUES (
                         '$term_taxonomy_id',
                         '$term_id',
                         '$taxonomy',
                         '$description',
                         '$parent',
                         '$count'
                    )";
                    $statement_term_tax = $connection->prepare($sql_term_taxonomy);
                    $inserted_term_tax = $statement_term_tax->execute();
            } 
            
            // Get external taxonomy categories        
            $sql_external_term_taxonomy = $connection->query("SELECT * FROM `$term_taxonomy_prefix`");
            $sql_external_terms = $connection->query("SELECT * FROM `$terms_prefix`");

            // If category matches external db in wp terms
            foreach ($sql_external_terms as $item) {
                if ($name === $item['name'] && in_array($name, $name_array) ){
                    $existing_term_taxonomy_id = $item['term_id'];
                    $existing_term_name = $item['name'];
                }
            }

            // Get the matching name id from terms, and check if it exists in term_taxonomy
            foreach ($sql_external_term_taxonomy as $item){

                if ($existing_term_taxonomy_id == $item['term_id']){
                    $term_taxonomy_id = $item['term_taxonomy_id'];
                }
                
                if ($term_taxonomy_id === $item['term_taxonomy_id']){
                    // Insert term relationship
                    $sql_term_relationship = "INSERT IGNORE INTO " . "$term_relationships_prefix" . "(
                        object_id,
                        term_taxonomy_id,
                        term_order
                        ) 
                        VALUES (
                            '$local_post_id',
                            '$term_taxonomy_id',
                            '0'
                        )";
                }
                
            }        

            //Prepare our statement.
            $statement_term_rel = $connection->prepare($sql_term_relationship);
            $statement_post = $connection->prepare($sql_local_post);
            //Execute the statement and insert our values.
            $inserted_tax = $statement_term_rel->execute();

            //Because PDOStatement::execute returns a TRUE or FALSE value,
            //we can easily check to see if our insert was successful.
            if($inserted){
                echo 'Row inserted!<br>';
            } elseif($inserted_cat){
                echo 'Category Row inserted!<br>';
            }
            
            exit;            
        }
        catch(PDOException $error) {
            echo 'Error: ' . $error->getMessage() . "<br/>";
            exit;
        }

    } // end of func
} // end of class