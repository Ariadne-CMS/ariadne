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
	
	public function login( $consumerKey = '', $consumerSecret = '', $callback = '', $redirect = true ) {
		$session = ar_loader::session();
		if ( !$session->id() ) {
			$session->start();
		}
		
		if ( isset($callback) && substr($callback, 0, 4)!='http' && $callback!='oob' ) {
			$callback = ar_loader::makeURL().$callback;
		}
		
		if ( !$this->client instanceof ar_connect_oauthConsumer ) {
			// FIXME: what if you want a caching client?
			$this->client = ar_connect_oauth::client( $consumerKey, $consumerSecret );
			$this->client->enableDebug();
		}
		
		$access_token        = $session->getvar('access_token'); 
		$access_token_secret = $session->getvar('access_token_secret');
		if ( $access_token && $access_token_secret ) {
			$this->client->setToken( $access_token, $access_token_secret );
			return true;
		}
		
		$oauth_verifier     = $session->getvar('oauth_verifier');
		$oauth_token        = $session->getvar('oauth_token');
		$oauth_token_secret = $session->getvar('oauth_token_secret');
		if ( !$oauth_verifier ) {
			$oauth_token_arg = ar::getvar('oauth_token');
			$oauth_verifier  = ar::getvar('oauth_verifier');
			if ( $oauth_verifier ) {
				$session->putvar( 'oauth_verifier', $oauth_verifier );
			} else {
				if ( !$callback ) {
					$callback = 'oob';
				}
				$info = $this->client->getRequestToken( 'http://api.twitter.com/oauth/request_token', (string) $callback );
				if ( ar_error::isError($info) ) {
					$info->debugInfo = $this->client->debugInfo;
					return $info;
				}
				$this->client->setToken( $info['oauth_token'], $info['oauth_token_secret'] );
				$session->putvar( 'oauth_token', $info['oauth_token'] );
				$session->putvar( 'oauth_token_secret', $info['oauth_token_secret'] );
				if ($redirect) {
					ar_loader::redirect( 'http://api.twitter.com/oauth/authorize?oauth_token='.RawUrlEncode( $info['oauth_token'] ) );
					return false;
				} else {
					return ar::url( 'http://api.twitter.com/oauth/authorize?oauth_token='.RawUrlEncode( $info['oauth_token'] ) );
				}
			}
		}

		if ( $oauth_verifier ) {
			$this->client->setToken( $oauth_token, $oauth_token_secret );
			$info = $this->client->getAccessToken( 'http://api.twitter.com/oauth/access_token', '', $oauth_verifier );
			if ( ar_error::isError( $info ) ) {
				$info->debugInfo = $this->client->debugInfo;
				return $info;
			}
			echo '<hr><pre>';
			var_dump($info);
			echo '</pre><hr>';
			$access_token = $info['oauth_token'];
			$access_token_secret = $info['oauth_token_secret'];
			$this->client->setToken( $access_token, $access_token_secret );	
			$session->putvar( 'access_token', $access_token );
			$session->putvar( 'access_token_secret', $access_token_secret );
			return $info;
		}
		
		return false;		
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