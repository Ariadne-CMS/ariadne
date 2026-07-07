<?php

    /*
     * This file is part of the Ariadne Component Library.
     *
     * (c) Muze <info@muze.nl>
     *
     * For the full copyright and license information, please view the
     * LICENSE
     * file that was distributed with this source code.
     */

    class TestStore extends PHPUnit\Framework\TestCase
    {
        static $store = null;
        static $tree = null;

        static $dns = [
            'pgsql:host=localhost;dbname=arc_store_test;user=postgres;password=',
            'mysql:host=localhost;dbname=arc_store_test;user=root;password='
        ];
        

        public static function setupBeforeClass() : void
        {            
            self::$tree = \arc\tree::expand([]);
            self::$store = new \arc\store\TreeStore( 
                self::$tree,
                new \arc\store\TreeQueryParser( array('\arc\store','tokenizer')),
                array('\arc\store\TreeStore','getResultHandler')
            );
//            self::$store = \arc\store::connect(self::$dns[0]);
            self::$store->initialize();

        }

        function __construct()
        {
            parent::__construct();
        }

        function testPostgresqlStoreQuery()
        {
            $qp = new \arc\store\PSQLQueryParser(array('\arc\store','tokenizer'));
            $result = $qp->parse("nodes.path='/'");
            $this->assertEquals("nodes.path = '/'", $result);
            $result = $qp->parse("foo.bar='baz'");
            $this->assertEquals("nodes.data #>> '{foo,bar}' = 'baz'", $result);
            $result = $qp->parse("foo.bar !~ 'b%z'");
            $this->assertEquals("nodes.data #>> '{foo,bar}' not like 'b%z'", $result);
            $result = $qp->parse("foo.bar ~= 'b%z'");
            $this->assertEquals("nodes.data #>> '{foo,bar}' like 'b%z'", $result);
            $result = $qp->parse("foo ? 'bar'");
            $this->assertEquals("nodes.data #> '{foo}' ? 'bar'", $result);
            $result = $qp->parse("foo.bar>3");
            $this->assertEquals("nodes.data #>> '{foo,bar}' > 3",$result);
            $result = $qp->parse("foo.bar <> 'bar\\'bar'");
            $this->assertEquals("nodes.data #>> '{foo,bar}' <> 'bar\\'bar'",$result);
            $result = $qp->parse("foo.bar != 'bar\\'bar'");
            $this->assertEquals("nodes.data #>> '{foo,bar}' != 'bar\\'bar'",$result);
            $result = $qp->parse("foo.bar !~ 'b%z' and bar.foo = 3");
            $this->assertEquals("nodes.data #>> '{foo,bar}' not like 'b%z' and nodes.data #>> '{bar,foo}' = 3", $result);
            $result = $qp->parse("(foo.bar !~ 'b%z' and bar.foo = 3)");
            $this->assertEquals("( nodes.data #>> '{foo,bar}' not like 'b%z' and nodes.data #>> '{bar,foo}' = 3 )", $result);
            $result = $qp->parse("(foo.bar !~ 'b%z' and bar.foo = 3) or nodes.path = '/'");
            $this->assertEquals("( nodes.data #>> '{foo,bar}' not like 'b%z' and nodes.data #>> '{bar,foo}' = 3 ) or nodes.path = '/'", $result);
            $result = $qp->parse("not(foo.bar = 'bar')");
            $this->assertEquals("not ( nodes.data #>> '{foo,bar}' = 'bar' )", $result);
        }

        function testMysqlStoreQuery()
        {
            $qp = new \arc\store\MySQLQueryParser(array('\arc\store','tokenizer'));
            $result = $qp->parse("nodes.path='/'");
            $this->assertEquals("nodes.path = '/'", $result);
            $result = $qp->parse("foo.bar='baz'");
            $this->assertEquals("JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo.bar')) = 'baz'", $result);
            $result = $qp->parse("foo.bar !~ 'b%z'");
            $this->assertEquals("JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo.bar')) not like 'b%z'", $result);
            $result = $qp->parse("foo.bar ~= 'b%z'");
            $this->assertEquals("JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo.bar')) like 'b%z'", $result);
            $result = $qp->parse("foo ? 'bar'");
            $this->assertEquals("JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo')) IS NOT NULL 'bar'", $result);
            $result = $qp->parse("foo.bar>3");
            $this->assertEquals("JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo.bar')) > 3",$result);
            $result = $qp->parse("foo.bar <> 'bar\\'bar'");
            $this->assertEquals("JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo.bar')) <> 'bar\'bar'",$result);
            $result = $qp->parse("foo.bar != 'bar\\'bar'");
            $this->assertEquals("JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo.bar')) != 'bar\'bar'",$result);
            $result = $qp->parse("foo.bar !~ 'b%z' and bar.foo = 3");
            $this->assertEquals("JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo.bar')) not like 'b%z' and JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.bar.foo')) = 3", $result);
            $result = $qp->parse("(foo.bar !~ 'b%z' and bar.foo = 3)");
            $this->assertEquals("( JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo.bar')) not like 'b%z' and JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.bar.foo')) = 3 )", $result);
            $result = $qp->parse("(foo.bar !~ 'b%z' and bar.foo = 3) or nodes.path = '/'");
            $this->assertEquals("( JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo.bar')) not like 'b%z' and JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.bar.foo')) = 3 ) or nodes.path = '/'", $result);
            $result = $qp->parse("not(foo.bar = 'bar')");
            $this->assertEquals("not ( JSON_UNQUOTE(JSON_EXTRACT( nodes.data, '$.foo.bar')) = 'bar' )", $result);
        }


        function testMaliciousQueries()
        {
            $qp = new \arc\store\PSQLQueryParser(array('\arc\store','tokenizer'));
            $this->expectException(\LogicException::class);
            $result = $qp->parse("nodes.path=''/'");
        }

        function testStoreParseError()
        {
            $qp = new \arc\store\PSQLQueryParser(array('\arc\store','tokenizer'));
            $this->expectException(\LogicException::class);
            $result = $qp->parse("just_a_name_with_1_number");
            echo $result."\n";
        }

        function testStoreParseParenthesisError()
        {
            $qp = new \arc\store\PSQLQueryParser(array('\arc\store','tokenizer'));
            $this->expectException(\LogicException::class);
            $result = $qp->parse("(parenthesis = 'unbalanced'");
        }

        function testStoreParseStringError()
        {
            $qp = new \arc\store\PSQLQueryParser(array('\arc\store','tokenizer'));
            $this->expectException(\LogicException::class);
            $result = $qp->parse("foo = 'bar");
        }


        function testStoreSave()
        {
            $result = self::$store->save(\arc\prototype::create([
                'name' => 'Foo',
                'foo' => \arc\prototype::create([
                    'bar' => 'Baz',
                    'int' => 10
                ])
            ]), '/foo/');
            $this->assertTrue($result);
            $result = self::$store->get('/foo/');
            $this->assertEquals($result->nodeValue->name,'Foo');
            $this->assertEquals($result->nodeValue->foo->bar,'Baz');
        }

        function testStoreExists()
        {
            $result = self::$store->exists('/foo/');
            $this->assertTrue($result);
            $result = self::$store->exists('/no-i-dont/');
            $this->assertFalse($result);
        }

        function testStoreLs()
        {
            $result = self::$store->ls('/');
            $this->assertContainsOnly('stdClass',$result);
            $this->assertCount(1, $result);
            $result = self::$store->cd('/foo/')->ls();
            $this->assertCount(0, $result);
        }

        function testStoreFind()
        {
            $result = self::$store->find("foo.bar ~= 'Ba%'");
            $this->assertCount(1, $result);
            $result = self::$store->find("nodes.path ~= '/%'");
            $this->assertContainsOnly('stdClass',$result);
            $this->assertCount(2, $result);
        }

        function testStoreDelete()
        {
            $result = self::$store->delete('/foo/');
            $this->assertTrue($result);
            $result = self::$store->exists('/foo/');
            $this->assertFalse($result);
        }

        public static function tearDownAfterClass() :void
        {
/*
            $db = new PDO(self::$dns[0]);
            $db->exec('drop table nodes;');
            $db->exec('drop table objects;');
            $db->exec('drop table links;');
*/
        }
    
    }
