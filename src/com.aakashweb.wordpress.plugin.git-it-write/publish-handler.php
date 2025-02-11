<?php
// FIXME: don't code for GitHub only. Other sources of webhook might be interessting as well
// FIXME: should a method return a HTTP specific WP_Error or an exception?

// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace com\aakashweb\wordpress\plugin\git_it_write;
// IMPORTS
use com\aakashweb\git\GitRepository;
use com\aakashweb\wordpress\plugin\git_it_write\GIW_Publisher;
use com\aakashweb\wordpress\plugin\git_it_write\GIW_Utils;
use com\aakashweb\wordpress\webhook\HttpResult;
use engineering\schumann\http\common\HTTP_STATUS_CODE;


if ( ! defined( 'ABSPATH' ) ) exit;

final class GIW_PublishHandler{
    /* ====================
     * PROPERTIES
     * ==================== */
    /**
     * array(
     *   username => array(
     *     repository name => array(
     *       branch => GitRepository
     *     )
     *   )
     * )
     */
    private static array $_RepositoryObjCache = array();


    /* ====================
     * METHODS
     * ==================== */
    /**
     * ???
     * 
     * @param   GitRepository   $gitRepository
     * @param   string          $giwRepositoryConfigId
     * @param   array           $giwRepositoryConfig
     * 
     * @return
     */
    public static function publishRepository( 
        GitRepository   $gitRepository,
        string          $giwRepositoryConfigId,
        array           $giwRepositoryConfig
    ) : object {
        // 
        $username           = $gitRepository->user;
        $repositoryName     = $gitRepository->repo;
        $branch             = $gitRepository->branch;

        // log
        GIW_Utils::log( "[repo '{$gitRepository}' ] ********** START publishing posts **********" );

        // Cache the repository class object
        if (
            !array_key_exists( $username,        self::$_RepositoryObjCache ) ||
            !array_key_exists( $repositoryName,  self::$_RepositoryObjCache[ $username ] ) ||
            !array_key_exists( $branch,          self::$_RepositoryObjCache[ $username ][ $repositoryName ] ) 
        ){
            GIW_Utils::log( "[repo '{$gitRepository}' ] CACHING repository object" );

            // store in cache
            self::$_RepositoryObjCache[ $username ][ $repositoryName ][ $branch ] = $repository;
        }

        // publish
        $publisher = new GIW_Publisher( $gitRepository, $giwRepositoryConfig );
        $result = $publisher->publish();

        // update repository list
        // TODO: refactor this
        $giwRepositoryConfigList = Git_It_Write::all_repositories();
        $giwRepositoryConfigList[ $giwRepositoryConfigId ][ 'last_publish' ] = time();
        update_option( 'giw_repositories', $giwRepositoryConfigList );

        // log
        GIW_Utils::log( "[repo '{$gitRepository}' ] ********** END successful **********" );

        // == SUCCESS ==
        return $result;
    }


    /**
     * ???
     * 
     * @param string    $giwRepositoryConfigId
     * @param array     $giwRepositoryConfig
     * 
     * @return
     */
    public static function publishRepositoryByConfig( 
        string  $giwRepositoryConfigId,
        array   $giwRepositoryConfig
    ) : object {
        // 
        $username           = $giwRepositoryConfig[ 'username' ];
        $repositoryName     = $giwRepositoryConfig[ 'repository' ];
        $branch             = $giwRepositoryConfig[ 'branch' ];
        $gitRepository   = false;

        // log
        GIW_Utils::log( "[repo '{$username}/{$repositoryName}'#'{$branch}' ] ********** START publishing posts by config **********" );

        // Cache the repository class object
        if (
            array_key_exists( $username,        self::$_RepositoryObjCache ) && 
            array_key_exists( $repositoryName,  self::$_RepositoryObjCache[ $username ] ) && 
            array_key_exists( $branch,          self::$_RepositoryObjCache[ $username ][ $repositoryName ] ) 
        ){
            GIW_Utils::log( "[repo '{$username}/{$repositoryName}'#'{$branch}' ] FOUND cached repository object" );

            // get from cache
            $gitRepository = self::$_RepositoryObjCache[ $username ][ $repositoryName ][ $branch ];
        } else {
            GIW_Utils::log( "[repo '{$username}/{$repositoryName}'#'{$branch}' ] CREATING new repository object" );

            // create new
            $gitRepository = new GitRepository( $username, $repositoryName, $branch );
            // store in cache
            self::$_RepositoryObjCache[ $username ][ $repositoryName ][ $branch ] = $repository;
        }

        // publish
        $result = self::publishRepository( $gitRepository, $giwRepositoryConfigId, $giwRepositoryConfig );

        // log
        GIW_Utils::log( "[repo '{$username}/{$repositoryName}'#'{$branch}' ] ********** END (config) successful **********" );

        // == SUCCESS ==
        return $result;
    }


    /**
     * ???
     * 
     * @param string $giwRepositoryId
     * 
     * @return
     */
    public static function publishRepositoryById( 
        string $giwRepositoryConfigId
    ) : object {
        // == GUARDS ==
        // empty ID
        if ( $giwRepositoryConfigId== 0 || empty( $giwRepositoryConfigId) ){
            throw new Exception( 'repository ID is null, empty or not valid' );
        }

        // == LOGIC ==
        GIW_Utils::log( "[repo '${giwRepositoryId}'] ********** START publishing posts by id **********" );

        // get all configured repositories in GIW plugin
        $giwRepositoryconfigList = Git_It_Write::all_repositories();

        // guard: repository with id needs to exist
        if( !isset( $giwRepositoryconfigList[ $giwRepositoryConfigId] ) ){
            GIW_Utils::log( "[repo '${giwRepositoryId}'] ********** ERROR: no matching configuration found ********** " );
            // == FAIL ==
            return HttpResult::error( HTTP_STATUS_CODE::STATUS_422_Unprocessable_Entity, 'repository_unknown', "repository is not configured on server" );
        }

        // == MATCH ==
        $giwRepositoryConfig = $giwRepositoryconfigList[ $giwRepositoryConfigId ];

        // log
        GIW_Utils::log( "[repo '${giwRepositoryId}'] FOUND matching configuration" );

        // publish
        $result = self::publishRepositoryByConfig( $giwRepositoryConfigId, $giwRepositoryConfig );

        // log
        GIW_Utils::log( "[repo '${giwRepositoryId}'] ********** END successful **********" );

        // == SUCCESS ==
        return $result;
    }


    /**
     * ???
     * 
     * @param  string $repositoryFullName
     * 
     * @return ???
     */
    public static function publishRepositoryByFullName(
        string $repositoryFullName
    ) : object {
        // == GUARDS ==
        // empty name
        if ( $repositoryFullName == 0 || empty( $repositoryFullName ) ){
            throw new Exception( 'repository name is null, empty or not valid' );
        }

        // == LOGIC ==
        GIW_Utils::log( "[repo '${repositoryFullName}'] ********** START publishing repository by full name of repository **********" );

        $remoteRepository = self::getRepositoryName( $repositoryFullName );
        if ( is_wp_error( $remoteRepository ) ) {
            GIW_Utils::log( "[repo '${repositoryFullName}'] ********** ERROR: $remoteRepository->get_error_message() **********" );
            // == FAIL ==
            return $remoteRepository;
        }

        $result = array();

        // get all configured repositories in GIW plugin
        $giwRepositoryConfigList = Git_It_Write::all_repositories();

        // FIXME: this processes all branches of one repository, if multiple branches are configured. The trigger comes from one specific branch though, does it?
        // iterate over list of repositories and try to find all matches
        $foundMatchingRepository = false;
        foreach( $giwRepositoryConfigList as $giwRepositoryConfigId => $giwRepositoryConfig ){
            // skip repository with empty ID
            if ( $giwRepositoryConfigId == 0 || empty( $giwRepositoryConfigId ) ){
                continue;
            }

            // guard: username has to match
            if ( $remoteRepository[ 'username' ] != $giwRepositoryConfig[ 'username' ] ){
                continue;
            }
            
            // guard: repository has to match
            if ( $remoteRepository[ 'repository' ] != $giwRepositoryConfig[ 'repository' ] ){
                continue;
            }

            // == MATCH ==
            GIW_Utils::log( "[repo '${repositoryFullName}'] FOUND matching configuration with ID '${giwRepositoryConfigId}'" );

            // publish
            $partialResult = self::publishRepositoryByConfig( $giwRepositoryConfigId, $giwRepositoryConfig );

            // add to result
            if ( !is_wp_error( $partialResult ) ){
                array_push( $result, $partialResult );
            }

            $foundMatchingRepository = true;
        }


        // guard: no match found
        if ( !$foundMatchingRepository ){
            $result = HttpResult::error( HTTP_STATUS_CODE::STATUS_422_Unprocessable_Entity, 'repository_unknown', "repository '${repositoryFullName}' is not configured on server" );
        }


        if ( is_wp_error( $result ) ){
            GIW_Utils::log( "[repo '${repositoryFullName}'] ********** ERROR: $result->get_error_message() **********" );
            // == FAIL ==
            return $result;
        }

        // == SUCCESS ==
        GIW_Utils::log( "[repo '${repositoryFullName}'] ********** END successful **********" );
        return $result;
    }


    /**
     * retrieves an array( username, repository name ) from a repository full name
     * 
     * @param  string $repositoryFullName full name of repository, e.g. 'username/repository'.
     * 
     * @return array|WP_Error
     */
    private static function getRepositoryName(
        string $repositoryFullName
    ) : array {
        // split repository name
        $repositoryName = explode( '/', $repositoryFullName );

        // guard: only two parts are allowed, otherwise it's something different
        if( count( $repositoryName ) != 2 ){
            // == FAIL ==
            return HttpResult::error( HTTP_STATUS_CODE::STATUS_400_Bad_Request, 'repository_name_invalid', "repository name '${repositoryFullName}' does not follow syntax '<username>/<repository>'" );
        }

        // == SUCCESS ==
        return array(
            'username'   => $repositoryName[0],
            'repository' => $repositoryName[1]
        );
    }
}

?>