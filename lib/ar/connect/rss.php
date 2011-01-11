<?php

ar_pinp::allow('ar_connect_rss');

ar::load('xml');

class ar_connect_rss extends arBase {

	public static function client( $url, $httpClient = null ) {
		if ( !isset($httpClient) ) {
			$httpClient = ar_http::client();
		}
		return new ar_connect_rssClient( $url, $httpClient );
	}

}

class ar_connect_rssClient extends ar_xmlDataBinding {

	private $httpClient = null;
	private $feed = null;
	private $xml = null;
	
	public function __construct( $feed = null, $httpClient = null ) {
		$this->feed = $feed;
		$this->httpClient = $httpClient;
		if ($feed && $this->httpClient) {
			$this->get( $feed )->parse();
		}
	}

	public function get( $url ) {
		$this->xml = $this->httpClient->get( $url );
		return $this;
	}
	
	public function parse( $xml = null ) {
		if ( !$xml ) {
			if ($this->xml) {
				$xml = $this->xml;
			}
		}
		if ($xml) {
			$dom = ar_xml::parse( $xml );

			$this->bind( $dom->rss->channel->title, 'title' )
				->bind( $dom->rss->channel->link, 'link' )
				->bindAsArray( 
					$dom->rss->channel->item, 
					'items', 
					array( 'ar_connect_rssClient', 'parseItem' )
				);
		}
		return $this;
	}

	public function parseItem( $item ) {
		return $item->bind( $item->title, "title")
				->bind( $item->link, "link" )
				->bind( $item->guid, "guid" )
				->bind( $item->{"media:content"}[0]->attributes, "media_content", "array")
				->bind( $item->{"media:description"}[0], "media_description", "html")
				->bind( $item->description, "description")
				->bind( $item->{"dc:creator"}, "dc_creator")
				->bind( $item->pubDate, "pubDate");	
	}

}
	
?>