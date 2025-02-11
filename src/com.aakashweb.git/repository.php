<?php
// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace com\aakashweb\wordpress\plugin\git_it_write;
// IMPORTS
// none

// FIXME: refactor and document code


if( ! defined( 'ABSPATH' ) ) exit;


/**
 * 
 */
final class GitRepository {
    /* ====================
     * PROPERTIES
     * ==================== */
    /**
     * name of repository's owner, typically the username in <username>/<repository name>
     */
    public string $ownerName;


    /**
     * name of repository
     */
    public string $repositoryName;


    /**
     * Name of branch
     */
    public string $branchName;


    /**
     * ???
     */
    public $parsedown;


    /**
     * ???
     */
    public $structure = array();


    /* ====================
     * CONSTRUCTOR
     * ==================== */
    /**
     * Constructor
     * 
     * @param   string $ownerName
     * @param   string $repositoryName
     * @param   string $branchName
     */
    public function __construct(
        string $ownerName,
        string $repositoryName,
        string $branchName
    ){
        $this->ownerName        = $ownerName;
        $this->repositoryName   = $repositoryName;
        $this->branchName       = $branchName;

        $this->build_repo_structure();
    }


    /* ====================
     * METHODS
     * ==================== */
    /**
     * ???
     * 
     * @param   string $url
     * 
     * @return  ???
     */
    public function get(
        string $url
    ){

        $general_settings = Git_It_Write_SE_Edition::general_settings();

        $username = $general_settings[ 'github_username' ];
        $access_token = $general_settings[ 'github_access_token' ];
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($username . ':' . $access_token),
            ),
        ); 

        $response = wp_remote_get( $url, $args );

        if( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );

        return $body;

    }


    /**
     * ???
     * 
     * @param   string $url
     * 
     * @return  ???
     */
    public function get_json(
        string $url
    ){
        $content = $this->get( $url );

        if( !$content ){
            return false;
        }

        return json_decode( $content );
    }


    /**
     * ???
     * 
     * 
     * @return  string
     */
    public function tree_url() : string {
        return "https://api.github.com/repos/{$this->ownerName}/{$this->repositoryName}/git/trees/{$this->branchName}?recursive=1";
    }


    /**
     * ???
     * 
     * @param   string $file_path
     * 
     * @return  string
     */
    public function raw_url(
        string $file_path
    ) : string {
        return "https://raw.githubusercontent.com/{$this->ownerName}/{$this->repositoryName}/{$this->branchName}/{$file_path}";
    }


    /**
     * ???
     * 
     * @param   string $file_path
     * 
     * @return  string
     */
    public function github_url( 
        $file_path
    ) : string {
        return "https://github.com/{$this->ownerName}/{$this->repositoryName}/blob/{$this->branchName}/{$file_path}";
    }


    /**
     * ???
     * 
     * @param   ??? $structure
     * @param   ??? $path_split
     * @param   ??? $item
     * 
     * @return  ???
     */
    public function add_to_structure(
        $structure,
        $path_split,
        $item
    ){        
        if( count( $path_split ) == 1 ){

            $full_file_name = $path_split[0];
            $extension = '';

            // Remove the file extension
            $file_slug = explode( '.', $full_file_name );
            if( count( $file_slug ) == 2 ){
                $extension = array_pop( $file_slug );
                $file_slug = implode( '', $file_slug );
            }else{
                $file_slug = $file_slug[0];
            }

            $structure[ $file_slug ] = array(
                'type' => 'file',
                'raw_url' => $this->raw_url( $item->path ),
                'github_url' => $this->github_url( $item->path ),
                'sha' => $item->sha,
                'file_type' => strtolower( $extension )
            );

            return $structure;

        }else{

            $first_dir = array_shift( $path_split );

            if( !array_key_exists( $first_dir, $structure ) ){
                $structure[ $first_dir ] = array(
                    'items' => array(),
                    'type' => 'directory'
                );
            }

            $structure[ $first_dir ][ 'items' ] = $this->add_to_structure( $structure[ $first_dir ][ 'items' ], $path_split, $item );
            return $structure;
        }
    }


    /**
     * ???
     * 
     * @return  ???
     */
    public function build_repo_structure(){

        GIW_Utils::log( 'Building repo structure ...' );

        $tree_url = $this->tree_url();
        $data = $this->get_json( $tree_url );

        if( !$data ){
            GIW_Utils::log( 'Failed to fetch the repository tree! ['. $tree_url .']' );
            return false;
        }

        if( !property_exists( $data, 'tree' ) ){
            GIW_Utils::log( 'Repository not found on Github! ['. $tree_url .']' );
            return false;
        }

        foreach( $data->tree as $item ){
            if( $item->type == 'tree' ){
                continue;
            }

            $path = $item->path;
            $path_split = explode( '/', $path );
            $this->structure = $this->add_to_structure( $this->structure, $path_split, $item );
        }

    }


    /**
     * ???
     * 
     * @param   ??? $item_props
     * 
     * @return  ???
     */
    public function get_item_content(
        $item_props
    ){

        $content = $this->get( $item_props[ 'raw_url' ] );

        if( !$content ){
            return false;
        }

        return $content;
    }


    

    /* ====================
     * MAGIC METHODS
     * ==================== */
    /**
     * @return string String representation of object
     */
    public function __toString(){
        return "{$this->ownerName}/{$this->repositoryName}'#'{$this->branchName}";
    }
}

?>