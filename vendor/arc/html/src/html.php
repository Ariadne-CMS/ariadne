<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc;

/**
 * This class contains a html parser and is a proxy for the html Writer.
 * Any method statically called on this class
 * will reroute the call to the HTML writer instance at 
 * \arc\html::$writer. Except the following methods:
 * parse, name, value, attribute, doctype, preamble, comment, cdata
 * If you need those, use the Writer instance directly
 */
class html extends xml
{
    /**
     * @var html\Writer The writer instance to use by default
     */
    public static $writer=null;    
        
    public static function __callStatic( $name, $args ) 
    {
        if ( !isset(self::$writer) ) {
            self::$writer = new html\Writer();
        }
        return call_user_func_array( [ self::$writer, $name ], $args );
    }

    /**
     * This parses an HTML string and returns a Proxy
     * @param string|Proxy $html
     * @param string $encoding
     * @return Proxy
     * @throws \arc\Exception
     */
    public static function parse( $html=null, $encoding = null ) 
    {
        $parser = new html\Parser();
        return $parser->parse( $html, $encoding );
    }

    /**
     * Returns a guaranteed valid HTML attribute value. Removes illegal characters.
     * @param string|array|bool $value
     * @return string
     */
    static public function value( $value ) 
    {
        if ( is_array( $value ) ) {
            $content = array_reduce( $value, function( $result, $value ) 
            {
                return $result . ' ' . static::value( $value );
            } );
        } else if ( is_bool( $value ) ) {
            $content = $value ? 'true' : 'false';
        } else {
            $content = htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
        }
        return $content;
    }

    /**
     * Returns a guaranteed valid HTML attribute. Removes illegal characters.
     * Allows for 'naked' attributes, without a value.
     * @param string $name
     * @param string|array|bool $value
     * @return string
     */
    static public function attribute( $name, $value )
    {
        if ($name === $value) {
            return ' ' . self::name( $name );
        } else if (is_numeric( $name )) {
            return ' ' . self::name( $value );
        } else {
            return \arc\xml::attribute( $name, $value );
        }
    }

    /**
     * Returns a HTML doctype string
     * @param string $version The doctype version to use, available are: 'html5', 'html4' (strict), 'transitional' (html4) and 'xhtml'
     * @retun string
     */
    static public function doctype( $version='html5' )
    {
        $doctypes = [
            'html5'        => '<!doctype html>',
            'html4'        => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
            'transitional' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
            'frameset'     => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
            'xhtml'        => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
        ];
        return isset( $doctypes[$version] ) ? $doctypes[$version] : $doctypes['html5'];
    }

}
