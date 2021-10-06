<?php
    // TODO: support postgresql
    ar_pinp::allow('ar_tasks');

    class ar_tasks extends arBase {

        private static $connection;

        public static function connect($db, $user, $pass) {
            self::$connection = new PDO($db, $user, $pass);
            self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES,true);
            $query = "show tables like 'tasks'";
            $result = self::$connection->query($query);
            if (!$result || $result->rowCount() === 0) {
                self::init();
            }
            $polyfill1 = <<<EOF
CREATE FUNCTION IF NOT EXISTS BIN_TO_UUID(b BINARY(16))
RETURNS CHAR(36)
BEGIN
   DECLARE hexStr CHAR(32);
   SET hexStr = HEX(b);
   RETURN LOWER(CONCAT(
        SUBSTR(hexStr, 1, 8), '-',
        SUBSTR(hexStr, 9, 4), '-',
        SUBSTR(hexStr, 13, 4), '-',
        SUBSTR(hexStr, 17, 4), '-',
        SUBSTR(hexStr, 21)
    ));
END
EOF;
            $polyfill2 = <<<EOF
CREATE FUNCTION IF NOT EXISTS UUID_TO_BIN(uuid CHAR(36))
RETURNS BINARY(16)
BEGIN
    RETURN UNHEX(REPLACE(uuid, '-', ''));
END
EOF;
            self::$connection->query($polyfill1);
            self::$connection->query($polyfill2);
        }

        public static function add($type, $params, $priority=0, $runafter=0) {
            $id = self::uuidv4();
            // UUID_TO_BIN = UNHEX(REPLACE(uuid, '-', ''))
            $query = "insert into tasks values(UUID_TO_BIN(:id), :type, :params, :runafter, :priority, :state, :failures, :timestamp)";
            $sth = self::$connection->prepare($query);
            $sth->execute(array(
                ':id'        => $id,
                ':type'      => $type,
                ':params'    => json_encode($params),
                ':runafter'  => $runafter,
                ':priority'  => $priority,
                ':state'     => 'todo',
                ':failures'  => 0,
                ':timestamp' => time(),
            ));
            return $id;
        }

        public static function selectTask($type='%', $state='todo', $newState='doing') {
            self::lock();
            $task = self::next($type, $state);
            if (!$task) {
                return false;
            }
            if (self::update($task->id, $newState)) {
                $task->state = $newState;
            } else {
                self::unlock();
                return ar_error::raiseError('ar_tasks: update failed: '.var_export(self::$connection->errorInfo(),true),501);
            }
            self::unlock();
            return $task;
        }

        public static function failTask($task, $backoff, $state='todo', $failedState='failed') {
            ar_tasks::lock();
            $task->failures++;
            if ($task->failures>=$maxFailures) {
                ar_tasks::update($task->id, $failedState);
            } else {
                ar_tasks::update($task->id, $state, $task->failures, time()+$backoff);
            }
            ar_tasks::unlock();
        }

        private static function init() {
            $query = <<<EOF
create table tasks (
    id binary(16) not null,
    type varchar(32) default '' not null,
    params json,
    runafter int(11) default '0' not null,
    priority int(11) default '0' not null,
    state varchar(32) default 'todo' not null,
    failures int,
    timestamp int,
    primary key (id),
    key type (type),
    key runafter (runafter)
);
EOF;
            $result = self::$connection->query($query);
            return $result;
        }

        private static function uuidv4(){
            $data = random_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        public static function lock() {
            $query = "lock tables tasks write";
            $result = self::$connection->query($query);
            return $result;
        }

        public static function unlock() {
            $query = "unlock tables";
            $result = self::$connection->query($query);
            return $result;
        }

        private static function next($type='%', $state='todo') {
            $query = "select BIN_TO_UUID(id) as id, type, params, runafter, priority, state, failures, timestamp from tasks where type like :type and state=:state and runafter<=:runafter order by priority DESC, timestamp ASC limit 1";
            $sth = self::$connection->prepare($query);
            $sth->execute(array(
                ':type'     => $type,
                ':state'    => $state,
                ':runafter' => time(),
            ));
            $result = $sth->fetchObject();
            if ($result) {
                $result->params = json_decode($result->params, true);
            }
            return $result;
        }

        public static function update($id, $state, $failures=null, $runafter=null) {
            $values = [
                'state' => $state,
                'timestamp' => time()
            ];
            if ($failures) {
                $values['failures'] = $failures;
            }
            if ($runafter) {
                $values['runafter'] = $runafter;
            }
            foreach($values as $key => $value) {
                $names[] = "$key=:$key";
            }

            $query = "update tasks set ".implode(',',$names)." where id=UUID_TO_BIN(:id)";
            $sth = self::$connection->prepare($query);
            foreach($values as $key => $value) {
                // bindParam uses value by reference, so we can't use $value directly
                $sth->bindParam(':'.$key, $values[$key], is_string($value) ? PDO::PARAM_STR : PDO::PARAM_INT);
            }
            $sth->bindParam(':id', $id, PDO::PARAM_STR);
            $result = $sth->execute();

            $result = $sth->rowCount();
            return $result;
        }
    }

