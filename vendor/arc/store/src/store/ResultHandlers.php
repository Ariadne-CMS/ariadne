<?php
    namespace arc\store;

    class ResultHandlers {

        public static function getDBHandler($db)
        {
            return function($query, $args) use ($db) {
                $q = $db->prepare('select * from nodes where '.$query);
                $result = $q->execute($args);
                $dataset = [];
                while ( $data = $q->fetch(\PDO::FETCH_ASSOC) ) {
                    $value = (object) $data;
                    $value->data = json_decode($value->data);
                    $value->ctime = strtotime($value->ctime);
                    $value->mtime = strtotime($value->mtime);
                    $path = $value->parent.$value->name.'/';
                    $dataset[$path] = $value;
                }
                return $dataset;
            };
        }

        public static function getDBGeneratorHandler($db)
        {
            return function($query, $args) use ($db) {
                $q = $db->prepare('select * from nodes where '.$query);
                $result = $q->execute($args);
                $data = $q->fetch(\PDO::FETCH_ASSOC);
                if (!$data) {
                    return $data;
                }
                while ($data) {
                    $value = (object) $data;
                    $value->data = json_decode($value->data);
                    $value->ctime = strtotime($value->ctime);
                    $value->mtime = strtotime($value->mtime);
                    $path = $value->path;
                    yield $path => $value;
                    $data = $q->fetch(\PDO::FETCH_ASSOC);
                }
            };
        }
    }