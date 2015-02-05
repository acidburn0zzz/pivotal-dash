<?php

namespace Packet\PivotalBundle\Entities;

class User {
	private $id;
	private $guzz;
	private $projects = array();

	public function __construct($account, $guzz) {
		foreach($account as $key => $value) {
			$this->$key = $value;
		}

		$this->guzz = $guzz;
	}

	public function getId() {
		return $this->id;
	}

	public function getProjects() {
		if(empty($this->projects)) {
			$projects = $this->guzz->getProjects();
			foreach($projects as $project) {
				$response = $this->guzz->get('/services/v5/projects/'. $project->id .'/stories', [
					'query' => ['filter' => 'owner:'. $this->initials]
				]);
				$data = json_decode($response->getBody());
				if($data && !empty($data)) {
					$this->projects[$project->id] = new Project($project, $this->guzz);
					$iteration = $this->projects[$project->id]->getIteration();

					$stories = array();
					foreach($data as $story) {
						if(isset($iteration->stories[$story->id])) {
							$stories[$story->id] = $story;
						}
					}
					$iteration->stories = $stories;

					$this->projects[$project->id]->setIteration($iteration);
				}
			}
		}

		return $this->projects;
	}

	public function getPoints() {
		$points = 0;
		foreach($this->getProjects() as $project) {
			$points += $project->getPoints();
		} 
		return $points;
	}

	public function getTasks() {
		$tasks = 0;
		foreach($this->getProjects() as $project) {
			$tasks += $project->getTasks();
		} 
		return $tasks;
	}

}
