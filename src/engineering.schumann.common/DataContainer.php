<?php
// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace engineering\schumann\common;
// IMPORTS
use engineering\schumann\common\E_E_SE_RESULT_TYPE;


/**
 * Configurable container for data.
 * More powerful than array.
 */
final class DataContainer{
    /* ====================
     * PROPERTIES
     * ==================== */
    /**
     * holds the actual (unstrctured) data for dynamic properties
     */
    private array $_propertyStore = array();


    /**
     * determines how to handle property names
     * 
     * false = ignore cases of property names
     * true  = be case-sensitive with property names
     */
    public bool $isCaseSensitive = false;


    /**
     * array of (property name => new property name).
     * This can be used to have one property with different names, e.g.
     * for having "author" and "author-of-this-thing" both map to "author".
     * 
     * BEWARE: this is case-sensitive if isCaseSensitive is true
     */
    public array $propertyAliasMap = array();


    /**
     * array of (property name => default value).
     * This can be used to define default values for a property 
     * 
     * BEWARE: this is case-sensitive if isCaseSensitive is true
     */
    public array $propertyDefaultMap = array();


    /* ====================
     * GETTER & SETTER
     * ==================== */
    // isCaseSensitive
    /**
     * SETTER: set property names to case-sensitive
     */
    public function setCaseSensitive() : DataContainer {
        $this->isCaseSensitive = true;

        return $this;
    }


    /**
     * SETTER: set property names to not be case-sensitive
     */
    public function setCaseInsensitive() : DataContainer {
        $this->isCaseSensitive = false;

        return $this;
    }


    /* ====================
     * METHODS
     * ==================== */
    /**
     * returns the mapped property name, if the provided name is valid to begin with
     * 
     * @param  string    $propertyName name to map
     * 
     * @return string    mapped property name
     * 
     * @throws exception in case assertions fail
     */
    public function getPropertyName(
        string $propertyName
    ) : string {
        // == GUARDS ==
        $this->assert_validProperty( $propertyName );
        $this->ensure_propertyAliasMapIsArray();
        $this->ensure_propertyDefaultMapIsArray();

        // == LOGIC ==
        // try to find property in mapping
        foreach ( $this->_propertyAliasMap as $old_propertyName => $new_propertyName ){
            $isMatch = false;

            // case-sensitive
            if ( $this->isCaseSensitive && $propertyName == $old_propertyName ){
                $isMatch = true;
            }
            // case-insensitive
            else if ( $this->isCaseInsensitive && strtolower( $propertyName ) == strtolower( $old_propertyName ) ){
                $isMatch = true;
            }

            // GUARD: match?
            if ( !$isMatch ){
                continue;
            }

            // GUARD: mapped property needs to be valid
            // NOTE:  This also blocks people from bypassing protected properties by providing a map to them
            $this->assert_validProperty( $new_propertyName );

            // MATCH
            // == SUCCESS ==
            return $new_propertyName;
        }

        // == FALLBACK ==
        // no mapping found = use as is
        return $propertyName;
    }


    /**
     * returns ALL properties found by name
     * 
     * @param  string    $propertyName name of property to get
     * 
     * @return array     array( property name => property value) if any was found
     * 
     * @throws exception in case assertions fail
     */
    public function getPropertyList(
        string $propertyName
    ) : array {
        // == GUARDS ==
        $this->assert_validProperty( $propertyName );
        $this->assert_nonEmptyPropertyStore();

        // == RESULT ==
        $result = array();

        // == LOGIC ==
        // map property name
        $propertyName = $this->getPropertyName( $propertyName );

        // try to find property in data
        foreach ( $this->_propertyStore as $local_propertyName => $propertyValue ){
            $isMatch = false;

            // case-sensitive
            if ( $this->isCaseSensitive && $propertyName == $local_propertyName ){
                $isMatch = true;
            }
            // case-insensitive
            else if ( $this->isCaseInsensitive && strtolower( $propertyName ) == strtolower( $local_propertyName ) ){
                $isMatch = true;
            }

            // GUARD: match?
            if ( !$isMatch ){
                continue;
            }

            // MATCH
            // add to result
            // BEWARE: this overwrites existing entries
            $result[ $local_propertyName ] = $propertyValue;
        }

        // GUARD: nothing found
        if ( empty( $result ) ){
            // try default value
            $defaultValue = null;
            if ( $this->tryGetPropertyDefaultValue( $propertyName, $defaultValue ) ){
                $result[ $propertyName ] = $defaultValue;
            }
        }

        // == SUCCESS ==
        return $result;
    }


    /**
     * Checks if a property exists
     * 
     * @param  string $propertyName name of property to check
     * 
     * @return bool   true, if a property value was found
     */
    public function hasProperty(
        string $propertyName
    ) : bool {
        return $this->tryGetPropertyValue( $propertyName, null );
    }


    /**
     * try to return the FIRST property found by name
     * 
     * @param  string    $propertyName name of property to get
     * @param  object    property value, if any was found. if NULL is returned than that is the value! otherwise it would be unset / does not exist.
     */
    public function tryGetPropertyValue(
        string  $propertyName,
        object &$propertyValue
    ) : bool {
        try {
            // try getting a property value
            $propertyValue = getPropertyValue( $propertyName );
            // == SUCCESS ==
            return SE_CONSTANTS::SUCCESS;
        }
        catch ( exception $e ){
            // == FAIL ==
            $propertyValue = null;
            return SE_CONSTANTS::FAIL;
        }
    }


    /**
     * returns the FIRST property found by name
     * 
     * @param  string    $propertyName name of property to get
     * 
     * @return object    property value, if any was found. if NULL is returned than that is the value! otherwise it would be unset / does not exist.
     * 
     * @throws exception
     */
    public function getPropertyValue(
        string $propertyName
    ) : object {
        // get values for property
        $values = $this->getPropertyList( $propertyName );

        // == GUARDS ==
        // .. empty
        if ( empty( $values ) ){
            throw new Exception( "Property '${name}' does not exist and no default set" );
        }
        // .. to many values
        if ( count( $values ) > 1 ){
            throw new Exception( "Property '${name}' is ambigous: more than 1 value found! Check case-sensitivity" );
        }

        // == SUCCESS ==
        return $values[0];
    }


    /**
     * stores a value for a property
     * 
     * @param  string    $propertyName
     * @param  string    $propertyValue
     * 
     * @throws exception 
     */
    public function setPropertyValue(
        string $propertyName,
        object $propertyValue
    ) : void {
        // == GUARDS ==
        $this->ensure_PropertyStoreIsArray();

        // == LOGIC ==
        // map property name
        $property_Name = $this->getPropertyName( $propertyName );
        // store it
        $this->_propertyStore[ $propertyName ] = $propertyValue;
    }


    /**
     * Checks if a property has default value configured
     * 
     * @param  string $propertyName name of property to check
     * 
     * @return bool
     */
    public function hasPropertyDefaultValue(
        string $propertyName
    ) : bool {
        return $this->tryGetPropertyDefaultValue( $propertyName, null );
    }


    /**
     * try to return default value for property
     * 
     * @param  string    $propertyName name of property to get default value for
     * 
     * @return object    property default value, if any was found
     */
    public function tryGetPropertyDefaultValue(
        string  $propertyName,
        object &$propertyDefaultValue
    ) : bool {
        try {
            // try getting a property default value
            $propertyDefaultValue = getPropertyDefaultValue( $propertyName, true );
            // == SUCCESS ==
            return SE_CONSTANTS::SUCCESS;
        }
        catch ( exception $e ){
            // == FAIL ==
            $propertyDefaultValue = null;
            return SE_CONSTANTS::FAIL;
        }
    }


    /**
     * returns the default value of a property if one is defined
     * 
     * @param  string    $propertyName
     * @param  bool      $throwExceptionIfNotFound
     * @param  object    $defaultValue in case no default value was configured and no exception shall be thrown
     * 
     * @return object    default value of property
     * 
     * @throws exception if $throwExceptionIfNotFound is true and no default value was configured
     */
    public function getPropertyDefaultValue(
        string $propertyName,
        bool   $throwExceptionIfNotFound = true,
        object $defaultValue = null
    ) : object {
        // == GUARDS ==
        $this->assert_validProperty( $propertyName );
        $this->ensure_propertyDefaultMapIsArray();

        // == LOGIC ==
        // map property name
        $propertyName = $this->getPropertyName( $propertyName );

        // try to find property in defaults
        foreach ( $this->propertyDefaultMap as $local_propertyName => $propertyDefaultValue ){
            $isMatch = false;

            // case-sensitive
            if ( $this->isCaseSensitive && $propertyName == $local_propertyName ){
                $isMatch = true;
            }
            // case-insensitive
            else if ( $this->isCaseInsensitive && strtolower( $propertyName ) == strtolower( $local_propertyName ) ){
                $isMatch = true;
            }

            // GUARD: match?
            if ( !$isMatch ){
                continue;
            }

            // MATCH
            // == SUCCESS ==
            return $propertyDefaultValue;
        }

        // GUARD: should exception be thrown?
        if ( $throwExceptionIfNotFound ){
            throw new Exception( "Property ${propertyName} does not have a default value" );
        }

        // == FALLBACK ==
        return $defaultValue;
    }


    /**
     * picks all values for selected properties. If provided, then a default is set for missing properties
     * 
     * @param  array     $propertyNames              array( property name (=> default value or null if not set) ) to pick
     * @param  bool      $exceptionOnMissingProperty shall an exception be thrown if a property cannot be found?
     * 
     * @return array
     * 
     * @throws exception depending on $exceptionOnMissingProperty
     */
    public function pick(
        array $propertyNames,
        bool  $exceptionOnMissingProperty = false
    ) : array {
        // copy property names
        $result = $propertyNames;

        // update values, if possible
        foreach ( $propertyNames as $propertyName => $defaultValue ){
            // == GUARDS ==
            $this->ensure_validProperty( $propertyName );

            // == LOGIC ==
            // retrieve property value
            $propertyValue = null;
            $propertyExists = !$this->tryGetPropertyValue( $propertyName, $propertyValue );

            if ( !$propertyExists && $exceptionOnMissingProperty ){
                throw new Exception( "Property '${propertyName}' does not exist");
            }

            if ( !$propertyExists ){
                continue;
            }

            // update value
            $result[ $propertyName ] = $propertyValue;
        }

        // == SUCCESS ==
        return $result;
    }


    public function setDefault(
        string $propertyName,
        object $defaultValue
    ) : void {
        // == GUARDS ==
        $this->ensure_validProperty( $propertyName );

        // == LOGIC ==
// FIXME: todo
        // only set default if no value exists
    }


    /* ====================
     * MAGIC METHODS
     * ==================== */
    /**
     * magic getter
     * __get() is utilized for reading data from inaccessible (protected or private) or non-existing properties.
     * 
     * @see https://www.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
     * 
     * @param  string $propertyName name of property which is getting accessed
     * @return object
     * 
     * @throws exception if $propertyName isn't valid
     */
    public function __get(
        string $propertyName
    ) : object {
        // == GUARDS ==
        $this->assert_validProperty( $propertyName );

        // == LOGIC ==
        // return property value
        return getPropertyValue( $propertyName );
    }


    /**
     * maggic setter
     * __set() is run when writing data to inaccessible (protected or private) or non-existing properties.
     * 
     * @see https://www.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
     * 
     * @param  string    $propertyName name of property which is getting accessed
     * 
     * @throws exception if $propertyName isn't valid
     */
    public function __set(
        string $propertyName, 
        object $value
    ) : void {
        // == GUARDS ==
        $this->assert_validProperty( $propertyName );
        $thit->ensure_PropertyStoreIsArray();

        // == LOGIC ==
        // set property value
        $this->setPropertyValue( $propertyName, $value );
    }


    /**
     * maggic checker
     * __isset() is triggered by calling isset() or empty() on inaccessible (protected or private) or non-existing properties.
     * 
     * @see https://www.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
     * 
     * @param  string    $propertyName name of property which is getting checked
     * 
     * @return bool      true, if property is set = property exist in container, even though it is NULL!
     */
    public function __isset(
        string $propertyName
    ) : bool {
        // has property can be used here
        return $this->hasProperty( $propertyName );
    }


    /**
     * maggic setter
     * __unset() is invoked when unset() is used on inaccessible (protected or private) or non-existing properties.
     * 
     * @see https://www.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
     * 
     * @param  string    $propertyName name of property which is getting deleted
     */
    public function __unset(
        string $propertyName
    ) : void {
        try {
            // get properties, if any
            $properties = $this->getPropertyList( $propertyName );

            // remove them from data store
            foreach ( $properties as $propertyName => $propertyValue ){
                unset( $this->_propertyStore[ $propertyName ] );
            }
        }
        catch ( exception $e ){
            // ignore
        }
    }


    /* ====================
     * ASSERTS
     * ==================== */
    /**
     * ensures valid property name
     * 
     * @param  string    $propertyName name of property to access
     * 
     * @throws exception if provided name is invalid
     */
    private static function assert_validPropertyName(
        string $propertyName
    ) : void {
        // == GUARDS ==
        // not null
        if ( $propertyName == null ) {
            throw new Exception( "Property name is NULL" );
        }

        // non empty
        if ( empty($propertyName) ) {
            throw new Exception( "Property name is EMPTY" );
        }

        // == SUCCESS ==
    }


    /**
     * ensures no access to protected or private properties
     * 
     * @param  string    $propertyName name of property to access
     * 
     * @throws exception if provided name is invalid
     * @throws exception if property is inaccessible
     */
    private function assert_validProperty(
        string $propertyName
    ) : void {
        // == GUARDS ==
        // property name is valid
        self::assert_validPropertyName( $propertyName );
        
        // dont allow access to private or protected properties this way
        if ( property_exists( $this, $propertyName ) ){
            throw new Exception( "Property '${propertyName}' is inaccessible" );
        }

        // == SUCCESS ==
    }


    /**
     * ensures there is some data stored
     * 
     * @throws exception if data is empty
     */
    private function assert_nonEmptyPropertyStore() : void {
        // == GUARDS ==
        $this->ensure_PropertyStoreIsArray();

        // empty
        if ( empty( $this->_propertyStore ) ){
            throw new Exception( 'no data found' );
        }

        // == SUCCESS ==
    }


    /**
     * ensures that property store is an array
     */
    private function ensure_PropertyStoreIsArray() : void {
        // == GUARDS ==
        // is array
        if ( is_array( $this->_propertyStore ) ){
            return;
        }

        // == LOGIC ==
        // make it a valid array
        $this->_propertyStore = array();

        // == SUCCESS ==
    }


    /**
     * ensures that property alias map is an array
     */
    private function ensure_propertyAliasMapIsArray() : void {
        // == GUARDS ==
        // is array
        if ( is_array( $this->propertyAliasMap ) ){
            return;
        }

        // == LOGIC ==
        // make it a valid array
        $this->propertyAliasMap = array();

        // == SUCCESS ==
    }


    /**
     * ensures that property default map is an array
     */
    private function ensure_propertyDefaultMapIsArray() : void {
        // == GUARDS ==
        // is array
        if ( is_array( $this->propertyDefaultMap ) ){
            return;
        }

        // == LOGIC ==
        // make it a valid array
        $this->propertyDefaultMap = array();

        // == SUCCESS ==
    }
}

?>