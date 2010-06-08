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
 * @subpackage	lib
 * @version		1.1
 */

require_once('url.php');

/** @#+
 * Constants
 */
/**
 * Base URL for Disqus.
 */

define('DISQUS_API_URL',		'http://www.disqus.com');
define('DISQUS_IMPORTER_URL',	'http://www.disqus.com');
define('ALLOWED_HTML', '<b><u><i><h1><h2><h3><code><blockquote><br><hr>');

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
	var $short_name;
	var $forum_api_key;
	var $api_version = '1.1';

	function DisqusAPI() {
		$this->last_error = null;
	}
	
	function query($method, $call, $args) {
		$args['api_version'] = $this->api_version;
		
		$url = DISQUS_API_URL . '/api/' . $call . '/';
		
		foreach ($args as $key=>$value) {
			if (empty($value)) unset($args[$key]);
		}
		
		if (strtoupper($method) == 'GET') {
			$url .= '?' . dsq_get_query_string($args);
			$args = null;
		}
		
		
		$response = dsq_urlopen($url, $args);
		
		if ($response['code'] != 200) {
			$this->last_error = $response['data']['message'];
			return -1;
		}
		
		$data = dsq_json_decode($response['data']);
		
		if(!$data || !$data->succeeded) {
			$this->last_error = $response['data']['message'];;
			if($data->code == 'bad-credentials' || $data->code == 'bad-key') {
				return -2;
			} else {
				return -1;
			}
		}
		return $data->message;
	}
	
	function get_last_error() {
		if (!empty($this->last_error)) return;
		return $this->last_error;
	}

	function get_user_name($user_api_key) {
		$params = array(
			'user_api_key'	=> $user_api_key,
		);
		
		$data = $this->query('POST', 'get_user_name', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function get_forum_list($user_api_key) {
		$params = array(
			'user_api_key'	=> $user_api_key,
		);
		
		$data = $this->query('GET', 'get_forum_list', $params);
		if ($data < 0) return false;
		return $data;
	}

	function get_forum_api_key($user_api_key, $forum_id) {
		$params = array(
			'user_api_key'	=> $user_api_key,
			'forum_id'		=> $forum_id,
		);
		
		$data = $this->query('GET', 'get_forum_api_key', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function get_forum_posts($user_api_key, $forum_id, $category_id=null, $limit=null, $start=null, $filter=null, $exclude=null) {
		$params = array(
			'user_api_key'	=> $user_api_key,
			'forum_id'		=> $forum_id,
			'category_id'	=> $category_id,
			'limit'			=> $limit,
			'start'			=> $start,
			'filter'		=> is_array($filter) ? implode(',', $filter) : $filter,
			'exclude'		=> is_array($exclude) ? implode(',', $exclude) : $exclude,
		);
		
		$data = $this->query('GET', 'get_forum_posts', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function get_num_posts($user_api_key, $thread_ids) {
		$params = array(
			'user_api_key'	=> $user_api_key,
			'thread_ids'	=> is_array($thread_ids) ? implode(',', $thread_ids) : $thread_ids,
		);
		
		$data = $this->query('GET', 'get_num_posts', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function get_categories_list($user_api_key, $forum_id) {
		$params = array(
			'user_api_key'	=> $user_api_key,
			'forum_id'		=> $forum_id,
		);
		
		$data = $this->query('GET', 'get_categories_list', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function get_thread_list($user_api_key, $forum_id, $limit=null, $start=null, $category_id=null) {
		$params = array(
			'user_api_key'	=> $user_api_key,
			'forum_id'		=> $forum_id,
			'limit'			=> $limit,
			'start'			=> $start,
			'category_id'	=> $category_id,
		);
		
		$data = $this->query('GET', 'get_thread_list', $params);
		if ($data < 0) return false;
		return $data;
	}

	function get_updated_threads($user_api_key, $forum_id, $since) {
		$params = array(
			'user_api_key'	=> $user_api_key,
			'forum_id'		=> $forum_id,
			'since'			=> is_string($since) ? $string : strftime('%Y-%m-%dT%H:%M', $since),
		);
		
		$data = $this->query('GET', 'get_updated_threads', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function get_thread_posts($user_api_key, $thread_id, $limit=null, $start=null, $filter=null, $exclude=null) {
		$params = array(
			'user_api_key'	=> $user_api_key,
			'thread_id'		=> $thread_id,
			'limit'			=> $limit,
			'start'			=> $start,
			'filter'		=> is_array($filter) ? implode(',', $filter) : $filter,
			'exclude'		=> is_array($exclude) ? implode(',', $exclude) : $exclude,
		);
		$data = $this->query('GET', 'get_thread_posts', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function thread_by_identifier($forum_api_key, $identifier, $title, $category_id=null, $create_on_fail=null) {
		$params = array(
			'forum_api_key'	=> $forum_api_key,
			'identifier'	=> $identifier,
			'title'			=> $title,
			'category_id'	=> $category_id,
			'create_on_fail'=> $create_on_fail,
		);
		
		$data = $this->query('POST', 'thread_by_identifier', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function get_thread_by_url($url, $forum_api_key=null, $partner_api_key=null) {
		$params = array(
			'url'			=> $url,
			'forum_api_key'	=> $forum_api_key,
			'partner_api_key'	=> $partner_api_key,
		);
		
		$data = $this->query('GET', 'get_thread_by_url', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function update_thread($forum_api_key, $thread_id, $title=null, $allow_comments=null, $slug=null, $url=null) {
		$params = array(
			'forum_api_key'	=> $forum_api_key,
			'thread_id'		=> $thread_id,
			'title'			=> $title,
			'allow_comments'=> $allow_comments,
			'slug'			=> $slug,
			'url'			=> $url,
		);
		
		$data = $this->query('POST', 'update_thread', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function create_post($forum_api_key, $thread_id, $message, $author_name, $author_email, $partner_api_key=null, $created_at=null, $ip_address=null, $author_url=null, $parent_post=null, $state=null) {
		$params = array(
			'forum_api_key'	=> $forum_api_key,
			'thread_id'		=> $thread_id,
			'message'		=> $message,
			'author_name'	=> $author_name,
			'author_email'	=> $author_email,
			'partner_api_key'	=> $partner_api_key,
			'created_at'	=> is_string($created_at) && !empty($created_at) ? strftime('%Y-%m-%dT%H:%M', $created_at) : null,
			'ip_address'	=> $ip_address,
			'author_url'	=> $author_url,
			'parent_post'	=> $parent_post,
			'state'			=> $state,
		);
		
		$data = $this->query('POST', 'create_post', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function moderate_post($user_api_key, $post_id, $action) {
		$params = array(
			'user_api_key'	=> $user_api_key,
			'post_id'		=> $post_id,
			'action'		=> $action,
		);
		
		$data = $this->query('POST', 'moderate_post', $params);
		if ($data < 0) return false;
		return $data;
	}
	
	function get_thread($post, $permalink, $title, $excerpt) {
		$title = strip_tags($title, ALLOWED_HTML);
		$title = urlencode($title);

		$excerpt = strip_tags($excerpt, ALLOWED_HTML);
		$excerpt = urlencode($excerpt);
		$excerpt = substr($excerpt, 0, 300);

		$thread_meta = $post->ID . ' ' . $post->guid;

		$response = @dsq_urlopen(
			DISQUS_API_URL . '/api/v2/get_thread/',
			array(
				'short_name'	=> $this->short_name,
				'thread_url'	=> $permalink,
				'thread_meta'	=> $thread_meta,
				'response_type'	=> 'php',
				'title'			=> $title,
				'message'		=> $excerpt,
				'api_key'		=> $this->forum_api_key,
				'source'		=> 'DsqWordPress20',
				'state_closed'	=> ($post->comment_status == 'closed') ? '1' : '0'
			)
		);

		$data = unserialize($response['data']);
		if(!$data || $data['stat'] == 'fail') {
			if($data['err']['code'] == 'bad-key') {
				return -2;
			} else {
				return -1;
			}
		}

		return $data;
	}

	function import_wordpress_comments($wxr) {
		$response = @dsq_urlopen(
			DISQUS_IMPORTER_URL . '/api/import-wordpress-comments/',
			array(
				'forum_url' => $this->short_name,
				'forum_api_key' => $this->forum_api_key,
				'response_type'	=> 'php',
				'wxr' => $wxr,
			)
		);
		
		$data = unserialize($response['body']);
		if (!$data || $data['stat'] == 'fail') {
			return -1;
		}
		return $data['import_id'];
	}

	function get_import_status($import_id) {
		$response = @dsq_urlopen(
			DISQUS_IMPORTER_URL . '/api/get-import-status/',
			array(
				'forum_url' => $this->short_name,
				'forum_api_key' => $this->forum_api_key,
				'import_id' => $import_id,
				'response_type'	=> 'php'
			)	
		);

		$data = unserialize($response['data']);
		if(!$data || $data['stat'] == 'fail') {
			return -1;
		}
		return $data;
	}
}

?>
