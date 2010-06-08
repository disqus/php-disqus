<?php
/**
 * Implementation of the Disqus v1.1 API.
 *
 * http://groups.google.com/group/disqus-dev/web/api-1-1
 *
 * @author		Disqus <team@disqus.com>
 * @copyright	2007-2010 Big Head Labs
 * @link		http://disqus.com/
 * @package		Disqus
 * @version		1.1
 */

require_once('url.php');

/** @#+
 * Constants
 */
/**
 * Base URL for Disqus.
 */

define('DISQUS_TYPE_SPAM', 'spam');
define('DISQUS_TYPE_DELETED', 'killed');
define('DISQUS_TYPE_KILLED', DISQUS_TYPE_DELETED);
define('DISQUS_TYPE_NEW', 'new');

define('DISQUS_STATE_APPROVED', 'approved');
define('DISQUS_STATE_UNAPPROVED', 'unapproved');
define('DISQUS_STATE_SPAM', 'spam');
define('DISQUS_STATE_DELETED', 'killed');
define('DISQUS_STATE_KILLED', DISQUS_STATE_DELETED);

define('DISQUS_ACTION_SPAM', 'spam');
define('DISQUS_ACTION_APPROVE', 'approve');
define('DISQUS_ACTION_DELETE', 'delete');
define('DISQUS_ACTION_KILL', 'kill');

if (!extension_loaded('json')) {
	require_once('json.php');
	function dsq_json_decode($data) {
		$json = new JSON;
		return $json->unserialize($data);
	}
} else {
	function dsq_json_decode($data) {
		return json_decode($data);
	}	
}

/**
 * Helper methods for all of the Disqus 1.1 API methods.
 *
 * @package		Disqus
 * @author		DISQUS.com <team@disqus.com>
 * @copyright	2007-2010 Big Head Labs
 * @version		1.1
 */
class DisqusAPI {
	var $user_api_key;
	var $forum_api_key;
	var $api_url = 'http://www.disqus.com/api/';
	var $api_version = '1.1';

	/**
	 * Creates a new inerface to the Disqus API.
	 */
	function DisqusAPI($user_api_key, $forum_api_key, $api_url='http://www.disqus.com/api/') {
		$this->user_api_key = $user_api_key;
		$this->forum_api_key = $forum_api_key;
		$this->api_url = $api_url;
		$this->last_error = null;
	}
	
	function call($method, $function, $args=array()) {
		$url = $this->api_url . $function . '/';
		
		if (!isset($args['user_api_key'])) {
			$args['user_api_key'] = $this->user_api_key;
		}
		if (!isset($args['forum_api_key'])) {
			$args['forum_api_key'] = $this->forum_api_key;
		}
		if (!isset($args['api_version'])) {
			$args['api_version'] = $this->api_version;
		}
		
		foreach ($args as $key=>$value) {
			// XXX: Disqus is lacking some exception handling and we sometimes
			// end up with 500s when passing invalid values
			if (empty($value)) unset($args[$key]);
		}
		
		if (strtoupper($method) == 'GET') {
			$url .= '?' . dsq_get_query_string($args);
			$args = null;
		}
		
		$response = dsq_urlopen($url, $args);
		
		// XXX: We could add in exception handling if they are using PHP5
		if ($response['code'] != 200) {
			$this->last_error = $response['data']['message'];
			return false;
		}
		
		$data = dsq_json_decode($response['data']);
		
		if(!$data || !$data->succeeded) {
			$this->last_error = $response['data']['message'];;
			return false;
		}
		return $data->message;
	}
	
	function get_last_error() {
		if (!empty($this->last_error)) return;
		return $this->last_error;
	}

	function get_user_name() {
		return $this->call('POST', 'get_user_name');
	}
	
	function get_forum_list() {
		return $this->call('GET', 'get_forum_list');
	}

	function get_forum_api_key($forum_id) {
		$params = array(
			'forum_id'		=> $forum_id,
		);
		
		return $this->call('GET', 'get_forum_api_key', $params);
	}
	
	function get_forum_posts($forum_id, $category_id=null, $limit=null, $start=null, $filter=null, $exclude=null) {
		$params = array(
			'forum_id'		=> $forum_id,
			'category_id'	=> $category_id,
			'limit'			=> $limit,
			'start'			=> $start,
			'filter'		=> is_array($filter) ? implode(',', $filter) : $filter,
			'exclude'		=> is_array($exclude) ? implode(',', $exclude) : $exclude,
		);
		
		return $this->call('GET', 'get_forum_posts', $params);
	}
	
	function get_num_posts($thread_ids) {
		$params = array(
			'thread_ids'	=> is_array($thread_ids) ? implode(',', $thread_ids) : $thread_ids,
		);
		
		return $this->call('GET', 'get_num_posts', $params);
	}
	
	function get_categories_list($forum_id) {
		$params = array(
			'forum_id'		=> $forum_id,
		);
		
		return $this->call('GET', 'get_categories_list', $params);
	}
	
	function get_thread_list($forum_id, $limit=null, $start=null, $category_id=null) {
		$params = array(
			'forum_id'		=> $forum_id,
			'limit'			=> $limit,
			'start'			=> $start,
			'category_id'	=> $category_id,
		);
		
		return $this->call('GET', 'get_thread_list', $params);
	}

	function get_updated_threads($forum_id, $since) {
		$params = array(
			'forum_id'		=> $forum_id,
			'since'			=> is_string($since) ? $string : strftime('%Y-%m-%dT%H:%M', $since),
		);
		
		return $this->call('GET', 'get_updated_threads', $params);
	}
	
	function get_thread_posts($thread_id, $limit=null, $start=null, $filter=null, $exclude=null) {
		$params = array(
			'thread_id'		=> $thread_id,
			'limit'			=> $limit,
			'start'			=> $start,
			'filter'		=> is_array($filter) ? implode(',', $filter) : $filter,
			'exclude'		=> is_array($exclude) ? implode(',', $exclude) : $exclude,
		);
		
		return $this->call('GET', 'get_thread_posts', $params);
	}
	
	function thread_by_identifier($identifier, $title, $category_id=null, $create_on_fail=null) {
		$params = array(
			'identifier'	=> $identifier,
			'title'			=> $title,
			'category_id'	=> $category_id,
			'create_on_fail'=> $create_on_fail,
		);
		
		return $this->call('POST', 'thread_by_identifier', $params);
	}
	
	function get_thread_by_url($url, $partner_api_key=null) {
		$params = array(
			'url'			=> $url,
			'partner_api_key'	=> $partner_api_key,
		);
		
		return $this->call('GET', 'get_thread_by_url', $params);
	}
	
	function update_thread($thread_id, $title=null, $allow_comments=null, $slug=null, $url=null) {
		$params = array(
			'thread_id'		=> $thread_id,
			'title'			=> $title,
			'allow_comments'=> $allow_comments,
			'slug'			=> $slug,
			'url'			=> $url,
		);
		
		return $this->call('POST', 'update_thread', $params);
	}
	
	function create_post($thread_id, $message, $author_name, $author_email, $params = array()) {
		$params['thread_id'] = $thread_id;
		$params['message'] = $message;
		$params['author_name'] = $author_name;
		$params['author_email'] = $author_email;
		
		return $this->call('POST', 'create_post', $params);
	}
	
	function moderate_post($post_id, $action) {
		$params = array(
			'post_id'		=> $post_id,
			'action'		=> $action,
		);
		
		return $this->call('POST', 'moderate_post', $params);
	}
}

?>
