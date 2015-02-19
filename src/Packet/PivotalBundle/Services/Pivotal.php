<?php

namespace Packet\PivotalBundle\Services;

use Packet\PivotalBundle\Entities\User;
use Packet\PivotalBundle\Entities\Project;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\BadResponseException;

class Pivotal extends Guzzle {

  private $endpoint;
  private $token;
  private $account;

  public function __construct($endpoint, $token, $account) {
    $this->endpoint = $endpoint;
    $this->token = $token;
    $this->account = $account;
    parent::__construct(['base_url' => $this->endpoint, 'defaults' => ['headers' => ['Content-Accept' => 'application/json', 'X-TrackerToken' => $this->token]]]);
  }

  public function getProject($id, $offset = null) {
    try {
			$response = $this->get('/services/v5/projects/'. (int) $id);
			$data = json_decode($response->getBody());
			return new Project($data, $this, $offset);
    }
    catch (BadResponseException $e) {
      throw $e;
    }
  }

	public function getProjects() {
		$projects = array();

		$memcache = new \Memcache;
		if($memcache->connect('memcached', 11211)) {
			if(!$projects = json_decode($memcache->get($this->account .'projects'))) {
				$memcache->delete($this->account .'projects');
				$projects = array();
			}
		}

		if(empty($projects)) {
			$response = $this->get('/services/v5/projects');
			$data = json_decode($response->getBody());
			foreach ($data as $key => $project) {
				$projects[$project->id] = $project;
			}
			$memcache->set($this->account .'projects', json_encode($projects), false, 0);
		}

		return $projects;
	}

	public function getTeam() {
		$accounts = $this->getAccounts();
		$users = array();
		foreach($accounts as $account) {
			$user = new User($account, $this);
			if(count($user->getProjects()) > 0) {
				$users[$user->getId()] = $user;
			}
		}
		return $users;
	}

	public function getUser($initials) {
			$accounts = $this->getAccounts();

			$user = null;
			foreach($accounts as $account) {
				if($account->initials == $initials) {
					$user = new User($account, $this);
					continue;
				}
			}
			return $user;
	}

	public function getAccounts() {
		$accounts = array();

		$memcache = new \Memcache;
		if($memcache->connect('memcached', 11211)) {
			if(!$accounts = json_decode($memcache->get($this->account .'_accounts'))) {
				$memcache->delete($this->account .'_accounts');
				$accounts = array();
			}
		}

		if(empty($accounts)) {
			$response = $this->get('/services/v5/accounts/'. $this->account .'/memberships');
			$data = json_decode($response->getBody());
			foreach($data as $account) {
				$accounts[$account->id] = $account->person;
			}
			$memcache->set($this->account .'_accounts', json_encode($accounts), false, 0);
		}

		return $accounts;
	}

	public static function purge() {
		if($memcache->connect('memcached', 11211)) {
			$memcache->delete($this->account .'_accounts');
			$memcache->delete($this->account .'_projects');
		}
	}
}
