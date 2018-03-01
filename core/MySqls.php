<?php

/**
 * 数据库操作类
 * @author:lts
 */

class Mysqls
{
    protected $link = false;//数据库连接

    /**
     * 执行mysql语句
     * @param $sql
     * @param bool $affect_num
     * @return bool|int|mysqli_result
     */
    function query($sql, $affect_num = false)
    {
        if (!$this->link) {//只有执行sql的时候才有数据库链接
            $this->link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME) or die(mysqli_connect_error());
            mysqli_query($this->link, 'set names utf8');
        }
        $res = mysqli_query($this->link, $sql);
        if ($affect_num) {
            return $res ? mysqli_affected_rows($this->link) : 0;
        }
        return $res;
    }

    /**
     * 获取一个字段
     * @param string $sql
     * @return mixed
     */
    function getOne($sql)
    {//获取单个字段数据
        $query = $this->query($sql);
        $data = mysqli_fetch_array($query, MYSQLI_NUM);
        return $data[0];
    }

    /**
     * 取出一条数据
     * @param string $sql
     * @return array|null
     */
    function getRow($sql)
    {
        $query = $this->query($sql);
        $data = mysqli_fetch_array($query, MYSQLI_ASSOC);
        return $data ? $data : array();
    }

    /**
     * 取出多条数据
     * @param string $sql
     * @return array
     */
    function getRows($sql)
    {
        $query = $this->query($sql);
        $data = array();
        while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 插入数据,debug为真返回sql
     * @param $table 数据表
     * @param array $data
     * @param bool $return
     * @param bool $debug
     * @return bool|int|mysqli_result|string
     */
    function insert($table, $data, $return = false, $debug = false)
    {
        if (!$table) {
            return false;
        }
        $fields = array();
        $values = array();
        foreach ($data as $field => $value) {
            $fields[] = '`' . $field . '`';
            $values[] = "'" . addslashes($value) . "'";
        }
        if (empty($fields) || empty($values)) {
            return false;
        }
        $sql = 'INSERT INTO `' . $table . '` 
				(' . join(',', $fields) . ') 
				VALUES (' . join(',', $values) . ')';
        if ($debug) {
            return $sql;
        }
        $query = $this->query($sql);
        return $return ? mysqli_insert_id($this->link) : $query;
    }

    /**
     * 更新数据
     * @param $table
     * @param $condition
     * @param $data
     * @param int $limit
     * @param bool $debug
     * @return bool|int|mysqli_result|string
     */
    function update($table, $condition, $data, $limit = 1, $debug = false)
    {
        if (!$table) {
            return false;
        }
        $set = array();
        foreach ($data as $field => $value) {
            $set[] = '`' . $field . '`=' . "'" . addslashes($value) . "'";
        }
        if (empty($set)) {
            return false;
        }
        $sql = 'UPDATE `' . $table . '` 
				SET ' . join(',', $set) . ' 
				WHERE ' . $condition . ' ' .
            ($limit ? 'LIMIT ' . $limit : '');
        if ($debug) {
            return $sql;
        }
        return $this->query($sql);
    }
}
