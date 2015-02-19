<?php

namespace Packet\PivotalBundle\Entities;


class Project {
	private $id;
	private $guzz;
	private $scope;
	private $offset;
	private $iteration;
	private $epics = array();
	private $labels = array();

	public function __construct($project, $guzz, $offset = null) {
		foreach($project as $key => $value) {
			$this->$key = $value;
		}

		$this->guzz = $guzz;
		$this->setScope('current');
		if($offset) {
			$this->setOffset($offset);
			$this->setScope('done');
		}
	}

	public function getId() {
		return $this->id;
	}

  public function getTasks() {
		$tasks = 0;
		foreach($this->iteration->stories as $story) {
			if($story->story_type == 'bug' || $story->story_type == 'chore') {
				$tasks++;
			}
		}
		return $tasks;
	}

  public function getPoints() {
		$points = 0;
		foreach($this->iteration->stories as $story) {
			if($story->story_type == 'feature' && isset($story->estimate)) {
				$points += $story->estimate;
			}
		}
		return $points;
	}

  public function getPointsDelivered() {
		$points = 0;
		foreach($this->iteration->stories as $story) {
			if($story->story_type == 'feature' && isset($story->estimate) && ($story->current_state == 'accepted' || $story->current_state == 'delivered')) {
				$points += $story->estimate;
			}
		}
		return $points;
	}

  public function getStoriesDelivered() {
		$points = 0;
		foreach($this->iteration->stories as $story) {
			if(($story->story_type == 'feature' || $story->story_type == 'chore' || $story->story_type == 'bug') && ($story->current_state == 'accepted' || $story->current_state == 'delivered')) {
				$points++;
			}
		}
		return $points;
	}

	public function setIteration($iteration)
	{
		$this->iteration = $iteration;
	}

	public function setScope($scope) {
		$scopes = array('done', 'current', 'backlog', 'current_backlog');
		if(in_array($scope, $scopes)) {
			$this->scope = $scope;
		}
	}

	public function setOffset($offset) {
		if($offset >= 0 && $this->current_iteration_number >= $offset) {
			$this->offset = (int) $offset;
		}
	}

	public function getOffset() {
		return $this->offset;
	}

	public function getIteration()
	{
		if(empty($this->iteration)) {
			$vars['scope'] = $this->scope;
			
			if($vars['scope'] == 'done' && $this->offset) {
				$vars['offset'] = $this->offset*-1;
			}

			$response = $this->guzz->get('/services/v5/projects/'. $this->id .'/iterations/', [
				'query' => $vars
			]);
			$iterations = json_decode($response->getBody());

			$this->iteration = array_shift($iterations);
		}

		// we do this every time because the stories may be modified after loading to filter by user
		$stories = array();
		$accounts = $this->guzz->getAccounts();

		if(count($this->iteration->stories)) {
			foreach($this->iteration->stories as $story) {
				$story->additional_owners = array();
				$story->owner = '(none)';
				foreach($story->owner_ids as $id) {
					if($id == $story->owned_by_id) {
						if(isset($accounts->$id)) {
							$story->owner = $accounts->$id->name;
						}
						else {
							$story->owner = '(missing)';
						}
					}
					else {
						if(isset($accounts->$id)) {
							$story->additional_owners[$id] = $accounts->$id->name;
						}
						else {
							$story->additional_owners[$id] = '(???)';
						}
					}
				}

				if(isset($accounts->{$story->requested_by_id})) {
					$story->requester = $accounts->{$story->requested_by_id}->name;
				}
				else {
					$story->requester = '(unknown, #'.$story->requested_by_id .')';
				}
				$stories[$story->id] = $story;
			}
			$this->iteration->stories = $stories;
		}

		return $this->iteration;
	}

	public function getLabels()
	{
		if(empty($this->labels)) {
			$response = $this->guzz->get('/services/v5/projects/'. $this->id .'/labels');
			$data = json_decode($response->getBody());
			foreach($data as $label) {
				$this->labels[$label->id] = $label;
			}
		}
		return $this->labels;
	}

	public function getEpics()
	{
		if(empty($this->epics)) {
			$response = $this->guzz->get('/services/v5/projects/'. $this->id .'/epics');
			$data = json_decode($response->getBody());
			foreach($data as $epics) {
				$this->epics[$epics->id] = $epics;
			}
		}
		return $this->epics;
	}
}
