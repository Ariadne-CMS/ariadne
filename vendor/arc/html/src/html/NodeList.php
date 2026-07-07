<?php

namespace arc\html;

class NodeList extends \ArrayObject {
    use \arc\xml\NodeListTrait {
        \arc\xml\NodeListTrait::parseArgs as traitParseArgs;
    }

    protected function canHaveContent( $tagName ) {
        $cantHaveContent = [
            'area', 'base', 'basefont', 'br',
            'col', 'frame', 'hr', 'img', 'input',
            'isindex', 'link', 'meta', 'param'
        ];
        return !in_array( trim( strtolower( $tagName ) ), $cantHaveContent );
    }

    protected function element( $tagName, $attributes, $content ) {
        $tagName =  \arc\html::name( $tagName );
        $el = '<' . $tagName;
        $el .= $this->getAttributes( $attributes );
        if ( $this->canHaveContent( $tagName ) ) {
            $el .= '>' . self::indent( $content, $this->writer->indent, $this->writer->newLine );
            $el .= '</' . $tagName . '>';
        } else {
            $el .= '>';
        }
        return $el;
    }

    protected function getAttributes( $attributes )
    {
        $result = '';
        if (count( $attributes )) {
            foreach ($attributes as $name => $value ) {
                $result .= \arc\html::attribute( $name, $value );
            }
        }
        return $result;
    }

    protected function parseArgs( $args ) {
        if ( is_string($args) ) {
            // allows for <input type="radio" checked>
            // as \arc\html::input(['type' => 'radio', 'checked']);
            return [ [ $args => $args ], '' ];
        } else {
            return $this->traitParseArgs($args);
        }
    }

}
