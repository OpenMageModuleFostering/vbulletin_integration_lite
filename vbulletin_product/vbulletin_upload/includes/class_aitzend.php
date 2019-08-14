<?php

class Ait_Zend_Exception extends Exception {}
class Ait_Zend_Http_Exception extends Ait_Zend_Exception {}
class Ait_Zend_Json_Exception extends Ait_Zend_Exception {}

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Class for encoding to and decoding from JSON.
 *
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Ait_Zend_Json
{
    /**
     * How objects should be encoded -- arrays or as StdClass. TYPE_ARRAY is 1
     * so that it is a boolean true value, allowing it to be used with
     * ext/json's functions.
     */
    const TYPE_ARRAY  = 1;
    const TYPE_OBJECT = 0;

     /**
      * To check the allowed nesting depth of the XML tree during xml2json conversion.
      *
      * @var int
      */
    public static $maxRecursionDepthAllowed=25;

    /**
     * @var bool
     */
    public static $useBuiltinEncoderDecoder = false;

    /**
     * Decodes the given $encodedValue string which is
     * encoded in the JSON format
     *
     * Uses ext/json's json_decode if available.
     *
     * @param string $encodedValue Encoded in JSON format
     * @param int $objectDecodeType Optional; flag indicating how to decode
     * objects. See {@link Zend_Json_Decoder::decode()} for details.
     * @return mixed
     */
    public static function decode($encodedValue, $objectDecodeType = Ait_Zend_Json::TYPE_ARRAY)
    {
        if (function_exists('json_decode') && self::$useBuiltinEncoderDecoder !== true) {
            return json_decode($encodedValue, $objectDecodeType);
        }

        return Ait_Zend_Json_Decoder::decode($encodedValue, $objectDecodeType);
    }


    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * Encodes using ext/json's json_encode() if available.
     *
     * NOTE: Object should not contain cycles; the JSON format
     * does not allow object reference.
     *
     * NOTE: Only public variables will be encoded
     *
     * @param mixed $valueToEncode
     * @param boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param array $options Additional options used during encoding
     * @return string JSON encoded object
     */
    public static function encode($valueToEncode, $cycleCheck = false, $options = array())
    {
        if (is_object($valueToEncode) && method_exists($valueToEncode, 'toJson')) {
            return $valueToEncode->toJson();
        }

        if (function_exists('json_encode') && self::$useBuiltinEncoderDecoder !== true) {
            return json_encode($valueToEncode);
        }

        return Ait_Zend_Json_Encoder::encode($valueToEncode, $cycleCheck, $options);
    }

    /**
     * fromXml - Converts XML to JSON
     *
     * Converts a XML formatted string into a JSON formatted string.
     * The value returned will be a string in JSON format.
     *
     * The caller of this function needs to provide only the first parameter,
     * which is an XML formatted String. The second parameter is optional, which
     * lets the user to select if the XML attributes in the input XML string
     * should be included or ignored in xml2json conversion.
     *
     * This function converts the XML formatted string into a PHP array by
     * calling a recursive (protected static) function in this class. Then, it
     * converts that PHP array into JSON by calling the "encode" static funcion.
     *
     * Throws a Zend_Json_Exception if the input not a XML formatted string.
     *
     * @static
     * @access public
     * @param string $xmlStringContents XML String to be converted
     * @param boolean $ignoreXmlAttributes Include or exclude XML attributes in
     * the xml2json conversion process.
     * @return mixed - JSON formatted string on success
     * @throws Ait_Zend_Json_Exception
     */
    public static function fromXml ($xmlStringContents, $ignoreXmlAttributes=true) {
        // Load the XML formatted string into a Simple XML Element object.
        $simpleXmlElementObject = simplexml_load_string($xmlStringContents);

        // If it is not a valid XML content, throw an exception.
        if ($simpleXmlElementObject == null) {
            throw new Ait_Zend_Json_Exception('Function fromXml was called with an invalid XML formatted string.');
        } // End of if ($simpleXmlElementObject == null)

        $resultArray = null;

        // Call the recursive function to convert the XML into a PHP array.
        $resultArray = self::_processXml($simpleXmlElementObject, $ignoreXmlAttributes);

        // Convert the PHP array to JSON using Zend_Json encode method.
        // It is just that simple.
        $jsonStringOutput = self::encode($resultArray);
        return($jsonStringOutput);
    } // End of function fromXml.

    /**
     * _processXml - Contains the logic for xml2json
     *
     * The logic in this function is a recursive one.
     *
     * The main caller of this function (i.e. fromXml) needs to provide
     * only the first two parameters i.e. the SimpleXMLElement object and
     * the flag for ignoring or not ignoring XML attributes. The third parameter
     * will be used internally within this function during the recursive calls.
     *
     * This function converts the SimpleXMLElement object into a PHP array by
     * calling a recursive (protected static) function in this class. Once all
     * the XML elements are stored in the PHP array, it is returned to the caller.
     *
     * Throws a Zend_Json_Exception if the XML tree is deeper than the allowed limit.
     *
     * @static
     * @access protected
     * @param SimpleXMLElement $simpleXmlElementObject XML element to be converted
     * @param boolean $ignoreXmlAttributes Include or exclude XML attributes in
     * the xml2json conversion process.
     * @param int $recursionDepth Current recursion depth of this function
     * @return mixed - On success, a PHP associative array of traversed XML elements
     * @throws Ait_Zend_Json_Exception
     */
    protected static function _processXml ($simpleXmlElementObject, $ignoreXmlAttributes, $recursionDepth=0) {
        // Keep an eye on how deeply we are involved in recursion.
        if ($recursionDepth > self::$maxRecursionDepthAllowed) {
            // XML tree is too deep. Exit now by throwing an exception.
            throw new Ait_Zend_Json_Exception(
                "Function _processXml exceeded the allowed recursion depth of " .
                self::$maxRecursionDepthAllowed);
        } // End of if ($recursionDepth > self::$maxRecursionDepthAllowed)

        if ($recursionDepth == 0) {
            // Store the original SimpleXmlElementObject sent by the caller.
            // We will need it at the very end when we return from here for good.
            $callerProvidedSimpleXmlElementObject = $simpleXmlElementObject;
        } // End of if ($recursionDepth == 0)

        if ($simpleXmlElementObject instanceof SimpleXMLElement) {
            // Get a copy of the simpleXmlElementObject
            $copyOfSimpleXmlElementObject = $simpleXmlElementObject;
            // Get the object variables in the SimpleXmlElement object for us to iterate.
            $simpleXmlElementObject = get_object_vars($simpleXmlElementObject);
        } // End of if (get_class($simpleXmlElementObject) == "SimpleXMLElement")

        // It needs to be an array of object variables.
        if (is_array($simpleXmlElementObject)) {
            // Initialize a result array.
            $resultArray = array();
            // Is the input array size 0? Then, we reached the rare CDATA text if any.
            if (count($simpleXmlElementObject) <= 0) {
                // Let us return the lonely CDATA. It could even be
                // an empty element or just filled with whitespaces.
                return (trim(strval($copyOfSimpleXmlElementObject)));
            } // End of if (count($simpleXmlElementObject) <= 0)

            // Let us walk through the child elements now.
            foreach($simpleXmlElementObject as $key=>$value) {
                // Check if we need to ignore the XML attributes.
                // If yes, you can skip processing the XML attributes.
                // Otherwise, add the XML attributes to the result array.
                if(($ignoreXmlAttributes == true) && (is_string($key)) && ($key == "@attributes")) {
                    continue;
                } // End of if(($ignoreXmlAttributes == true) && ($key == "@attributes"))

                // Let us recursively process the current XML element we just visited.
                // Increase the recursion depth by one.
                $recursionDepth++;
                $resultArray[$key] = self::_processXml ($value, $ignoreXmlAttributes, $recursionDepth);

                // Decrease the recursion depth by one.
                $recursionDepth--;
            } // End of foreach($simpleXmlElementObject as $key=>$value) {

            if ($recursionDepth == 0) {
                // That is it. We are heading to the exit now.
                // Set the XML root element name as the root [top-level] key of
                // the associative array that we are going to return to the original
                // caller of this recursive function.
                $tempArray = $resultArray;
                $resultArray = array();
                $resultArray[$callerProvidedSimpleXmlElementObject->getName()] = $tempArray;
            } // End of if ($recursionDepth == 0)

            return($resultArray);
        } else {
            // We are now looking at either the XML attribute text or
            // the text between the XML tags.
            return (trim(strval($simpleXmlElementObject)));
        } // End of if (is_array($simpleXmlElementObject))
    } // End of function _processXml.
}

/**
 * Encode PHP constructs to JSON
 *
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Ait_Zend_Json_Encoder
{
    /**
     * Whether or not to check for possible cycling
     *
     * @var boolean
     */
    protected $_cycleCheck;

    /**
     * Additional options used during encoding
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Array of visited objects; used to prevent cycling.
     *
     * @var array
     */
    protected $_visited = array();

    /**
     * Constructor
     *
     * @param boolean $cycleCheck Whether or not to check for recursion when encoding
     * @param array $options Additional options used during encoding
     * @return void
     */
    protected function __construct($cycleCheck = false, $options = array())
    {
        $this->_cycleCheck = $cycleCheck;
        $this->_options = $options;
    }

    /**
     * Use the JSON encoding scheme for the value specified
     *
     * @param mixed $value The value to be encoded
     * @param boolean $cycleCheck Whether or not to check for possible object recursion when encoding
     * @param array $options Additional options used during encoding
     * @return string  The encoded value
     */
    public static function encode($value, $cycleCheck = false, $options = array())
    {
        $encoder = new self(($cycleCheck) ? true : false, $options);

        return $encoder->_encodeValue($value);
    }

    /**
     * Recursive driver which determines the type of value to be encoded
     * and then dispatches to the appropriate method. $values are either
     *    - objects (returns from {@link _encodeObject()})
     *    - arrays (returns from {@link _encodeArray()})
     *    - basic datums (e.g. numbers or strings) (returns from {@link _encodeDatum()})
     *
     * @param $value mixed The value to be encoded
     * @return string Encoded value
     */
    protected function _encodeValue(&$value)
    {
        if (is_object($value)) {
            return $this->_encodeObject($value);
        } else if (is_array($value)) {
            return $this->_encodeArray($value);
        }

        return $this->_encodeDatum($value);
    }

    /**
     * Encode an object to JSON by encoding each of the public properties
     *
     * A special property is added to the JSON object called '__className'
     * that contains the name of the class of $value. This is used to decode
     * the object on the client into a specific class.
     *
     * @param $value object
     * @return string
     * @throws Ait_Zend_Json_Exception If recursive checks are enabled and the object has been serialized previously
     */
    protected function _encodeObject(&$value)
    {
        if ($this->_cycleCheck) {
            if ($this->_wasVisited($value)) {

                if (isset($this->_options['silenceCyclicalExceptions'])
                    && $this->_options['silenceCyclicalExceptions']===true) {

                    return '"* RECURSION (' . get_class($value) . ') *"';

                } else {
                    throw new Ait_Zend_Json_Exception(
                        'Cycles not supported in JSON encoding, cycle introduced by '
                        . 'class "' . get_class($value) . '"'
                    );
                }
            }

            $this->_visited[] = $value;
        }

        $props = '';

        if ($value instanceof Iterator) {
            $propCollection = $value;
        } else {
            $propCollection = get_object_vars($value);
        }

        foreach ($propCollection as $name => $propValue) {
            if (isset($propValue)) {
                $props .= ','
                        . $this->_encodeValue($name)
                        . ':'
                        . $this->_encodeValue($propValue);
            }
        }

        return '{"__className":"' . get_class($value) . '"'
                . $props . '}';
    }

    /**
     * Determine if an object has been serialized already
     *
     * @param mixed $value
     * @return boolean
     */
    protected function _wasVisited(&$value)
    {
        if (in_array($value, $this->_visited, true)) {
            return true;
        }

        return false;
    }

    /**
     * JSON encode an array value
     *
     * Recursively encodes each value of an array and returns a JSON encoded
     * array string.
     *
     * Arrays are defined as integer-indexed arrays starting at index 0, where
     * the last index is (count($array) -1); any deviation from that is
     * considered an associative array, and will be encoded as such.
     *
     * @param $array array
     * @return string
     */
    protected function _encodeArray(&$array)
    {
        $tmpArray = array();

        // Check for associative array
        if (!empty($array) && (array_keys($array) !== range(0, count($array) - 1))) {
            // Associative array
            $result = '{';
            foreach ($array as $key => $value) {
                $key = (string) $key;
                $tmpArray[] = $this->_encodeString($key)
                            . ':'
                            . $this->_encodeValue($value);
            }
            $result .= implode(',', $tmpArray);
            $result .= '}';
        } else {
            // Indexed array
            $result = '[';
            $length = count($array);
            for ($i = 0; $i < $length; $i++) {
                $tmpArray[] = $this->_encodeValue($array[$i]);
            }
            $result .= implode(',', $tmpArray);
            $result .= ']';
        }

        return $result;
    }

    /**
     * JSON encode a basic data type (string, number, boolean, null)
     *
     * If value type is not a string, number, boolean, or null, the string
     * 'null' is returned.
     *
     * @param $value mixed
     * @return string
     */
    protected function _encodeDatum(&$value)
    {
        $result = 'null';

        if (is_int($value) || is_float($value)) {
            $result = (string) $value;
        } elseif (is_string($value)) {
            $result = $this->_encodeString($value);
        } elseif (is_bool($value)) {
            $result = $value ? 'true' : 'false';
        }

        return $result;
    }

    /**
     * JSON encode a string value by escaping characters as necessary
     *
     * @param $value string
     * @return string
     */
    protected function _encodeString(&$string)
    {
        // Escape these characters with a backslash:
        // " \ / \n \r \t \b \f
        $search  = array('\\', "\n", "\t", "\r", "\b", "\f", '"');
        $replace = array('\\\\', '\\n', '\\t', '\\r', '\\b', '\\f', '\"');
        $string  = str_replace($search, $replace, $string);

        // Escape certain ASCII characters:
        // 0x08 => \b
        // 0x0c => \f
        $string = str_replace(array(chr(0x08), chr(0x0C)), array('\b', '\f'), $string);

        return '"' . $string . '"';
    }

    /**
     * Encode the constants associated with the ReflectionClass
     * parameter. The encoding format is based on the class2 format
     *
     * @param $cls ReflectionClass
     * @return string Encoded constant block in class2 format
     */
    private static function _encodeConstants(ReflectionClass $cls)
    {
        $result    = "constants : {";
        $constants = $cls->getConstants();

        $tmpArray = array();
        if (!empty($constants)) {
            foreach ($constants as $key => $value) {
                $tmpArray[] = "$key: " . self::encode($value);
            }

            $result .= implode(', ', $tmpArray);
        }

        return $result . "}";
    }

    /**
     * Encode the public methods of the ReflectionClass in the
     * class2 format
     *
     * @param $cls ReflectionClass
     * @return string Encoded method fragment
     *
     */
    private static function _encodeMethods(ReflectionClass $cls)
    {
        $methods = $cls->getMethods();
        $result = 'methods:{';

        $started = false;
        foreach ($methods as $method) {
            if (! $method->isPublic() || !$method->isUserDefined()) {
                continue;
            }

            if ($started) {
                $result .= ',';
            }
            $started = true;

            $result .= '' . $method->getName(). ':function(';

            if ('__construct' != $method->getName()) {
                $parameters  = $method->getParameters();
                $paramCount  = count($parameters);
                $argsStarted = false;

                $argNames = "var argNames=[";
                foreach ($parameters as $param) {
                    if ($argsStarted) {
                        $result .= ',';
                    }

                    $result .= $param->getName();

                    if ($argsStarted) {
                        $argNames .= ',';
                    }

                    $argNames .= '"' . $param->getName() . '"';

                    $argsStarted = true;
                }
                $argNames .= "];";

                $result .= "){"
                         . $argNames
                         . 'var result = ZAjaxEngine.invokeRemoteMethod('
                         . "this, '" . $method->getName()
                         . "',argNames,arguments);"
                         . 'return(result);}';
            } else {
                $result .= "){}";
            }
        }

        return $result . "}";
    }

    /**
     * Encode the public properties of the ReflectionClass in the class2
     * format.
     *
     * @param $cls ReflectionClass
     * @return string Encode properties list
     *
     */
    private static function _encodeVariables(ReflectionClass $cls)
    {
        $properties = $cls->getProperties();
        $propValues = get_class_vars($cls->getName());
        $result = "variables:{";
        $cnt = 0;

        $tmpArray = array();
        foreach ($properties as $prop) {
            if (! $prop->isPublic()) {
                continue;
            }

            $tmpArray[] = $prop->getName()
                        . ':'
                        . self::encode($propValues[$prop->getName()]);
        }
        $result .= implode(',', $tmpArray);

        return $result . "}";
    }

    /**
     * Encodes the given $className into the class2 model of encoding PHP
     * classes into JavaScript class2 classes.
     * NOTE: Currently only public methods and variables are proxied onto
     * the client machine
     *
     * @param $className string The name of the class, the class must be
     * instantiable using a null constructor
     * @param $package string Optional package name appended to JavaScript
     * proxy class name
     * @return string The class2 (JavaScript) encoding of the class
     * @throws Ait_Zend_Json_Exception
     */
    public static function encodeClass($className, $package = '')
    {
        $cls = new ReflectionClass($className);
        if (! $cls->isInstantiable()) {
            throw new Ait_Zend_Json_Exception("$className must be instantiable");
        }

        return "Class.create('$package$className',{"
                . self::_encodeConstants($cls)    .","
                . self::_encodeMethods($cls)      .","
                . self::_encodeVariables($cls)    .'});';
    }

    /**
     * Encode several classes at once
     *
     * Returns JSON encoded classes, using {@link encodeClass()}.
     *
     * @param array $classNames
     * @param string $package
     * @return string
     */
    public static function encodeClasses(array $classNames, $package = '')
    {
        $result = '';
        foreach ($classNames as $className) {
            $result .= self::encodeClass($className, $package);
        }

        return $result;
    }
}

/**
 * Decode JSON encoded string to PHP variable constructs
 *
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Ait_Zend_Json_Decoder
{
    /**
     * Parse tokens used to decode the JSON object. These are not
     * for public consumption, they are just used internally to the
     * class.
     */
    const EOF         = 0;
    const DATUM        = 1;
    const LBRACE    = 2;
    const LBRACKET    = 3;
    const RBRACE     = 4;
    const RBRACKET    = 5;
    const COMMA       = 6;
    const COLON        = 7;

    /**
     * Use to maintain a "pointer" to the source being decoded
     *
     * @var string
     */
    protected $_source;

    /**
     * Caches the source length
     *
     * @var int
     */
    protected $_sourceLength;

    /**
     * The offset within the souce being decoded
     *
     * @var int
     *
     */
    protected $_offset;

    /**
     * The current token being considered in the parser cycle
     *
     * @var int
     */
    protected $_token;

    /**
     * Flag indicating how objects should be decoded
     *
     * @var int
     * @access protected
     */
    protected $_decodeType;

    /**
     * Constructor
     *
     * @param string $source String source to decode
     * @param int $decodeType How objects should be decoded -- see
     * {@link Zend_Json::TYPE_ARRAY} and {@link Zend_Json::TYPE_OBJECT} for
     * valid values
     * @return void
     */
    protected function __construct($source, $decodeType)
    {
        // Set defaults
        $this->_source       = $source;
        $this->_sourceLength = strlen($source);
        $this->_token        = self::EOF;
        $this->_offset       = 0;

        // Normalize and set $decodeType
        if (!in_array($decodeType, array(Ait_Zend_Json::TYPE_ARRAY, Ait_Zend_Json::TYPE_OBJECT)))
        {
            $decodeType = Ait_Zend_Json::TYPE_ARRAY;
        }
        $this->_decodeType   = $decodeType;

        // Set pointer at first token
        $this->_getNextToken();
    }

    /**
     * Decode a JSON source string
     *
     * Decodes a JSON encoded string. The value returned will be one of the
     * following:
     *        - integer
     *        - float
     *        - boolean
     *        - null
     *      - StdClass
     *      - array
     *         - array of one or more of the above types
     *
     * By default, decoded objects will be returned as associative arrays; to
     * return a StdClass object instead, pass {@link Zend_Json::TYPE_OBJECT} to
     * the $objectDecodeType parameter.
     *
     * Throws a Zend_Json_Exception if the source string is null.
     *
     * @static
     * @access public
     * @param string $source String to be decoded
     * @param int $objectDecodeType How objects should be decoded; should be
     * either or {@link Zend_Json::TYPE_ARRAY} or
     * {@link Zend_Json::TYPE_OBJECT}; defaults to TYPE_ARRAY
     * @return mixed
     * @throws Ait_Zend_Json_Exception
     */
    public static function decode($source = null, $objectDecodeType = Ait_Zend_Json::TYPE_ARRAY)
    {
        if (null === $source) {
            throw new Ait_Zend_Json_Exception('Must specify JSON encoded source for decoding');
        } elseif (!is_string($source)) {
            throw new Ait_Zend_Json_Exception('Can only decode JSON encoded strings');
        }

        $decoder = new self($source, $objectDecodeType);

        return $decoder->_decodeValue();
    }

    /**
     * Recursive driving rountine for supported toplevel tops
     *
     * @return mixed
     */
    protected function _decodeValue()
    {
        switch ($this->_token) {
            case self::DATUM:
                $result  = $this->_tokenValue;
                $this->_getNextToken();
                return($result);
                break;
            case self::LBRACE:
                return($this->_decodeObject());
                break;
            case self::LBRACKET:
                return($this->_decodeArray());
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * Decodes an object of the form:
     *  { "attribute: value, "attribute2" : value,...}
     *
     * If Zend_Json_Encoder was used to encode the original object then
     * a special attribute called __className which specifies a class
     * name that should wrap the data contained within the encoded source.
     *
     * Decodes to either an array or StdClass object, based on the value of
     * {@link $_decodeType}. If invalid $_decodeType present, returns as an
     * array.
     *
     * @return array|StdClass
     */
    protected function _decodeObject()
    {
        $members = array();
        $tok = $this->_getNextToken();

        while ($tok && $tok != self::RBRACE) {
            if ($tok != self::DATUM || ! is_string($this->_tokenValue)) {
                throw new Ait_Zend_Json_Exception('Missing key in object encoding: ' . $this->_source);
            }

            $key = $this->_tokenValue;
            $tok = $this->_getNextToken();

            if ($tok != self::COLON) {
                throw new Ait_Zend_Json_Exception('Missing ":" in object encoding: ' . $this->_source);
            }

            $tok = $this->_getNextToken();
            $members[$key] = $this->_decodeValue();
            $tok = $this->_token;

            if ($tok == self::RBRACE) {
                break;
            }

            if ($tok != self::COMMA) {
                throw new Ait_Zend_Json_Exception('Missing "," in object encoding: ' . $this->_source);
            }

            $tok = $this->_getNextToken();
        }

        switch ($this->_decodeType) {
            case Ait_Zend_Json::TYPE_OBJECT:
                // Create new StdClass and populate with $members
                $result = new StdClass();
                foreach ($members as $key => $value) {
                    $result->$key = $value;
                }
                break;
            case Ait_Zend_Json::TYPE_ARRAY:
            default:
                $result = $members;
                break;
        }

        $this->_getNextToken();
        return $result;
    }

    /**
     * Decodes a JSON array format:
     *    [element, element2,...,elementN]
     *
     * @return array
     */
    protected function _decodeArray()
    {
        $result = array();
        $starttok = $tok = $this->_getNextToken(); // Move past the '['
        $index  = 0;

        while ($tok && $tok != self::RBRACKET) {
            $result[$index++] = $this->_decodeValue();

            $tok = $this->_token;

            if ($tok == self::RBRACKET || !$tok) {
                break;
            }

            if ($tok != self::COMMA) {
                throw new Ait_Zend_Json_Exception('Missing "," in array encoding: ' . $this->_source);
            }

            $tok = $this->_getNextToken();
        }

        $this->_getNextToken();
        return($result);
    }

    /**
     * Removes whitepsace characters from the source input
     */
    protected function _eatWhitespace()
    {
        if (preg_match(
                '/([\t\b\f\n\r ])*/s',
                $this->_source,
                $matches,
                PREG_OFFSET_CAPTURE,
                $this->_offset)
            && $matches[0][1] == $this->_offset)
        {
            $this->_offset += strlen($matches[0][0]);
        }
    }

    /**
     * Retrieves the next token from the source stream
     *
     * @return int Token constant value specified in class definition
     */
    protected function _getNextToken()
    {
        $this->_token      = self::EOF;
        $this->_tokenValue = null;
        $this->_eatWhitespace();

        if ($this->_offset >= $this->_sourceLength) {
            return(self::EOF);
        }

        $str        = $this->_source;
        $str_length = $this->_sourceLength;
        $i          = $this->_offset;
        $start      = $i;

        switch ($str{$i}) {
            case '{':
               $this->_token = self::LBRACE;
               break;
            case '}':
                $this->_token = self::RBRACE;
                break;
            case '[':
                $this->_token = self::LBRACKET;
                break;
            case ']':
                $this->_token = self::RBRACKET;
                break;
            case ',':
                $this->_token = self::COMMA;
                break;
            case ':':
                $this->_token = self::COLON;
                break;
            case  '"':
                $result = '';
                do {
                    $i++;
                    if ($i >= $str_length) {
                        break;
                    }

                    $chr = $str{$i};
                    if ($chr == '\\') {
                        $i++;
                        if ($i >= $str_length) {
                            break;
                        }
                        $chr = $str{$i};
                        switch ($chr) {
                            case '"' :
                                $result .= '"';
                                break;
                            case '\\':
                                $result .= '\\';
                                break;
                            case '/' :
                                $result .= '/';
                                break;
                            case 'b' :
                                $result .= chr(8);
                                break;
                            case 'f' :
                                $result .= chr(12);
                                break;
                            case 'n' :
                                $result .= chr(10);
                                break;
                            case 'r' :
                                $result .= chr(13);
                                break;
                            case 't' :
                                $result .= chr(9);
                                break;
                            case '\'' :
                                $result .= '\'';
                                break;
                            default:
                                throw new Ait_Zend_Json_Exception("Illegal escape "
                                    .  "sequence '" . $chr . "'");
                            }
                    } elseif ($chr == '"') {
                        break;
                    } else {
                        $result .= $chr;
                    }
                } while ($i < $str_length);

                $this->_token = self::DATUM;
                //$this->_tokenValue = substr($str, $start + 1, $i - $start - 1);
                $this->_tokenValue = $result;
                break;
            case 't':
                if (($i+ 3) < $str_length && substr($str, $start, 4) == "true") {
                    $this->_token = self::DATUM;
                }
                $this->_tokenValue = true;
                $i += 3;
                break;
            case 'f':
                if (($i+ 4) < $str_length && substr($str, $start, 5) == "false") {
                    $this->_token = self::DATUM;
                }
                $this->_tokenValue = false;
                $i += 4;
                break;
            case 'n':
                if (($i+ 3) < $str_length && substr($str, $start, 4) == "null") {
                    $this->_token = self::DATUM;
                }
                $this->_tokenValue = NULL;
                $i += 3;
                break;
        }

        if ($this->_token != self::EOF) {
            $this->_offset = $i + 1; // Consume the last token character
            return($this->_token);
        }

        $chr = $str{$i};
        if ($chr == '-' || $chr == '.' || ($chr >= '0' && $chr <= '9')) {
            if (preg_match('/-?([0-9])*(\.[0-9]*)?((e|E)((-|\+)?)[0-9]+)?/s',
                $str, $matches, PREG_OFFSET_CAPTURE, $start) && $matches[0][1] == $start) {

                $datum = $matches[0][0];

                if (is_numeric($datum)) {
                    if (preg_match('/^0\d+$/', $datum)) {
                        throw new Ait_Zend_Json_Exception("Octal notation not supported by JSON (value: $datum)");
                    } else {
                        $val  = intval($datum);
                        $fVal = floatval($datum);
                        $this->_tokenValue = ($val == $fVal ? $val : $fVal);
                    }
                } else {
                    throw new Ait_Zend_Json_Exception("Illegal number format: $datum");
                }

                $this->_token = self::DATUM;
                $this->_offset = $start + strlen($datum);
            }
        } else {
            throw new Ait_Zend_Json_Exception('Illegal Token');
        }

        return($this->_token);
    }
}

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Response
 * @version    $Id: Response.php 12519 2008-11-10 18:41:24Z alexander $
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_Http_Response represents an HTTP 1.0 / 1.1 response message. It
 * includes easy access to all the response's different elemts, as well as some
 * convenience methods for parsing and validating HTTP responses.
 *
 * @package    Zend_Http
 * @subpackage Response
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Ait_Zend_Http_Response
{
    /**
     * List of all known HTTP response codes - used by responseCodeAsText() to
     * translate numeric codes to messages.
     *
     * @var array
     */
    protected static $messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    /**
     * The HTTP version (1.0, 1.1)
     *
     * @var string
     */
    protected $version;

    /**
     * The HTTP response code
     *
     * @var int
     */
    protected $code;

    /**
     * The HTTP response code as string
     * (e.g. 'Not Found' for 404 or 'Internal Server Error' for 500)
     *
     * @var string
     */
    protected $message;

    /**
     * The HTTP response headers array
     *
     * @var array
     */
    protected $headers = array();

    /**
     * The HTTP response body
     *
     * @var string
     */
    protected $body;

    /**
     * HTTP response constructor
     *
     * In most cases, you would use Zend_Http_Response::fromString to parse an HTTP
     * response string and create a new Zend_Http_Response object.
     *
     * NOTE: The constructor no longer accepts nulls or empty values for the code and
     * headers and will throw an exception if the passed values do not form a valid HTTP
     * responses.
     *
     * If no message is passed, the message will be guessed according to the response code.
     *
     * @param int $code Response code (200, 404, ...)
     * @param array $headers Headers array
     * @param string $body Response body
     * @param string $version HTTP version
     * @param string $message Response code as text
     * @throws Ait_Zend_Http_Exception
     */
    public function __construct($code, $headers, $body = null, $version = '1.1', $message = null)
    {
        // Make sure the response code is valid and set it
        if (self::responseCodeAsText($code) === null) {
            throw new Ait_Zend_Http_Exception("{$code} is not a valid HTTP response code");
        }

        $this->code = $code;

        // Make sure we got valid headers and set them
        if (! is_array($headers)) {
            throw new Ait_Zend_Http_Exception('No valid headers were passed');
    }

        foreach ($headers as $name => $value) {
            if (is_int($name))
                list($name, $value) = explode(": ", $value, 1);

            $this->headers[ucwords(strtolower($name))] = $value;
        }

        // Set the body
        $this->body = $body;

        // Set the HTTP version
        if (! preg_match('|^\d\.\d$|', $version)) {
            throw new Ait_Zend_Http_Exception("Invalid HTTP response version: $version");
        }

        $this->version = $version;

        // If we got the response message, set it. Else, set it according to
        // the response code
        if (is_string($message)) {
            $this->message = $message;
        } else {
            $this->message = self::responseCodeAsText($code);
        }
    }

    /**
     * Check whether the response is an error
     *
     * @return boolean
     */
    public function isError()
    {
        $restype = floor($this->code / 100);
        if ($restype == 4 || $restype == 5) {
            return true;
        }

        return false;
    }

    /**
     * Check whether the response in successful
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        $restype = floor($this->code / 100);
        if ($restype == 2 || $restype == 1) { // Shouldn't 3xx count as success as well ???
            return true;
        }

        return false;
    }

    /**
     * Check whether the response is a redirection
     *
     * @return boolean
     */
    public function isRedirect()
    {
        $restype = floor($this->code / 100);
        if ($restype == 3) {
            return true;
        }

        return false;
    }

    /**
     * Get the response body as string
     *
     * This method returns the body of the HTTP response (the content), as it
     * should be in it's readable version - that is, after decoding it (if it
     * was decoded), deflating it (if it was gzip compressed), etc.
     *
     * If you want to get the raw body (as transfered on wire) use
     * $this->getRawBody() instead.
     *
     * @return string
     */
    public function getBody()
    {
        $body = '';

        // Decode the body if it was transfer-encoded
        switch ($this->getHeader('transfer-encoding')) {

            // Handle chunked body
            case 'chunked':
                $body = self::decodeChunkedBody($this->body);
                break;

            // No transfer encoding, or unknown encoding extension:
            // return body as is
            default:
                $body = $this->body;
                break;
        }

        // Decode any content-encoding (gzip or deflate) if needed
        switch (strtolower($this->getHeader('content-encoding'))) {

            // Handle gzip encoding
            case 'gzip':
                $body = self::decodeGzip($body);
                break;

            // Handle deflate encoding
            case 'deflate':
                $body = self::decodeDeflate($body);
                break;

            default:
                break;
        }

        return $body;
    }

    /**
     * Get the raw response body (as transfered "on wire") as string
     *
     * If the body is encoded (with Transfer-Encoding, not content-encoding -
     * IE "chunked" body), gzip compressed, etc. it will not be decoded.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->body;
    }

    /**
     * Get the HTTP version of the response
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the HTTP response status code
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->code;
    }

    /**
     * Return a message describing the HTTP response code
     * (Eg. "OK", "Not Found", "Moved Permanently")
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get a specific header as string, or null if it is not set
     *
     * @param string$header
     * @return string|array|null
     */
    public function getHeader($header)
    {
        $header = ucwords(strtolower($header));
        if (! is_string($header) || ! isset($this->headers[$header])) return null;

        return $this->headers[$header];
    }

    /**
     * Get all headers as string
     *
     * @param boolean $status_line Whether to return the first status line (IE "HTTP 200 OK")
     * @param string $br Line breaks (eg. "\n", "\r\n", "<br />")
     * @return string
     */
    public function getHeadersAsString($status_line = true, $br = "\n")
    {
        $str = '';

        if ($status_line) {
            $str = "HTTP/{$this->version} {$this->code} {$this->message}{$br}";
        }

        // Iterate over the headers and stringify them
        foreach ($this->headers as $name => $value)
        {
            if (is_string($value))
                $str .= "{$name}: {$value}{$br}";

            elseif (is_array($value)) {
                foreach ($value as $subval) {
                    $str .= "{$name}: {$subval}{$br}";
                }
            }
        }

        return $str;
    }

    /**
     * Get the entire response as string
     *
     * @param string $br Line breaks (eg. "\n", "\r\n", "<br />")
     * @return string
     */
    public function asString($br = "\n")
    {
        return $this->getHeadersAsString(true, $br) . $br . $this->getRawBody();
    }

    /**
     * A convenience function that returns a text representation of
     * HTTP response codes. Returns 'Unknown' for unknown codes.
     * Returns array of all codes, if $code is not specified.
     *
     * Conforms to HTTP/1.1 as defined in RFC 2616 (except for 'Unknown')
     * See http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10 for reference
     *
     * @param int $code HTTP response code
     * @param boolean $http11 Use HTTP version 1.1
     * @return string
     */
    public static function responseCodeAsText($code = null, $http11 = true)
    {
        $messages = self::$messages;
        if (! $http11) $messages[302] = 'Moved Temporarily';

        if ($code === null) {
            return $messages;
        } elseif (isset($messages[$code])) {
            return $messages[$code];
        } else {
            return 'Unknown';
        }
    }

    /**
     * Extract the response code from a response string
     *
     * @param string $response_str
     * @return int
     */
    public static function extractCode($response_str)
    {
        preg_match("|^HTTP/[\d\.x]+ (\d+)|", $response_str, $m);

        if (isset($m[1])) {
            return (int) $m[1];
        } else {
            return false;
        }
    }

    /**
     * Extract the HTTP message from a response
     *
     * @param string $response_str
     * @return string
     */
    public static function extractMessage($response_str)
    {
        preg_match("|^HTTP/[\d\.x]+ \d+ ([^\r\n]+)|", $response_str, $m);

        if (isset($m[1])) {
            return $m[1];
        } else {
            return false;
        }
    }

    /**
     * Extract the HTTP version from a response
     *
     * @param string $response_str
     * @return string
     */
    public static function extractVersion($response_str)
    {
        preg_match("|^HTTP/([\d\.x]+) \d+|", $response_str, $m);

        if (isset($m[1])) {
            return $m[1];
        } else {
            return false;
        }
    }

    /**
     * Extract the headers from a response string
     *
     * @param string $response_str
     * @return array
     */
    public static function extractHeaders($response_str)
    {
        $headers = array();

        // First, split body and headers
        $parts = preg_split('|(?:\r?\n){2}|m', $response_str, 2);
        if (! $parts[0]) return $headers;

        // Split headers part to lines
        $lines = explode("\n", $parts[0]);
        unset($parts);
        $last_header = null;

        foreach($lines as $line) {
            $line = trim($line, "\r\n");
            if ($line == "") break;

            if (preg_match("|^([\w-]+):\s+(.+)|", $line, $m)) {
                unset($last_header);
                $h_name = strtolower($m[1]);
                $h_value = $m[2];

                if (isset($headers[$h_name])) {
                    if (! is_array($headers[$h_name])) {
                        $headers[$h_name] = array($headers[$h_name]);
                    }

                    $headers[$h_name][] = $h_value;
                } else {
                    $headers[$h_name] = $h_value;
                }
                $last_header = $h_name;
            } elseif (preg_match("|^\s+(.+)$|", $line, $m) && $last_header !== null) {
                if (is_array($headers[$last_header])) {
                    end($headers[$last_header]);
                    $last_header_key = key($headers[$last_header]);
                    $headers[$last_header][$last_header_key] .= $m[1];
                } else {
                    $headers[$last_header] .= $m[1];
                }
            }
        }

        return $headers;
    }

    /**
     * Extract the body from a response string
     *
     * @param string $response_str
     * @return string
     */
    public static function extractBody($response_str)
    {
        $parts = preg_split('|(?:\r?\n){2}|m', $response_str, 2);
        if (isset($parts[1])) {
            return $parts[1];
        }
        return '';
    }

    /**
     * Decode a "chunked" transfer-encoded body and return the decoded text
     *
     * @param string $body
     * @return string
     */
    public static function decodeChunkedBody($body)
    {
        $decBody = '';

        while (preg_match("/^([\da-fA-F]+)[^\r\n]*\r\n/sm", trim($body), $m)) {
            $length = hexdec(trim($m[1]));
            $cut = strlen($m[0]);

            $decBody .= substr($body, $cut, $length);
            $body = substr($body, $cut + $length + 2);
        }

        return $decBody;
    }

    /**
     * Decode a gzip encoded message (when Content-encoding = gzip)
     *
     * Currently requires PHP with zlib support
     *
     * @param string $body
     * @return string
     */
    public static function decodeGzip($body)
    {
        if (! function_exists('gzinflate')) {
            throw new Ait_Zend_Http_Exception('Unable to decode gzipped response ' .
                'body: perhaps the zlib extension is not loaded?');
        }

        return gzinflate(substr($body, 10));
    }

    /**
     * Decode a zlib deflated message (when Content-encoding = deflate)
     *
     * Currently requires PHP with zlib support
     *
     * @param string $body
     * @return string
     */
    public static function decodeDeflate($body)
    {
        if (! function_exists('gzuncompress')) {
            throw new Ait_Zend_Http_Exception('Unable to decode deflated response ' .
                'body: perhaps the zlib extension is not loaded?');
        }

        return gzuncompress($body);
    }

    /**
     * Create a new Zend_Http_Response object from a string
     *
     * @param string $response_str
     * @return Ait_Zend_Http_Response
     */
    public static function fromString($response_str)
    {
        $code    = self::extractCode($response_str);
        $headers = self::extractHeaders($response_str);
        $body    = self::extractBody($response_str);
        $version = self::extractVersion($response_str);
        $message = self::extractMessage($response_str);

        return new Ait_Zend_Http_Response($code, $headers, $body, $version, $message);
    }
}
