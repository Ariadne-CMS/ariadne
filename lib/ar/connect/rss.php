<?php

ar_pinp::allow('ar_connect_rss');

ar::load('xml');

class ar_connect_rss extends arBase {

	public static function client( $url = null, $httpClient = null ) {
		if ( !isset($httpClient) ) {
			$httpClient = ar_http::client();
		}
		return new ar_connect_rssClient( $url, $httpClient );
	}
	
	public static function parse( $xml ) {
		$client = new ar_connect_rssClient();
		return $client->parse( $xml );
	}

}

class ar_connect_rssClient extends ar_xmlDataBinding {

	private $httpClient = null;
	private $feed = null;
	
	public function __construct( $feed = null, $httpClient = null ) {
		$this->feed = $feed;
		$this->httpClient = $httpClient;
		if ($feed && $this->httpClient) {
			$this->get( $feed );
		}
	}

	public function get( $url, $request = null, $options = array() ) {
		$xml = $this->httpClient->get( $url, $request, $options );
		if ( $xml && !ar_error::isError( $xml ) ) {
			return $this->parse( $xml );
		} else {
			return $xml;
		}
	}
	
	public function parse( $xml ) {
		$dom = ar_xml::parse( $xml );
		$channel = $dom->rss->channel[0];
		$this->bind( $channel->title, 'title' )
			->bind( $channel->link, 'link' )
			->bind( $channel->description, 'description' )
			->bind( $channel->language, 'language' )
			->bind( $channel->copyright, 'copyright' )
			->bind( $channel->managingEditor, 'managingEditor' )
			->bind( $channel->webMaster, 'webMaster' )
			->bind( $channel->pubDate, 'pubDate' )
			->bind( $channel->lastBuildDate, 'lastBuildDate' )
			->bind( $channel->category, 'category' )
			->bind( $channel->generator, 'generator' )
			->bind( $channel->cloud->attributes, 'cloud', 'array' )
			->bind( $channel->ttl, 'ttl', 'int' )
			->bind( $channel->image->attributes, 'image', 'array' )
			->bindAsArray( 
				$channel->item, 
				'items', 
				array( 'ar_connect_rssClient', 'parseItem' )
			)
			->bind( $dom, 'rawXML', 'xml' );
		return $this;
	}

	public function parseItem( $item ) {
		return $item->bind( $item->title, 'title')
				->bind( $item->link, 'link' )
				->bind( $item->description, 'description')
				->bind( $item->guid, 'guid' )
				->bind( $item->author, 'author' )
				->bind( $item->category, 'category' )
				->bind( $item->comments, 'comments' )
				->bindAsArray( $item->enclosure, 'enclosures', array('ar_connect_rssClient', 'parseEnclosure') )
				->bind( $item->source->nodeValue, 'source' )
				->bind( $item->source->getAttribute('url'), 'source_url' )
				->bind( $item->pubDate, 'pubDate')	
				->bind( $item->{'content:encoded'}, 'content:encoded', 'html' )
				->bind( $item, 'rawXML', 'xml' );
	}

	public function parseEnclosure( $item ) {
		return $item->bind( $item->getAttribute('url'), 'url')
				->bind( $item->getAttribute('length'), 'length' )
				->bind( $item->getAttribute('type'), 'type');
	}
}
?>