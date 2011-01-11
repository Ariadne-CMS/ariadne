<?php

ar_pinp::allow('ar_connect_twitter');
ar_pinp::allow('ar_connect_twitterClient');

class ar_connect_twitter extends arBase {

	public static function client( $httpClient = null ) {
		return new ar_connect_twitterClient( $httpClient );
	}
	
	public static function parse( $text, $parseTwitterLinks = true ) {
		// FIXME: allow normal links and mailto links to be specified like the user and argument links
		// link URLs
		$text = " ".preg_replace( "/(([[:alnum:]]+:\/\/)|www\.)([^[:space:]]*)".
			"([[:alnum:]#?\/&=])/i", "<a href=\"\\1\\3\\4\" target=\"_blank\">".
			"\\1\\3\\4</a>", $text);

		// link mailtos
		$text = preg_replace( "/(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)".
			"([[:alnum:]-]))/i", "<a href=\"mailto:\\1\">\\1</a>", $text);

		if ( $parseTwitterLinks ) {
			if ( is_array($parseTwitterLinks) ) {
				$userlink = $parseTwitterLinks['user'];
				$argumentLink = $parseTwitterLinks['argument'];
			} else {
				$userLink = true;
				$argumentLink = true;
			}
			if ( is_bool($userLink) && $userLink ) {
				$userLink = '<a href="http://twitter.com/{user}" target="_blank">@{user}</a>';
			}
			if ( is_bool($argumentLink) && $argumentLink ) {
				$argumentLink = '<a href="http://twitter.com/search?q=%23{argument}" target="_blank">#{argument}</a>';
			}
			if ($userLink) {
				//link twitter users
				$text = preg_replace_callback( '/([\b ])@([a-z0-9_]*)\b/i', 
					create_function( 
						'$matches', 
						'return $matches[1].str_replace( "{user}", $matches[2], "'.AddCSlashes( (string) $userLink, '"' ).'" );'
					),
					$text
				);
			}
			if ($argumentLink) {
				//link twitter arguments
				$text = preg_replace_callback( '/([\b ])#([a-z0-9_]*)\b/i',
					create_function( 
						'$matches', 
						'return $matches[1].str_replace( "{argument}", $matches[2], "'.AddCSlashes( (string) $argumentLink, '"' ).'" );'
					),
					$text
				);
			}
		}
		
		return trim($text);
	}
	
	public static function friendlyDate( $date, $nls = null, $now = null ) {
		if (!$nls) {
			$nls = array(
				'lastyear'   => 'last year',
				'yearsago'   => '%d years ago',
				'lastmonth'  => 'last month',
				'monthsago'  => '%d months ago',
				'lastweek'   => 'last week',
				'weeksago'   => '%d weeks ago',
				'yesterday'  => 'yesterday',
				'daysago'    => '%d days ago',
				'hourago'    => '1 hour ago',
				'hoursago'   => '%d hours ago',
				'minuteago'  => '1 minute ago',
				'minutesago' => '%d minutes ago',
				'justnow'    => 'just now'
			);
		}
				
		if ( !isset($now) ) {
			$now = time();
		}
		if ( is_string($date) ) {
			$date = strtotime($date, $now);
		}
		if ( is_string( $now ) ) {
			$now = strtotime( $now );
		}
		if ( is_int( $date ) && is_int( $now ) ) {
			$interval = getdate($now - $date);
			
			if ( $interval['year'] > 1971 ) {
				return sprintf( $nls['yearsago'], ( $interval['year'] - 1970 ) );
			} else if ( ($interval['year'] > 1970 ) || ( $interval['mon'] > 11 ) ) {
				return $nls['lastyear'];
			} else if ( $interval['mon'] > 2 ) {
				return sprintf( $nls['monthsago'], $interval['mon'] );
			} else if ( $interval['mon'] > 1 ) {
				return $nls['lastmonth'];
			} else if ( $interval['mday'] > 2 ) {
				return sprintf( $nls['daysago'], $interval['mday'] );
			} else if ( $interval['mday'] > 1 ) {
				return $nls['yesterday'];
			} else if ( $interval['hours'] > 2 ) {
				return sprintf( $nls['hoursago'], $interval['hours'] );
			} else if ( $interval['hours'] > 1 ) {
				return $nls['hourago'];
			} else if ( $interval['minutes'] > 1 ) {
				return sprintf( $nls['minutesago'], $interval['minutes'] );
			} else if ( $interval['minutes'] > 0 ) {
				return $nls['minuteago'];
			} else {
				return $nls['justnow'];
			}
		} else {
			return ar_error::raiseError( 'Illegal date argument', ar_exceptions::ILLEGAL_ARGUMENT );
		}
	}
}

class ar_connect_twitterClient extends arBase {

	private $rootURL = 'http://api.twitter.com/1/';
	private $searchURL = 'http://search.twitter.com/search.json';
	private $client = null;
	
	public function __construct( $httpClient = null ) {
		if (!$httpClient) {
			ar::load('http');
			$httpClient = new ar_httpClientStream();
		}
		$this->client = $httpClient;
	}

	public function parse( $text, $parseTwitterLinks=true ) {
		return ar_connect_twitter::parse( $text, $parseTwitterLinks );
	}
	
	public function friendlyDate( $date, $nls = null, $now = null ) {
		return ar_connect_twitter::friendlyDate( $date, $nls, $now );
	}
	
	public function setAccessToken( $access_token, $access_token_secret, $consumerKey = null, $consumerSecret = null ) {
	
		if ( !$this->client instanceof ar_connect_oauthClient ) { //FIXME: a real OAuth is also ok
			// FIXME: what if you want a caching client?
			$this->client = ar_connect_oauth::client( $consumerKey, $consumerSecret );
		}
		
		return $this->client->setToken( $access_token, $access_token_secret );
	}
	
	public function login( $consumerKey = null, $consumerSecret = null, $callback = '', $redirect = true ) {
		// FIXME: $redirect should probably be allowed to be an object that implements a redirect() method
		$session = ar_loader::session(); //FIXME: allow different session object to be passed
		if ( !$session->id() ) {
			$session->start();
		}
		
		if ( isset($callback) && substr( (string) $callback, 0, 4)!='http' && $callback!='oob' ) {  
			$callback = ar_loader::makeURL().$callback;
		}
		
		if ( !$this->client instanceof ar_connect_oauthClient ) { ////FIXME: a real OAuth is also ok
			// FIXME: what if you want a caching client?
			$this->client = ar_connect_oauth::client( $consumerKey, $consumerSecret );
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
					return 'http://api.twitter.com/oauth/authorize?oauth_token='.RawUrlEncode( $info['oauth_token'] );
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
			$access_token = $info['oauth_token'];
			$access_token_secret = $info['oauth_token_secret'];
			$this->client->setToken( $access_token, $access_token_secret );	
			$session->putvar( 'access_token', $access_token );
			$session->putvar( 'access_token_secret', $access_token_secret );
			return $info;
		}
		
		return false;		
	}
	
	public function tweets( $user, $options = array() ) {
		// http://dev.twitter.com/doc/get/statuses/user_timeline
		$defaults = array(
			'count' => 10, 
			'page' => 1
		);
		$options += $defaults;
		if ( is_numeric($user) ) {
			$options['user_id'] = $user;
			unset($user);
		} else if ($user) {
			$options['screen_name'] = $user;
		}
		return $this->get( 'statuses/user_timeline', $options );
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
		return $this->get( 'trends/'.$timeslice, $options );
	}
	
	public function search( $options ) {
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
		$options['status'] = $status;
		return $this->post( 'statuses/update', $options );
	}
	
	public function get( $path, $options = array() ) {
		$url = ar::url( $this->rootURL.$path.'.json' );
		$url->query->import( $options );
		$json = $this->client->get( $url );
		if ($json && !ar_error::isError($json) ) {
			return json_decode( $json );
		} else {
			return $json;
		}
	}
	
	public function post( $path, $options = array() ) {
		$url = ar::url( $this->rootURL.$path.'.json' );
		$json = $this->client->post( $url, $options );
		if ($json && !ar_error::isError($json) ) {
			return json_decode( $json );
		} else {
			return $json;
		}
	}
	
}
	
?>