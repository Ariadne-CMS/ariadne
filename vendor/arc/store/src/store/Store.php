<?php
namespace arc\store;

interface Store {
    /**
     * change the current path, returns a new store instance for that path
     * @param string $path
     * @return Store
     */
    public function cd($path);

    /**
     * creates sql query for the search query and returns the resulthandler
     * @param string $query
     * @param string $path
     * @return mixed
     */
    public function find($query, $path='');

    /**
     * get a single object from the store by path
     * @param string $path
     * @return mixed
     */
    public function get($path='');

    /**
     * list all parents, including self, by path, starting from the root
     * @param string $path
     * @param string $top
     * @return mixed
     */
    public function parents($path='', $top='/');

    /**
     * list all child objects by path
     * @param string $path
     * @return mixed
     */
    public function ls($path='');

    /**
     * returns true if an object with the given path exists
     * @param string $path
     * @return bool
     */
    public function exists($path='');

    /**
     * initialize the store, if it wasn't before
     * @return bool|mixed
     */
    public function initialize();

    /**
     * save (insert or update) a single object on the given path
     * @param $data
     * @param string $path
     * @return mixed
     */
    public function save($data, $path='');

    /**
     * remove the object with the given path and all its children
     * won't remove the root object ever
     * @param string $path
     * @return mixed
     */
    public function delete($path = '');

}