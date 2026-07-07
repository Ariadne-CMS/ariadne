<?php

namespace arc\store;

final class PSQLQueryParser {
	
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
        $sql      = [];
        $position = 0;
        $expect   = 'name|parenthesis_open|not';
        foreach( call_user_func($this->tokenizer, $query) as $token ) {
            $tokenType = key($token);
            list($tokenValue, $offset)=$token[$tokenType];
            if ( !preg_match("/^$expect$/",$tokenType) ) {
                throw new \LogicException('Parse error at '.$position.': expected '.$expect.', got '.$tokenType.': '
                    .(substr($query,0, $position)." --> ".substr($query,$position)) );
            }
            switch($tokenType) {
                case 'number':
                case 'string':
                    $sql[]  = $tokenValue;
                    $expect = 'operator|parenthesis_close';
                break;
                case 'name':
                    switch ($tokenValue) {
                        case 'nodes.path':
                        case 'nodes.parent':
                        case 'nodes.name':
                        case 'nodes.mtime':
                        case 'nodes.ctime':
                            $sql[] = $tokenValue;
                        break;
                        default:
                            $sql[] = "nodes.data #>> '{".str_replace('.',',',$tokenValue)."}'";
                        break;
                    }
                    $expect = 'compare';
                break;
                case 'compare':
                    switch( $tokenValue ) {
                        case '>':
                        case '>=':
                        case '<':
                        case '<=':
                        case '=':
                        case '<>':
                        case '!=':
                            $sql[] = $tokenValue;
                        break;
                        case '?':
                            $part  = $sql[count($sql)-1];
                            $part  = str_replace('#>>', '#>', $part);
                            $sql[count($sql)-1] = $part;
                            $sql[] = $tokenValue;                            
                        break;
                        case '~=':
                            $sql[] = 'like';
                        break;
                        case '!~':
                            $sql[] = 'not like';
                        break;
                    }
                    $expect = 'number|string';
                break;
                case 'not':
                    $sql[]  = $tokenValue;
                    $expect = 'name|parenthesis_open';
                break;
                case 'operator':
                    $sql[]  = $tokenValue;
                    $expect = 'name|parenthesis_open|not';
                break;
                case 'parenthesis_open':
                    $sql[]  = $tokenValue;
                    $indent++;
                    $expect = 'name|parenthesis_open|not';
                break;
                case 'parenthesis_close':
                    $sql[]  = $tokenValue;
                    $indent--;
                    if ( $indent>0 ) {
                        $expect = 'operator|parenthesis_close';
                    } else {
                        $expect = 'operator';
                    }
                break;
            }
            $position += $offset + strlen($tokenValue);
        }
        if ( $indent!=0 ) {
            throw new \LogicException('unbalanced parenthesis');
        }
        if ($position<strlen($query)) {
            throw new \LogicException('Parse error at '.$position.': unrecognized token: '
            .(substr($query,0, $position)." --> ".substr($query,$position)) );
        }
        foreach(['number','string','compare'] as $tokenType) {
            if (strpos($expect, $tokenType)!==false) {
                throw new \LogicException('Parse error at '.$position.': expected '.$expect.': '
                .(substr($query,0, $position)." --> ".substr($query,$position)) );

            }
        }
        return implode(' ',$sql);
    }

}
