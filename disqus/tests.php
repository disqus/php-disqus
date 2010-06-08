<?php

date_default_timezone_set('America/Los_Angeles');

require_once('PHPUnit/Framework.php');
require_once('disqus.php');

define('USER_API_KEY', $_SERVER['argv'][count($_SERVER['argv'])-1]);

if (strlen(USER_API_KEY) != 64) {
	die('Syntax: phpunit tests.php <user_api_key>');
}

class DisqusAPITest extends PHPUnit_Framework_TestCase {
	public function test_get_user_name() {
		$dsq = new DisqusAPI();
		$response = $dsq->get_user_name(USER_API_KEY);
		
		$this->assertTrue(!($response < 0));
	}

	public function test_get_forum_list() {
		$dsq = new DisqusAPI();
		$response = $dsq->get_forum_list(USER_API_KEY);
		
		$this->assertTrue(!($response < 0));
	}
	
	/**
	 * @depends test_get_forum_list
	 */
	public function test_get_forum_api_key() {
		$dsq = new DisqusAPI();

		$response = $dsq->get_forum_list(USER_API_KEY);
		$forum_id = $response[0]->id;
		
		$response = $dsq->get_forum_api_key(USER_API_KEY, $forum_id);
		
		$this->assertTrue(!($response < 0));
	}
	
	/**
	 * @depends test_get_forum_list
	 */
	public function test_get_forum_posts() {
		$dsq = new DisqusAPI();

		$response = $dsq->get_forum_list(USER_API_KEY);
		$this->assertTrue(!($response < 0));

		$forum_id = $response[0]->id;
		
		$response = $dsq->get_forum_posts(USER_API_KEY, $forum_id);
		$this->assertTrue(!($response < 0));
	}
	
	/**
	 * @depends test_get_forum_posts
	 */
	public function test_get_num_posts() {
		$dsq = new DisqusAPI();

		$response = $dsq->get_forum_list(USER_API_KEY);
		$this->assertTrue(!($response < 0));

		$forum_id = $response[0]->id;
		
		$response = $dsq->get_forum_posts(USER_API_KEY, $forum_id);
		$this->assertTrue(!($response < 0));

		$thread_id = $response[0]->thread->id;

		$response = $dsq->get_num_posts(USER_API_KEY, array($thread_id));
		$this->assertTrue(!($response < 0));
	}
	
	/**
	 * @depends test_get_forum_list
	 */
	public function test_get_categories_list() {
		$dsq = new DisqusAPI();

		$response = $dsq->get_forum_list(USER_API_KEY);
		$this->assertTrue(!($response < 0));

		$forum_id = $response[0]->id;
		
		$response = $dsq->get_categories_list(USER_API_KEY, $forum_id);
		$this->assertTrue(!($response < 0));
	}
	
	/**
	 * @depends test_get_forum_list
	 */
	public function test_get_thread_list() {
		$dsq = new DisqusAPI();

		$response = $dsq->get_forum_list(USER_API_KEY);
		$this->assertTrue(!($response < 0));

		$forum_id = $response[0]->id;
		
		$response = $dsq->get_thread_list(USER_API_KEY, $forum_id);
		$this->assertTrue(!($response < 0));
	}
	
	/**
	 * @depends test_get_forum_list
	 */
	public function test_get_updated_threads() {
		$dsq = new DisqusAPI();

		$response = $dsq->get_forum_list(USER_API_KEY);
		$this->assertTrue(!($response < 0));

		$forum_id = $response[0]->id;
		
		$response = $dsq->get_updated_threads(USER_API_KEY, $forum_id, time());
		$this->assertTrue(!($response < 0));
	}
	
	/**
	 * @depends test_get_forum_posts
	 */
	public function test_get_thread_posts() {
		$dsq = new DisqusAPI();

		$response = $dsq->get_forum_list(USER_API_KEY);
		$this->assertTrue(!($response < 0));

		$forum_id = $response[0]->id;
		
		$response = $dsq->get_forum_posts(USER_API_KEY, $forum_id);
		$this->assertTrue(!($response < 0));

		$thread_id = $response[0]->thread->id;

		$response = $dsq->get_thread_posts(USER_API_KEY, $thread_id);
		$this->assertTrue(!($response < 0));
	}
}

?>