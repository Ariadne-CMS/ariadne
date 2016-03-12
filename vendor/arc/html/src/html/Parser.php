<?php

namespace arc\html;

class Parser
{
    public $options = [
        'libxml_options' => 0
    ];

    public function __construct( $options = array() )
    {
        $optionList = [ 'libxml_options' ];
        foreach( $options as $option => $optionValue ) {
            if ( in_array( $option, $optionList ) ) {
                $this->{$option} = $optionValue;
            }
        }
    }

    public function parse( $html, $encoding = null )
    {
        if ( !$html ) {
            return \arc\html\Proxy( null );
        }
        if ( $html instanceof Proxy ) { // already parsed
            return $html;
        }
        $html = (string) $html;
        if ( stripos($html, '<body>')!==false ) {
            return $this->parseFull( $html, $encoding );
        } else {
            return $this->parsePartial( $html, $encoding );
        }
    }

    private function parsePartial( $html, $encoding )
    {
        $result = $this->parseFull( '<body id="ArcPartialHTML">'.$html.'</body>', $encoding );
        if ( $result ) {
            $result = new \arc\html\Proxy( $result->find('#ArcPartialHTML')[0]->children(), $this );
//            $result = new \arc\html\Proxy( $result->children(), $this );
        } else {
            throw new \arc\Exception('parse error');
        }
        return $result;
    }

    private function throwError($prevErrorSetting)
    {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            libxml_use_internal_errors( $prevErrorSetting );
            $message = 'Incorrect html passed.';
            foreach ( $errors as $error ) {
                $message .= "\nline: ".$error->line."; column: ".$error->column."; ".$error->message;
            }
            throw new \arc\Exception( $message, \arc\exceptions::ILLEGAL_ARGUMENT );
    }

    private function insertEncoding($html, $encoding)
    {
        $meta = '<meta id="ArcTempEncoding" http-equiv="content-type" content="text/html; charset="'.  htmlspecialchars($encoding) .'">';
        if ( preg_match('/<head([^>]*)>/i', $html) ) {
            $html = preg_replace('/<head([^>]*)>/i', '<head\\1>'.$meta, $html);
        } else if ( preg_match('/<body([^>]*)>/i', $html) ) {
            $html = preg_replace('/<body([^>]*)>/i', '<head>'.$meta.'</head><body\\1>', $html);
        } else {
            $html = $meta.$html;
        }
        return $html;
    }

    private function removeEncoding( $dom, $encoding)
    {
        $meta = $dom->getElementById('ArcTempEncoding');
        $meta->parentNode->removeChild($meta);
    }

    private function parseFull( $html, $encoding )
    {
        $dom = new \DomDocument();
        libxml_disable_entity_loader(); // prevents XXE attacks
        $prevErrorSetting = libxml_use_internal_errors(true);
        if ( $encoding ) {
            $html = $this->insertEncoding($html, $encoding);
        }
        if ( !$dom->loadHTML( $html, $this->options['libxml_options'] ) ) {
            $this->throwError($prevErrorSetting);
        }
        if ( $encoding ) {
            $this->removeEncoding($dom, $encoding);
        }
        libxml_use_internal_errors( $prevErrorSetting );
        return new \arc\html\Proxy( simplexml_import_dom( $dom ), $this );
    }

}
