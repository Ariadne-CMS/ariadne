<?php

namespace arc;

final class store {

    /**
     * @return mixed current store
     */
    public static function getStore()
    {
        $context = \arc\context::$context;
        return $context->arcStore;
    }

    /**
     * Connects to an ARC object store
     * @param string $dsn postgresql connection string
     * @param string $resultHandler handler that executes the sql query
     * @return store\Store the store
     */
    public static function connect($dsn, $resultHandler=null)
    {
        $db = new \PDO($dsn);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        if (strpos($dsn, 'mysql:')===0) {
            $storeType = '\arc\store\MySQL';
        } else if (strpos($dsn, 'pgsql:')===0) {
            $storeType = '\arc\store\PSQL';
        }
        if (!$storeType) {
            throw new \arc\ConfigError('Unknown database type');
        }

        if (!$resultHandler) {
            $resultHandler = array('\arc\store\ResultHandlers', 'getDBHandler');
        }

        $queryParserClassName = $storeType.'QueryParser';
        $className = $storeType.'Store';
        $store = new $className(
            $db, 
            new $queryParserClassName(array('\arc\store','tokenizer')), 
            $resultHandler($db)
        );

        \arc\context::push([
            'arcStore' => $store
        ]);
        return $store;
    }

    /**
     * disconnects the store
     */
    public static function disconnect()
    {
        \arc\context::pop();
    }

    /**
     *
     * @param $path
     * @return mixed
     */
    public static function cd($path)
    {
        return self::getStore()->cd($path);
    }

    /**
     * @param $query
     * @return mixed
     */
    public static function find($query)
    {
        return self::getStore()->find($query);
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function parents($path)
    {
        return self::getStore()->parents($path);
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function ls($path)
    {
        return self::getStore()->ls($path);
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function get($path)
    {
        return self::getStore()->get($path);
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function exists($path)
    {
        return self::getStore()->exists($path);
    }

    /**
     * yields the tokens in the search query expression
     * @param string $query
     * @return \Generator
     * @throws \LogicException
     */        
    public static function tokenizer($query) {
    /*
        query syntax:
        part     = ( name '.' )* name compare value
        query    = part | part operator (not) part | (not) '(' query ')'  
        operator = 'and' | 'or'
        not      = 'not'
        compare  = '<' | '>' | '=' | '>=' | '<=' | 'like' | 'not like' | '?'
        value    = number | string
        number   = [0-9]* ('.' [0-9]+)?
        string   = \' [^\\1]* \'

        e.g: "contact.address.street like '%Crescent%' and ( name.firstname = 'Foo' or name.lastname = 'Bar')"
    */
        $token = <<<'REGEX'
/^\s*
(
            (?<compare>
                <= | >= | <> | < | > | = | != | ~= | !~ | \?
            )
            |
            (?<operator>
                and | or
            )
            |
            (?<not>
                not
            )
            |
            (?<name>
                [a-z]+[a-z0-9_-]*
                (?: \. [a-z]+[a-z0-9_-]* )*
            )
            |
            (?<number>
                [+-]?[0-9](\.[0-9]+)?
            )
            |
            (?<string>
                 '(?:\\.|[^\\'])*' 
            )
            |
            (?<parenthesis_open>
                \(
            )
            |
            (?<parenthesis_close>
                \)
            )
)/xi
REGEX;
        do {
            $result = preg_match($token, $query, $matches, PREG_OFFSET_CAPTURE);
            if ($result) {
                $value  = $matches[0][0];
                $offset = $matches[0][1];
                $query = substr($query, strlen($value) + $offset);
                yield array_filter(
                    $matches,
                    function($val, $key) {
                        return !is_int($key) && $val[0];
                    },
                    ARRAY_FILTER_USE_BOTH
                );
            }
        } while($result);
        if ( trim($query) ) {
            throw new \LogicException('Could not parse '.$query);
        }
    }

}
