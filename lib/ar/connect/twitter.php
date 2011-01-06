<?php

ar_pinp::allow('ar_connect_twitter');

ar::load('http');

class ar_connect_twitter extends arBase {

	private $rootURL = 'http://api.twitter.com/1/';
	private $searchURL = 'http://search.twitter.com/search.json';
	private $client = null;
	
	public function __construct( $httpClient = null ) {
		if (!$httpClient) {
			$httpClient = new ar_httpClientStream();
		}
		$this->client = $httpClient;
	}
	
	public static function get( $httpClient = null ) {
		return new ar_connect_twitter( $httpClient );
	}
	
	public function statuses( $options = array() ) {
		// http://dev.twitter.com/doc/get/statuses/user_timeline
		$defaults = array(
			'count' => 10, 
			'page' => 1
		);
		$options += $defaults;
		if ($options['user']) {
			$url = ar::url( $this->rootURL.'statuses/user_timeline/'.$options['user'].'.json' );
			unset($options['user']);
		} else {
			$url = ar::url( $this->rootURL.'statuses/user_timeline.json' );
		}
		$url->query->import( $options );
		$json = $this->client->get( $url );
		if ($json && !ar_error::isError($json) ) {
			$statuses = json_decode($json);
			foreach( $statuses as $index => $status ) {
//				$statuses[$index]['created_at'] = new ar_i18nDateTime( $status['created_at'] );
//				$statuses[$index]['user']['created_at'] = new ar_i18nDateTime( $status['user']['create_at'] );
				$status->user->profile_image_url = ar::url( $status->user->profile_image_url );
			}
			return $statuses;
		} else {
			return $json;
		}
	}

	public function trends( $timeslice = 'current', $options = array() ) {
		switch ( $timeslice ) {
			case 'current':			//http://dev.twitter.com/doc/get/trends/current
			case 'daily':			//http://dev.twitter.com/doc/get/trends/daily
			case 'weekly':			//http://dev.twitter.com/doc/get/trends/weekly
			break;
			default :
				$timeslice = 'current';
			break;
		}
		$url = ar::url( $this->rootURL.'trends/'.$timeslice.'.json' );
		$url->query->import( $options );
		$json = $this->client->get( $url );
		if ($json && !ar_error::isError($json) ) {
			$trends = json_decode( $json );
			return $trends;
		} else {
			return $json;
		}
	}
	
	public function search( $options = array() ) {
		// http://search.twitter.com/api/
		if ( is_string($options) ) {
			$options = array( 'q' => $options );
		}
		$url = ar::url( $this->searchURL );
		$url->query->import( $options );
		$json = $this->client->get( $url );
		if ($json && !ar_error::isError($json) ) {	
			return json_decode( $json );
		} else {
			return $json;
		}
	}
	
	public function tweet( $status, $options = array() ) {
		$url = ar::url( $this->rootURL.'statuses/update.json' );
		$options['status'] = $status;
		$json = $this->client->post( $url, $options );
		if ($json && !ar_error::isError($json) ) {
			return json_decode( $json );
		} else {
			return $json;
		}
	}
	
}
	
?>