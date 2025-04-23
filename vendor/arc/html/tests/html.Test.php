<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class TestHTML extends PHPUnit_Framework_TestCase
{

	var $html1 = '<html>
    <head>
        <title>Example</title>
    </head>
    <body>
        <h1>Example Title</h1>
        <hr>
        <p>A paragraph</p>
    </body>
</html>
';

    function testHTMLBasics()
    {
        $doctype = \arc\html::doctype();
        $this->assertEquals( '<!doctype html>', (string) $doctype );
        $comment = \arc\html::comment('A comment');
        $this->assertEquals( '<!-- A comment -->', (string) $comment );
    }

    function testHTMLWriter()
    {
        $html = \arc\html::ul( [ 'class' => 'menu' ],
            \arc\html::li('menu 1 ',
            	\arc\html::input(['type' => 'radio', 'checked'])
            )
            ->li('menu 2')
        );
        $this->assertEquals(
            "<ul class=\"menu\">\r\n\t<li>\r\n\t\tmenu 1 <input type=\"radio\" checked>\r\n\t</li>"
            ."\r\n\t<li>menu 2</li>\r\n</ul>",
            ''.$html
        );
    }

    function testHTMLParsing()
    {
        $html = \arc\html::parse( $this->html1 );
        $error = null;
        $htmlString = ''.$html;
        $html2 = \arc\html::parse( $htmlString );
        $this->assertEquals( '<title>Example</title>', $html->head->title );
        $this->assertEquals( 'Example', (string) $html->head->title->nodeValue );
        $this->assertEquals( (string) $html->head->title, (string) $html2->head->title );
        $this->assertEquals( 'Example', (string) $html->head->title->nodeValue );
    }

    function testHTMLFind()
    {
        $html = \arc\html::parse( $this->html1 );
        $title = $html->find('head title')[0];
        $this->assertEquals( 'Example', $title->nodeValue );
    }

    function testDomMethods()
    {
        $html = \arc\html::parse( $this->html1 );
        $title = $html->getElementsByTagName('title')[0];
        $this->assertEquals( 'Example', $title->nodeValue );
    }

    function testEncoding()
    {
        $entities = 'I&ntilde;t&euml;rn&acirc;ti&ocirc;n&agrave;liz&aelig;ti&oslash;n';
        $i18n = html_entity_decode($entities, ENT_HTML5, 'UTF-8');

        $htmlString = "<html><head><title>Encodingtest</title></head><body>$i18n</body></html>";
        $html = \arc\html::parse($htmlString, "utf-8");
        $i18nEl = $html->body->nodeValue;
        $this->assertEquals( $i18n, (string) $i18nEl );

        $htmlString = "<html><body>$i18n</body></html>";
        $html = \arc\html::parse($htmlString, "utf-8");
        $i18nEl = $html->body->nodeValue;
        $this->assertEquals( $i18n, (string) $i18nEl );

        $htmlString = "<ul><li>$i18n</li></ul>";
        $html = \arc\html::parse($htmlString, "utf-8");
        $i18nEl = $html->ul->li->nodeValue;
        $this->assertEquals( $i18n, (string) $i18nEl );

    }

}
