<?php

namespace arc\store;

final class TreeQueryParser {
	
	private $tokenizer;

	public function __construct($tokenizer) {
		$this->tokenizer = $tokenizer;
	}

    /**
     * @param string $query
     * @return string postgresql 'where' part of the sql query
     * @throws \LogicException when a parse error occurs
     */
    public function parse($query)
    {
        $indent   = 0;
        $part     = '';
        $fn       = [];
        $currentCheck = [];
        $position = 0;
        $expect   = 'name|parenthesis_open|not';

        foreach( call_user_func($this->tokenizer, $query) as $token ) {
            $type = key($token);
            list($token, $offset)=$token[$type];
            if ( !preg_match("/^$expect$/",$type) ) {
                throw new \LogicException('Parse error at '.$position.': expected '.$expect.', got '.$type.': '
                    .(substr($query,0, $position)." --> ".substr($query,$position)) );
            }
            switch($type) {
                case 'number':
                case 'string':
                    if (strpos($part, '{placeholder}')!==false) {
                        $fn[] = str_replace('{placeholder}', $token, $part);
                    } else {
                        $fn[] = $part.$token;
                    }
                    $part   = '';
                    $expect = 'operator|parenthesis_close';
                break;
                case 'name':
                    switch ($token) {
                        case 'nodes.path':
                            $part = '$node->getPath()';
                        break;
                        case 'nodes.parent':
                            $part = '$node->parentNode->getPath()';
                        break;
                        case 'nodes.name':
                            $part = '$node->nodeName';
                        break;
                        case 'nodes.mtime':
                            $part = '$node->nodeValue->mtime';
                        break;
                        case 'nodes.ctime':
                            $part = '$node->nodeValue->ctime';
                        break;
                        default:
                            $part = '$node->nodeValue->'.str_replace('.','->',$token);
                        break;
                    }
                    $expect = 'compare';
                break;
                case 'compare':
                    switch( $token ) {
                        case '>':
                        case '>=':
                        case '<':
                        case '<=':
                        case '!=':
                            $part = '( '.$part. ' ?? null ) '.$token;
                        break;
                        case '<>':
                            $part = '( '.$part. ' ?? null ) !=';
                        break;
                        case '=':
                            $part = '( '.$part. ' ?? null ) ==';
                        break;
                        case '?':
                            $part ='property_exists('.$part.' ?? null,{placeholder})';
                        break;
                        case '~=':
                            $part = '$like('.$part.' ?? null,{placeholder})';
                        break;
                        case '!~':
                            $part = '!$like('.$part.' ?? null,{placeholder})';
                        break;
                    }
                    $expect = 'number|string';
                break;
                case 'not':
                    $fn[]   = '!';
                    $expect = 'name|parenthesis_open';
                break;
                case 'operator':
                    switch($token) {
                        case 'and':
                            $fn[] = '&&';
                        break;
                        case 'or':
                            $fn[] = '||';
                        break;
                    }
                    $expect = 'name|parenthesis_open|not';
                break;
                case 'parenthesis_open':
                    $fn[]   = $token;
                    $indent++;
                    $expect = 'name|parenthesis_open|not';
                break;
                case 'parenthesis_close':
                    $fn[]   = $token;
                    $indent--;
                    if ( $indent>0 ) {
                        $expect = 'operator|parenthesis_close';
                    } else {
                        $expect = 'operator';
                    }
                break;
            }
            $position += $offset + strlen($token);
        }
        if ( $indent!=0 ) {
            throw new \LogicException('unbalanced parenthesis');
        } else if ( trim($part) ) {
            $position -= strlen($token);
            throw new \LogicException('parse error at '.$position.': '.(substr($query,0, $position)." --> ".substr($query,$position)));
        } else {
            $fn = implode(' ',$fn);
                $like = function($haystack, $needle) {
                $re = str_replace('%', '.*', $needle);
                return preg_match('|'.$re.'|i', $haystack);
            };
            $script = 'return function($node) use ($like) { return '.$fn.'; };';
            return eval($script);
        }
    }

}
