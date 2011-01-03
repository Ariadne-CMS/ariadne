<?php

ar_pinp::allow('ar_connect_rss');

ar::load('xml');

class ar_connect_rss extends ar_xmlDataBinding {

	public function __construct( $feed = null ) {
		if ($feed) {
			$xml = ar_http::get($feed);
			$dom = ar_xml::parse( $xml );

			$this->bind( $dom->rss->channel->title, 'title' )
				->bind( $dom->rss->channel->link, 'link' )
				->bindAsArray( 
					$dom->rss->channel->item, 
					'items', 
					array( 'ar_connect_rss', 'parseItem' )
				);
		}
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

	public static function get( $url ) {
		return new ar_connect_rss( $url );
	}

}
	
?>