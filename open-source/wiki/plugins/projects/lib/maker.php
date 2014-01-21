<?php

require_once(dirname(__FILE__).'/plugins.php');
require_once(dirname(__FILE__).'/tools.php');
require_once(dirname(__FILE__).'/project_file.php');

class MakeNode {
	protected $name = NULL;
	// points the nodes that depend on this node
	private $links = array();
	// all nodes that will be affected if this node is updated
	private $affect = array();
	public $valid = true;
	public $ref = 0;
	public $unconditional = false;
	private $is_target = NULL;

	public function name() { return $this->name; }
	public function affect() { return $this->affect; }
	public function is_target() { return $this->is_target; }

	public function link($node) {
		if (!in_array($node->name(), array_keys($this->links)))
			$this->links[$node->name()] = $node; 
	}

	public function __construct($name, $is_target) {
		$this->name = $name;
		$this->is_target = $is_target;
	}
	
	// returns an array of loops
	public function find_affected(&$errors, $path = array()) {
		// if already found
		if ($this->loops != NULL || $this->affect != NULL) return;
		// check for loops
		if (in_array($this->name, $path)) {
			$loop = array($this->name);
			$next = array_pop($path);
			while ($next != $name) {
				$loop[] = $next;
				$next = array_pop($path);
			}
			add_error($errors, $this->name, array('loop' => $loop));
			return;
		}
		$loops = array();
		$affect = array();
		$path[] = $this->name;
		foreach ($this->links as $link) {
			$link->find_affected($errors, $path);
			$new_affect = $link->affect();
			if ($new_affect) $affect = array_merge($affect, $new_affect);
			$affect[] = $link->name();
		}
		if ($affect) 
			$this->affect = array_keys(array_flip($affect));
		else $this->affect = array();
	}
	
	public function mark_invalid(&$errors) {
		if (!$this->valid) return;
		$this->valid = false;
		foreach ($this->links as $link) {
			add_error($errors, $link->name(), 
				array('dependency' => $this->name));
			if ($link->valid) $link->mark_invalid($errors);
		}
	}
	
	public function mark_unconditional() {
		$this->unconditional = true;
		if ($this->is_target) return;
		foreach ($this->links as $link) $link->mark_unconditional();
	}
	
	public function ref() {
		if ($this->ref == 0) 
			foreach ($this->links as $link) $link->ref();
		$this->ref++;
	}

	public function unref(&$node_refs = array()) {
		foreach ($this->links as $link) {
			$link->ref--;
			if ($node_refs && isset($node_refs[$link->name()]))
				$node_refs[$link->name()] = $link->ref;
		}
		if ($node_refs)
			asort($node_refs);
	}
}

class Maker {
	private $nodes = NULL;
	private $project;
	private $rules = NULL;
	private $errors = array();
	private $files = NULL;
	private $parallel = false;
	private $intermediate_files = array();
	private $plugins = NULL;

	public function __construct($project) {
		$this->project = $project;
		$this->plugins = new Plugins(PROJECTS_PLUGINS_TARGET_DIR);
		$this->construct($project->files());
		$this->parallel = function_exists('pcntl_fork');
	}
	
	public function errors() { return $this->errors; }

	public function default_rule($file) {
		$handlers = $this->plugins->handlers($this->project, $file);
		if (!$handlers) return NULL;
		reset($handlers);
		$handler = current($handlers);
		$default = $handler->handle($this->project, $file);
		return $default;
	}

	// construct a dependency tree
	private function construct(&$files) {
		// the root node
		$this->nodes = array();
		$this->intermediate_files = array();
		$this->errors = array();
		foreach ($files as $name => $file)
			$this->files[$name] = &$file;
		$this->files = $files;
		$filenames = array_keys($files);
		$no_rules = array();
		foreach ($this->files as $name => $file) {
			if (!$file->makable()) {
				$default = $this->default_rule($file);
//					wiki_debug("default $name", $default);
				if ($default === NULL)
					$this->add_error($name, "undefined");
				else {
					$file = $default;
					$this->files[$name] =  $file;
				}
			}
			$this->nodes[$name] = new MakeNode($name, $file->is_target()); 
		}
		// link nodes reversly with dependency, i.e, if one node changed, 
		// which other nodes need to be upated
		while ((list($name, $node) = each($this->nodes)) != false) {	
			$deps = $this->files[$name]->dependency();
			if ($deps) foreach ($deps as $dep) {
				if (!isset($this->nodes[$dep])) {
					$missing = ProjectFile::create($this->project, 
						new TargetDefinition(array(
						'name' => $dep, 'type' => TARGET)));
					$default = $this->default_rule($missing);
//					wiki_debug("default $dep", $default);
					if ($default == NULL) {
						$this->add_error($name, array('dependency' => $dep));
						continue;
					}
					$this->intermediate_files[] = $dep;
					$this->files[$dep] = $default;
					$dep_node = new MakeNode($dep, true);
					$this->nodes[$dep] = $dep_node;
				}
				else $dep_node = $this->nodes[$dep];
				$dep_node->link($node);
			}
		}
		foreach ($this->nodes as $node) $node->find_affected($this->errors);
		// find those which are affected by the error nodes
		$errors = array_intersect(array_keys($this->errors), 
			array_keys($this->nodes));
		foreach ($errors as $name)
			$this->nodes[$name]->mark_invalid($this->errors);
		$invalid = array_keys($this->errors);
		// drop the invalid nodes
		$nodes = array();
		// relink valid nodes
		foreach ($this->nodes as $name => $node)
			if ($node->valid) 
				$nodes[$name] = new MakeNode($name, $node->is_target());
		foreach ($nodes as $name => $node) {
			$deps = array_diff($this->files[$name]->dependency(), $invalid);
			foreach ($deps as $dep) $nodes[$dep]->link($node);
		}
		$this->nodes = $nodes;
	}
	
	public function update($changes) {
		$changes = array_merge($changes, $this->intermediate_files);
		$changes = array_keys(array_flip($changes));
		if (!$changes) return array();
		$root = new MakeNode(NULL, false);
		foreach ($changes as $key => $change) {
			$node = $this->nodes[$change];
			if ($node) {
				$root->link($node);
				$node->mark_unconditional();
			}
			else unset($changes[$key]);
		}
		$root->ref();
		$nodes = array();
		foreach ($this->nodes as $name => $node)
			if ($node->ref > 0) 
				$nodes[$name] = $node->ref;
			else $node->ref = -1;
		if (!$nodes) return array();
		$root->unref($nodes);
		asort($nodes);
        $this->make($nodes);
		// remove stale files
		foreach ($this->errors as $name => $error)
			if (isset($this->files[$name])) {
				$file = $this->files[$name];
				if ($file->is_target()) 
					$file->delete($this->project->path());
			}
		return array_keys($nodes);
	}
	
	// make the files with ref >=0 one by one
	private function make($nodes) {
		$this->pid = array();
		$this->running = array();
		while ($nodes) {
			reset($nodes);
			$name = key($nodes);
			$ref = current($nodes);
			$node = $this->nodes[$name];
			if (!$node->valid || $node->ref < 0) {
				unset($nodes[$name]);
				continue;
			}
			if ($ref === 0) {
				unset($nodes[$name]);
				// if all dependency are up to date, do not need to
				// make unless it is requested
				$file = $this->files[$name];
				if (!$node->unconditional) {
					$deps = $file->dependency();
					$drop = true;
					foreach ($deps as $dep) 
						if ($this->nodes[$dep]->ref !== -1 ) {
							$drop = false;
							break;
						}
					if ($drop) {
						$node->ref = -1;
						$node->unref($nodes);
						continue;
					}
				}
				// drop source file
				if (!$file->is_target()) {
					$node->unref($nodes);
					continue;
				}
				$this->pid[$name] = $this->make_target($file);
				if (!$this->parallel || count($this->pid) == MAX_PARALLEL_JOBS) 
					$this->wait($nodes);
			}
			else {
				if (count($this->pid) > 0) 
					$this->wait($nodes);
				else {
					wiki_debug('error', "maker not counting correctly");
					wiki_debug('nodes', $nodes);
//					wiki_debug('nodes', $this->nodes);
					break;
				}
			}
		}			
	}

	private function wait(&$nodes) {
		if ($this->parallel)
			$pid = pcntl_wait($result); 
		else {
			$result = current($this->pid);
			$pid = $result;
		}
		$name = array_search($pid, $this->pid);
		unset($this->pid[$name]);
		$time = $this->times[$name];
		unset($this->times[$name]);
		$node = $this->nodes[$name];
		$node->unref($nodes);
		// check for error
		if ($result != 0) {
			$this->add_error($name, array("failure" => $result));
			$node->mark_invalid($this->errors);
			return;
		}
		// success, check if the node is not really updated
		if ($time != NULL && 
			$time == $this->files[$name]->time($this->project->path()))
			$node->ref = -1;
	}
	
	private function make_target($file) {
		$name = $file->name();
		$this->times[$name] = $file->time($this->project->path());
		// if all the dependent files are up-to-date, no need to make unless
		// requested for unconditional remake.
		if ($this->parallel) {
			$pid = pcntl_fork();
			// check if cannot fork
			if ($pid == -1) $this->parallel = false;
		}
		if ($this->parallel && $pid > 0) return;
		$result = $file->make($this->project->path());
		if ($this->parallel) exit($result);
		return $result;
	}
	
	private function add_error($name, $error) {
		add_error($this->errors, $name, $error);
	}
	
	public function dependency_order() {
		$order = array();
		$errors = array();
		foreach ($this->nodes as $node)
			$order = array_merge($order, $node->affect($errors));
		return array_keys(array_flip($order));
	}
}

function add_error(&$errors, $name, $error) {
	if (!isset($errors[$name]))
		$errors[$name] = array($error);
	else 
		$errors[$name][] = $error;
}

?>