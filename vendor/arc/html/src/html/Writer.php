<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace arc\html;

/**
 * This class allows you to create valid and nicely indented HTML strings
 * Any method not explicitly defined is interpreted as a new HTML element to create.
 */
class Writer {

    public $indent = "\t";
    public $newLine = "\r\n";

    public function __construct( $options = [] )
    {
        $optionList = ['indent','newLine'];
        foreach( $options as $option => $optionValue ) {
            if ( in_array( $option, $optionList ) ) {
                $this->{$option} = $optionValue;
            }
        }
    }

    public function __call( $name, $args )
    {
        return call_user_func_array( [ new \arc\html\NodeList( [], $this), $name], $args );
    }

}

