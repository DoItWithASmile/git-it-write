<?php

class G2W_Utils{

    public static function log( $message = '' ){
        
        try{
            
            $file = G2W_PATH . 'logs/log.log';
            $line_tmpl = '%s - %s';
            
            $message = is_array( $message ) ? json_encode( $message ) : $message;

            $date = date('m/d/Y H:i');
            $line = sprintf( $line_tmpl, $date, $message );
            
            file_put_contents( $file, $line.PHP_EOL , FILE_APPEND | LOCK_EX );
            
            if( defined( 'G2W_ON_GUI' ) ){
                show_message( $line );
            }

        }catch( Exception $e ){
            
        }
        
    }

    public static function read_log( $total_lines = 500 ){
        // https://stackoverflow.com/a/2961685/306961

        $lines = array();
        $fp = fopen( G2W_PATH . 'logs/log.log', 'r' );

        while( !feof( $fp ) ){
            $line = fgets( $fp, 4096 );
            array_push( $lines, $line );
            if ( count( $lines ) > $total_lines )
                array_shift( $lines );
        }

        fclose( $fp );

        return $lines;

    }

    public static function remove_extension_relative_url( $url, $allowed_file_types ){
        /**
         * Accepts only a relative URL. Starting with . or /
         * ./hello/abcd.md?param=value.md#heading => ./hello/abcd/?param=value.md#heading
        */

        $parts = parse_url( $url );

        if( !isset( $parts[ 'path' ] ) ){
            return $url;
        }

        $path_parts = pathinfo( $parts[ 'path' ] );
        if( !isset( $path_parts[ 'extension' ] ) ){ # No extension already
            return $url;
        }

        if( !in_array( strtolower( $path_parts[ 'extension' ] ), $allowed_file_types ) ){ # Extension is not part of the publish list, then return
            return $url;
        }

        $final_url = array();

        array_push( $final_url, $path_parts[ 'dirname' ] . '/' . $path_parts[ 'filename' ] . '/' );
        if( isset( $parts[ 'query' ] ) ) array_push( $final_url, '?' . $parts[ 'query' ] );
        if( isset( $parts[ 'fragment' ] ) ) array_push( $final_url, '#' . $parts[ 'fragment' ] );

        return implode( '', $final_url );

    }

    public static function get_repo_config_by_full_name( $full_name ){

        $all_repos = Github_To_WordPress::all_repositories();

        $name_split = explode( '/', $full_name );
        if( count( $name_split ) != 2 ){
            return false;
        }

        $username = $name_split[0];
        $repo_name = $name_split[1];

        foreach( $all_repos as $id => $repo ){
            if( $id == 0 ) continue;

            if( $repo[ 'username' ] == $username && $repo[ 'repository' ] == $repo_name ){
                return $repo;
            }
        }

        return false;

    }

}

?>