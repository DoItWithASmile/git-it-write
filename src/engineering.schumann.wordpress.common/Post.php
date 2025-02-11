<?php
// TODO: replace GIW_Utils::log with other logger
// FIXE: ...

// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace engineering\schumann\wordpress\common;
// IMPORTS
use engineering\schumann\common\helper\BooleanHelper;
use engineering\schumann\common\DataContainer;
use engineering\schumann\common\SE_CONSTANTS;
use engineering\schumann\common\E_SE_RESULT_TYPE;
use engineering\schumann\wordpress\common\WP_CONSTANTS;


if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Representation of a WordPress post
 */
final class Post {
    /* ====================
     * CONSTANTS
     * ==================== */
    // WP native
    const PROPERTY_NAME_WP_ID               = 'ID';
    const PROPERTY_NAME_WP_POST_TITLE       = 'post_title';
    const PROPERTY_NAME_WP_POST_AUTHOR      = 'post_author';
    const PROPERTY_NAME_WP_POST_NAME        = 'post_name';
    const PROPERTY_NAME_WP_META_INPUT       = 'meta_input';
    const PROPERTY_NAME_WP_POST_TYPE        = 'post_type';
    const PROPERTY_NAME_WP_POST_STATUS      = 'post_status';

    // WP extension
    // .. sticky
    const PROPERTY_NAME_EX_STICKY           = 'sticky';
    const PROPERTY_DEFAULT_STICKY           = false;
    // .. slug
    const PROPERTY_NAME_EX_SLUG             = 'slug';
    const PROPERTY_DEFAULT_SLUG             = null;
    // .. custom fields
    const PROPERTY_NAME_EX_CUSTOM_FIELD     = 'custom_fields';
    const PROPERTY_DEFAULT_CUSTOM_FIELDS    = array();
    // .. taxonomy
    const PROPERTY_NAME_EX_TAXONOMY_MAP     = 'taxonomy';
    const PROPERTY_DEFAULT_TAXONOMY_MAP     = array();
    // .. featured image
    const PROPERTY_NAME_EX_FEATURED_IMAGE   = 'featured_image';
    const PROPERTY_DEFAULT_FEATURED_IMAGE   = null;
    

    /**
     * Array of all attributes support by post natively in WordPress
     * 
     * @see https://developer.wordpress.org/reference/functions/wp_insert_post/
     */
    const WORDPRESS_POST_ATTRIBUTE_LIST = [
        Post::PROPERTY_NAME_WP_ID, // int
        Post::PROPERTY_NAME_WP_POST_TITLE, // string
        Post::PROPERTY_NAME_WP_POST_NAME, // string
        Post::PROPERTY_NAME_WP_POST_AUTHOR, // int
        'post_date', // string
        'post_date_gmt', // string
        'post_modified', // string
        'post_modified_gmt', // string
        'post_content', // string
        'post_content_filtered', // string
        'post_excerpt', // string
        Post::PROPERTY_NAME_WP_POST_STATUS, // string
        Post::PROPERTY_NAME_WP_POST_TYPE, // string
        'post_password', // string
        'post_category', // int[]
        'post_parent', // int
        'post_mime_type', // string

        'page_template', // string

        'comment_status', // string

        'ping_status', // string
        'to_ping', // string
        'pinged', // string
        'menu_order', // int
        'guid', // string
        'import_id', // int
        'tags_input', // array
        'tax_input', // array
        Post::PROPERTY_NAME_WP_META_INPUT, // array
    ];


    /**
     * array( alias => setting ) to map property names of different sources or versions
     */
    const WORDPRESS_POST_ATTRIBUTE_ALIAS_MAP = [
        'stick_post'                            => Post::PROPERTY_NAME_EX_STICKY,
        'isSticky'                              => Post::PROPERTY_NAME_EX_STICKY,

        'author'                                => Post::PROPERTY_NAME_WP_POST_AUTHOR,

        Post::PROPERTY_NAME_EX_SLUG             => Post::PROPERTY_NAME_WP_POST_NAME,

        Post::PROPERTY_NAME_EX_CUSTOM_FIELDS    => Post::PROPERTY_NAME_WP_META_INPUT,

        'image'                                 => Post::PROPERTY_NAME_EX_FEATURED_IMAGE,
        'image_url'                             => Post::PROPERTY_NAME_EX_FEATURED_IMAGE
    ];


    /**
     * array( property name => default value ) to set default values of a post
     */
    const WORDPRESS_POST_ATTRIBUTE_DEFAULTS = [
        // .. ID
        Post::PROPERTY_NAME_WP_ID               => null,
        // .. post type
        Post::PROPERTY_NAME_WP_POST_TYPE        => 'post',
        // .. post statys
        Post::PROPERTY_NAME_WP_POST_STATUS      => 'draft',
        // .. sticky
        Post::PROPERTY_NAME_EX_STICKY           => Post::PROPERTY_DEFAULT_STICKY,
        // .. slug
        Post::PROPERTY_NAME_EX_SLUG             => Post::PROPERTY_DEFAULT_SLUG,
        // .. custom fields
        Post::PROPERTY_NAME_EX_CUSTOM_FIELD     => Post::PROPERTY_DEFAULT_CUSTOM_FIELDS,
        // .. taxonomy
        Post::PROPERTY_NAME_EX_TAXONOMY_MAP     => Post::PROPERTY_DEFAULT_TAXONOMY_MAP,
        // .. featured image
        Post::PROPERTY_NAME_EX_FEATURED_IMAGE   => Post::PROPERTY_DEFAULT_FEATURED_IMAGE
    ];
    
    
    /* ====================
     * PROPERTIES
     * ==================== */
    /**
     * meta data of post
     */
    public engineering\schumann\common\DataContainer $metadata;


    /**
     * last error
     */
    private ?string $_last_error = null;


    /* ====================
     * GETTER & SETTER
     * ==================== */
    /**
     * Check if there is an error present.
     * Returns true if there is an error present, false otherwise
     * 
     * @return bool true, if there is an recorded error present
     */
    public function hasError() : bool {
        return !empty( $this->$_last_error );
    }


    /**
     * Returns last error, if any
     * 
     * @return string last error set
     */
    public function getError() : string {
        return $this->_last_error;
    }


    /**
     * Sets the last error
     * 
     * @param object $error last error to be stored
     */
    private function setError( 
        $error
    ) : void {
        // == GUARDS ==
        if ( is_wp_error( $error ) ){
            $this->setErrorWP_Error( $error );
            return;
        } 

        // == LOGIC ==
        $this->_last_error = $error;
    }


    /**
     * Sets the last error
     * 
     * @param string $error last error to be stored
     */
    private function setErrorString( 
        string $error
    ) : void {
        $this->_last_error = $error;
    }


    /**
     * Sets the last error
     * 
     * @param WP_Error $error last error to be stored
     */
    private function setErrorWP_Error( 
        WP_Error $error
    ) : void {
        $this->_last_error = $error->get_error_message();
    }


    /**
     * Clears last error
     */
    private function clearError() : void {
        $this->_last_error = null;
    }


    /**
     * Checks if post is sticky
     * 
     * @return bool true, if post is sticky
     */
    public function isSticky() : bool {
        return $this->metadata->getPropertyValue( Post::PROPERTY_NAME_EX_STICKY );
    }


    /**
     * returns the slug of a post
     * 
     * @return string slug of a post
     */
    public function getSlug() : string {
        return $this->metadata->getPropertyValue( Post::PROPERTY_NAME_EX_SLUG );
    }


    /**
     * returns the post's ID. Null = post wasn't created yet
     * 
     * @return string ID of a post
     */
    public function getID() : string {
        return $this->metadata->getPropertyValue( Post::PROPERTY_NAME_WP_ID );
    }


    /**
     * returns the post's ID. Null = post wasn't created yet
     * 
     * @return string ID of a post
     */
    public function setID(
        string $ID
    ) : void {
        $this->metadata->setPropertyValue( Post::PROPERTY_NAME_WP_ID, $ID );
    }    


    /**
     * returns the array( taxonomy name => taxonomy entry )
     * 
     * @return array
     */
    public function getTaxonomyMap() : array {
        return $this->metadata->getPropertyValue( Post::PROPERTY_NAME_EX_TAXONOMY_MAP );
    }


    /**
     * returns the featured image
     * 
     * @return string
     */
    public function getFeaturedImageUrl() : string {
        return $this->metadata->getPropertyValue( Post::PROPERTY_NAME_EX_FEATURED_IMAGE );
    }


    /**
     * An array of elements that make up a post to update or insert.
     * 
     * @return array ( attribute name => value )
     * 
     * @see https://developer.wordpress.org/reference/functions/wp_insert_post/
     */
    public function getWP_Post_AttributeMap() : array {
        return $this->metadata->pick( Post::WORDPRESS_POST_ATTRIBUTE_LIST );
    }


    /* ====================
     * CONSTRUCTOR
     * ==================== */
    /**
     * Constructor
     */
    public function __construct(){
        $this->metadata = new engineering\schumann\common\DataContainer();
        $this->metadata->propertyAliasMap   = WORDPRESS_POST_ATTRIBUTE_ALIAS_MAP;
        $this->metadata->propertyDefaultMap = WORDPRESS_POST_ATTRIBUTE_DEFAULTS;
    }


    /* ====================
     * METHODS
     * ==================== */
    /**
     * publishes a post by creating or updating the post itself and running additional actions.
     * 
     * NOTE: only the creation of the post can fail which results in aborting this operation.
     *       other steps can fail as well, but there is no rollback for this, only a roll forward.
     *       check log files for details.
     */
    public function publish() : E_SE_RESULT_TYPE {
        // create post first
        if ( $this->createOrUpdate() == E_SE_RESULT_TYPE::FAIL ){
            // == FAIL ==
            return E_SE_RESULT_TYPE::FAIL;
        }

        // post published
        GIW_Utils::log( '---------- ' . $post . ' Published ----------' );

        // update taxonomy
        // CAN fail or skip
        $post->updateTaxonomies();

        // update stick post
        // CANNOT fail
        $post->updateStickyness();

        // update featured image
        // CAN fail or skip
        $post->updateFeaturedImage();

        // == SUCCESS? ==
        return E_SE_RESULT_TYPE::SUCCESS;
    }


    /**
     * creates or updates the post
     * 
     * NOTE: SKIPPED result only indicates, that the post already existed but NOT that nothing has changed.
     * 
     * @return bool true, if no errors occured
     */
    public function createOrUpdate() : E_SE_RESULT_TYPE {
        // clear error
        $this->clearError();

        // get metadata
        $postAttributeMap = $this->getWP_Post_Attributes();

        // try to insert post
        // see https://developer.wordpress.org/reference/functions/wp_insert_post/
        $post_id = wp_insert_post( $postAttributeMap, WP_CONSTANTS::RETURN_WP_ERROR_CLASS );

        // GUARD: error
        if ( is_wp_error( $post_id ) ){
            // store last error
            $this->setErrorWP_Error( $post_id );
        }
        else if ( empty( $post_id ) ){
            // store last error
            $this->setErrorString( "post ID is empty" );
        }

        // GUARD: abort
        if ( $this->hasError() )
        {
            // log error
            GIW_Utils::log( $this . ' ERROR: Failed to publish post - ' . $this->getError() );

            // == FAIL ==
            return E_SE_RESULT_TYPE::FAIL;
        }

        // GUARD: nothing done
        if ( $this->getId() == $post_id ){
            // == SKIPPED ==
            // NOTE: this only means, the post already existed. It doesn't mean that attributes weren't updated!
            return E_SE_RESULT_TYPE::SKIPPED;
        }

        // update metadata
        $this->setId( $post_id );

        // == SUCCESS ==
        return E_SE_RESULT_TYPE::SUCCESS;
    }


    /**
     * Updates post stickyness
     * 
     * @return bool true, if no error occured
     */
    public function updateStickyness() : E_SE_RESULT_TYPE {
        // clear error
        $this->clearError();

        // == GUARDS ==
        // NOTE: Wordpress already takes care of only performing actions if something changed. We don't need to worry about it.

        // == LOGIC ==
        // stick post
        // see https://developer.wordpress.org/reference/functions/stick_post/
        if ( $this->isSticky() )
        {
            GIW_Utils::log( $this . ' SET sticky' );
            stick_post( $new_post_id );
        }
        // unstick post
        // see https://developer.wordpress.org/reference/functions/unstick_post/
        else
        {
            GIW_Utils::log( $this . ' UNSET sticky' );
            unstick_post( $new_post_id );
        }

        // == SUCCESS ==
        return E_SE_RESULT_TYPE::SUCCESS;
    }


    /**
     * Adds terms of an taxonomy to the post
     * 
     * @param string $taxonomyName name of taxonomy to add terms for
     * @param string|array $terms single term or array of terms to add
     * 
     * @return bool true, if no error occured
     */
    public function addTaxonomy(
        string $taxonomyName,
        $terms
    ) : E_SE_RESULT_TYPE {
        // clear error
        $this->clearError();

        // == GUARDS ==
        // .. need ID
        $this->assert_hasID();
        // .. terms has incorrect type
        if ( !is_string( $terms ) && !is_array( $terms ) ){
            throw new Exception( "terms need to be provided as string or array" );
        }
        // .. taxonomy name does not exist
        if( !taxonomy_exists( $taxonomyName ) ){
            GIW_Utils::log( $this . ' SKIP taxonomy: [' . $taxonomyName . '] - does not exist.' );
            // == SKIPPED ==
            return E_SE_RESULT_TYPE::SKIPPED;
        }

        // CONTENT
        // add terms to post
        GIW_Utils::log( $this . ' ADD taxonomy: [' . $taxonomyName . ']' );
        $set_tax = wp_set_object_terms( $this->getId(), $terms, $taxonomyName );
        if( is_wp_error( $set_tax ) ){
            // store last error
            $this->setErrorWP_Error( $set_tax );

            GIW_Utils::log( $this . ' FAILED to add taxonomy: [' . $taxonomyName . '] - ' . $this->getError() );
        }

        // == SUCCESS? ==
        return $this->hasError() ? E_SE_RESULT_TYPE::FAIL : E_SE_RESULT_TYPE::SUCCESS;
    }


    /**
     * clears all taxonomy relationships to current post
     * 
     * @return bool true, if no errors occured
     */
    public function clearTaxonomies() : E_SE_RESULT_TYPE {
        // clear error
        $this->clearError();

        // get post type
        $postType = $this->metadata->getPropertyValue( Post::PROPERTY_NAME_WP_POST_TYPE );

        // get all taxonomies for this post type
        $taxonomyNames = get_object_taxonomies( array( Post::PROPERTY_NAME_WP_POST_TYPE => $postType ) );

        // delete relationships to this post
        wp_delete_object_term_relationships( $this->getId(), $taxonomyNames );

        // == SUCCESS? ==
        return $this->hasError() ? E_SE_RESULT_TYPE::FAIL : E_SE_RESULT_TYPE::SUCCESS;
    }


    /**
     * Updates the taxnomoy for post
     * 
     * @param  bool $clearOld determines if old taxonomy relationships shall be removed first
     * 
     * @return bool true, if no errors occured
     */
    public function updateTaxonomies( 
        bool $clearOld = true 
    ) : E_SE_RESULT_TYPE {
        // clear error
        $this->clearError();

        // clear old taxonomies if requested
        if ( $clearOld ){
            $this->clearTaxonomies();
        }

        // get taxonomy from metadata
        $taxonomyMap = $this->getTaxonomyMap();

        // GUARD: nothing to do
        if( empty( $taxonomyMap ) ){
            // == SKIPPED ==
            return E_SE_RESULT_TYPE::SKIPPED;
        }

        // Set the post taxonomy
        $result = E_SE_RESULT_TYPE::SUCCESS;
        foreach( $taxonomyMap as $taxonomyName => $terms ){
            if ( $this->addTaxonomy( $taxonomyName, $terms ) == E_SE_RESULT_TYPE::FAIL ){
                $result = E_SE_RESULT_TYPE::FAIL;
            }
        }

        // == SUCCESS? ==
        return $result;
    }


    /**
     * Upload the featured image, if any was set
     * @see source: fullbright/git-it-write, commit d95887a087c7f5f854eed0f82ec68f151c72da74
     * 
     * @return bool      true, if no errors occured
     * 
     * @throws exception if ID is not set
     */
    public function updateFeaturedImage() : E_SE_RESULT_TYPE{
        // clear error
        $this->clearError();

        // == GUARDS ==
        $this->assert_hasID();

        // == LOGIC ==
        $featured_image = $this->getFeaturedImageUrl();

        // GUARD: no featured image set
        if ( empty( $featured_image ) ){
            GIW_Utils::log( $this . ' SKIP - Featured image not set/empty');
            // == SKIPPED ==
            return E_SE_RESULT_TYPE::SKIPPED;
        }

        // upload featured image
        GIW_Utils::log( $this . ' UPLOAD featured image [' . $featured_image . ']' );
// FIXME: refactor image upload
        $this->upload_featured_image( $featured_image, $this->getID() );

        // done
        return $this->hasError() ? E_SE_RESULT_TYPE::FAIL : E_SE_RESULT_TYPE::SUCCESS;
    }
    

    /* ====================
     * MAGIC METHODS
     * ==================== */
    /**
     * @return string String representation of object
     */
    public function __toString(){
        return 'Post [' . $this->getId() . ': ' . $this->getSlug() . ']';
    }


    /**
     * forward __get to metadata
     */
    public function __get(
        string $propertyName
    ) : object {
        return $this->metadata->$propertyName;
    }


    /**
     * forward __set to metadata
     */
    public function __set(
        string $propertyName, 
        object $value
    ) : void {
        $this->metadata->$propertyName = $value;
    }


    /**
     * forward __isset to metadata
     */
    public function __isset(
        string $propertyName
    ) : bool {
        return isset( $this->metadata->$propertyName );
    }


    /**
     * forward __unset to metadata
     */
    public function __unset(
        string $propertyName
    ) : void {
        unset( $this->metadata->$propertyName );
    }


    /* ====================
     * ASSERTS & GUARDS
     * ==================== */
    /**
     * ensures post has ID = was saved / updated or retrieved
     */
    private function assert_hasID() : void {
        // == GUARDS ==
        if ( empty( $this->getID() ) ){
            throw new Exception( $this . " does not have an ID" );
        }

        // == SUCCESS ==
    }


    /**
     * ensures metadata is present, even if empty.
     */
    private function ensure_metadataExists() : void {
        // == GUARDS ==
        // not null
        if ( $this->metadata != null ) {
            return;
        }

        // == LOGIC ==
        // make not null
        $this->metadata = new engineering\schumann\common\DataContainer();

        // == SUCCESS ==
    }

}

?>