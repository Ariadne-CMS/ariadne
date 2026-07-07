<?php
namespace arc\store;

final class TreeStore implements Store {

    private $tree;
    private $queryParser;
    private $resultHandler;
    private $path;

    /**
     * MemStore constructor.
     * @param \arc\tree\Node $tree
     * @param callable $queryParser
     * @param callable $resultHandler
     * @param string $path
     */
    public function __construct($tree = null, $queryParser = null, $resultHandler = null, $path = '/')
    {
        $this->tree          = $tree;
        $this->queryParser   = $queryParser;
        $this->resultHandlerFactory = $resultHandler;
        $this->resultHandler = $resultHandler($this->tree);
        $this->path          = \arc\path::collapse($path);
    }

    /**
     * change the current path, returns a new store instance for that path
     * @param string $path
     * @return PSQLStore
     */
    public function cd($path)
    {
        return new self( $this->tree, $this->queryParser, $this->resultHandlerFactory, \arc\path::collapse($path, $this->path) );
    }

    /**
     * creates sql query for the search query and returns the resulthandler
     * @param string $query
     * @param string $path
     * @return mixed
     */
    public function find($query, $path='')
    {
        $path = \arc\path::collapse($path, $this->path);
        $root = $this->tree->cd($path);
        $f    = $this->queryParser->parse($query, $path);
        return call_user_func($this->resultHandler, $f );
    }

    /**
     * get a single object from the store by path
     * @param string $path
     * @return mixed
     */
    public function get($path='')
    {
        $path   = \arc\path::collapse($path, $this->path);
        $result = $this->tree->cd($path);
        return $result;
    }

    /**
     * list all parents, including self, by path, starting from the root
     * @param string $path
     * @param string $top
     * @return mixed
     */
    public function parents($path='', $top='/')
    {
        $path   = \arc\path::collapse($path, $this->path);
        return ($this->resultHandler)(
            'strtolower($node->path)==strtolower(substring("'
            .$path.'",1,'.sizeof($path)
            .') && like(strtolower($node->path),strtolower("'.$top.'")'
        );
    }

    /**
     * list all child objects by path
     * @param string $path
     * @return mixed
     */
    public function ls($path='')
    {
        $path  = \arc\path::collapse($path, $this->path);
        return $this->tree->cd($path)->ls(function($node) {
            return json_decode(json_encode($node->nodeValue),false);
        });
    }

    /**
     * returns true if an object with the given path exists
     * @param string $path
     * @return bool
     */
    public function exists($path='')
    {
        $path   = \arc\path::collapse($path, $this->path);
        if ($path!='/') {
            $parent = ($path=='/' ? '' : \arc\path::parent($path));
            $name   = basename($path);
            return $this->tree->cd($parent)->childNodes->offsetExists($name);
        }
        return true;
    }

    /**
     * initialize the postgresql database, if it wasn't before
     * @return bool|mixed
     */
    public function initialize() {
        return $this->save(\arc\prototype::create([
            'name' => 'Root'
        ]),'/');
    }

    /**
     * save (insert or update) a single object on the given path
     * @param $data
     * @param string $path
     * @return mixed
     */
    public function save($data, $path='') {
        $path   = \arc\path::collapse($path, $this->path);
        $parent = ($path=='/' ? '' : \arc\path::parent($path));
        if ($path!='/' && !$this->exists($parent)) {
            throw new \arc\IllegalRequest("Parent $parent not found.", \arc\exceptions::OBJECT_NOT_FOUND);
        }
        $parentNode = $this->tree->cd($parent);
        $name = ($path=='/' ? '' : basename($path));
        if ($name) {
            $parentNode->appendChild($name, $data);
        } else {
            $parentNode->nodeValue = $data;
        }
        return true;
    }

    /**
     * remove the object with the given path and all its children
     * won't remove the root object ever
     * @param string $path
     * @return mixed
     */
    public function delete($path = '') {
        $path   = \arc\path::collapse($path, $this->path);
        $parent = \arc\path::parent($path);
        $name   = basename($path);
        $this->tree->cd($parent)->removeChild($name);
        return true;
    }


    public static function getResultHandler($tree)
    {
        return function($callback) use ($tree) {
            $dataset = \arc\tree::filter($tree, $callback);
            foreach($dataset as $path => $node) {
                $node = json_decode(json_encode($node),false);
                $dataset[$path] = $node;
            }
            return $dataset;
        };
    }
}
