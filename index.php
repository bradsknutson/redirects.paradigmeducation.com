<?php

    require 'conn.php';
    $redirect = $mysqli->real_escape_string( $_GET['redir'] );

    $url = $_SERVER['HTTP_HOST'];
        
    $parts = explode('.', $url);

    if( sizeof($parts) > 2 ) {
        
        $domain = $parts[1] .'.'. $parts[2];
        $sub_domain = $parts[0];

        echo 'Domain: '. $domain .'<br />';
        echo 'Sub Domain: '. $sub_domain .'<br />';
        echo 'Redirect: '. $redirect;
        echo '<br />';
        
    } else {
        
        $domain = $parts[0] .'.'. $parts[1];
        $sub_domain = '';
     
        echo 'Domain: '. $domain .'<br />';
        echo 'Redirect: '. $redirect;
        echo '<br />';
        
    }

    $base = "SELECT b.default_url
                    FROM book b, root_domains c, sub_domains d
                    WHERE c.id = b.domain_id
                    AND d.id = b.sub_id
                    AND c.domain = '". $domain ."'
                    AND d.sub = '". $sub_domain ."'";

    $default_url_result = $mysqli->query($base);
    $default_url_count = $default_url_result->num_rows;

    if( $default_url_count > 0 ) {
        
        $default_url_row = $default_url_result->fetch_array();
        $default_url_result->close();
        
        $default = $default_url_row['default_url'];
        
    } else {
        
        $default = 'http://paradigmeducation.com';
        
    }


    /***************************************************/
    /******************MATCH REDIRECT*******************/
    /***************************************************/
            
    $redir_match = "SELECT a.destination, c.domain, d.sub
                    FROM redirects a, book b, root_domains c, sub_domains d
                    WHERE a.book_id = b.id
                    AND c.id = b.domain_id
                    AND d.id = b.sub_id
                    AND a.string = '". $redirect ."'
                    AND c.domain = '". $domain ."'
                    AND d.sub = '". $sub_domain ."'
                    AND a.deleted = '0'";

    $redir_match_result = $mysqli->query($redir_match);
    $redir_match_count = $redir_match_result->num_rows;

    if( $redir_match_count > 0 ) {

        /***************************************************/
        /***************REDIRECT MATCH FOUND****************/
        /*********************REDIRECT**********************/
        /***************************************************/

        $redir_match_row = $redir_match_result->fetch_array();
        $redir_match_result->close();

        $destination = $redir_match_row['destination'];

        redirect($destination);

    } else {

        /***************************************************/
        /**************NO REDIRECT MATCH FOUND**************/
        /*****************REDIRECT TO ROOT******************/
        /***************************************************/

        redirect($default);

    }


    function redirect($location) {

        header("HTTP/1.1 301 Moved Permanently"); 
        header('Location: '. $location);
        exit;
                
        //echo 'Destination: '. $location;
        
    }


?>