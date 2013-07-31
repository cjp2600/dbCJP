<?php
/**
 * ActiveRecords класс для Bitrix framework
 * http://github.com/cjp2600
 *
 * Date: 16.07.13
 * Time: 15:12
 * Автор: Станислав Семёнов (cjp2600), email: cjp2600@ya.ru
 */

class dbCJP
{
    private $_table_name;
    private $_where = '';
    private $_ar_where = array();
    private $_ar_wherein = array();
    private $_ar_like = array();
    private $_dirty_fields = array();
    private $_expr_fields = array();
    private $_ar_get = array();
    private $_ar_aliased_tables = array();
    private $_ar_limit = array();
    private $_ar_join = array();
    private $_ar_sort_by = '';
    private $_sort_by    = '';
    private $_limit = '';
    private $_select = '*';
    private $_is_pagenav = FALSE;
    private $_pagenav_title = "";
    private $_cache_id = null;
    private $_pagenav_query_result = NULL;
    private $_pagenav_limit = 20;
    private $_pagenav_template = '/manager/pgn_template.php';
    private $_ar_offset = array();
    private $_count_get = NULL;

    /**
     * Конфиг с параметрами по умолчанию.
     * @var array
     */
    private $_config = array(
        'table'      => 'default_table_name',
        'engine'     => 'MyISAM',
        'cache'      => FALSE,
        'cache_time' => 3600
    );

    /**
     * Конструктор класса
     * @param null $table_name
     * @param array $data
     */
    private function __construct($table_name = null, $data = array())
    {
        if ((is_null($table_name)) && (count($data) > 0)) {
            $this->_config['table'] = (isset($data['table'])) ? $data['table'] : $this->_config['table'];
        } else {
            $this->_config['table'] = $table_name;
        }
        $this->_table_name = $this->_config['table'];
        self::__config_build($data);
    }

    /**
     * Обновляем переменные.
     */
    private function reset()
    {
        $this->_where = '';
        $this->_ar_where = array();
        $this->_ar_wherein = array();
        $this->_ar_like = array();
        $this->_dirty_fields = array();
        $this->_expr_fields = array();
        $this->_ar_get = array();
        $this->_ar_limit = array();
        $this->_limit = '';
        $this->_select = '*';
        $this->_ar_offset = array();
        $this->_count_get = NULL;
    }

    /**
     * @param $config
     * @return dbCJP
     */
    public static function init($config)
    {
        return new self(null, $config);
    }

    /**
     * @param $array
     */
    private function __config_build($array){
        if (!is_null($array) && is_array($array) && count($array)>0 ) {
            foreach ($array as $k=>$v){
                $this->_config[$k] = $v;
            }
        }
    }

    /**
     * @param $table_name
     * @return dbCJP
     */
    public static function table($table_name)
    {
        return new self($table_name, array());
    }

    /**
     * @param $value
     * @return int|string
     */
    private function escape($value)
    {
        $str ='';
        if (is_string($value)) {
            $str = "'" . self::escape_str($value) . "'";
        } elseif (is_numeric($value)) {
            $str = "'$value'";
        } elseif (is_bool($value)) {
            $str = ($value === FALSE) ? 0 : 1;
        } elseif (is_null($value)) {
            $str = 'NULL';
        }
        return $str;
    }

    /**
     * @param $str
     * @return string
     */
    private function escape_str($str)
    {
        return preg_quote($str);
    }

    /**
     * @param $data_array
     * @param $PRIMARY
     * @return mixed
     */
    private function _create($data_array, $PRIMARY)
    {
        global $DB;
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_table_name}` (";
        $sql .= "`$PRIMARY` int(11) NOT NULL AUTO_INCREMENT,";
        foreach ($data_array as $key => $val) {
            $sql .= "`$key` $val " . ",";
        }
        $sql .= "PRIMARY KEY (`$PRIMARY`) ";
        $sql .= ") ENGINE={$this->_config['engine']} AUTO_INCREMENT=1;";
        return $DB->Query($sql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
    }

    /**
     * @param $data_array
     * @param string $PRIMARY
     * @return bool|mixed
     */
    public function create($data_array, $PRIMARY = "id")
    {
        return (is_array($data_array) && count($data_array) > 0) ? self::_create($data_array, $PRIMARY) : false;
    }

    /**
     * @param null $limit
     * @param bool $is
     * @return $this
     */
    public function pagination($limit = null,$is = true){
        $this->_is_pagenav = $is;
        $this->_pagenav_limit = (!is_null($limit))? $limit : $this->_pagenav_limit;
        return $this;
    }

    /**
     * @param null $data_array
     * @param bool $is_uniq
     * @return bool
     */
    public function insert($data_array = null, $is_uniq = FALSE)
    {
        global $DB;
        if (!is_null($data_array)) {
            if ((is_array($data_array) && count($data_array) > 0)) {
                if ($is_uniq) {
                    if (self::where($data_array)->count() > 0) {
                        return FALSE;
                    }
                }
                $ID = $DB->Add($this->_table_name, $data_array);
                self::reset();
                return $ID;

            }
        } else {
            $inDA = self::get_object_rows();
            if ($inDA) {
                if ($is_uniq) {
                    if (self::where($inDA)->count() > 0) {
                        self::reset();
                        return FALSE;
                    }
                }
                $ID = $DB->Add($this->_table_name, $inDA);
                self::reset();
                return $ID;
            }
        }
        return FALSE;
    }

    /**
     * @param null $data_array
     * @param null $where
     * @return bool
     */
    public function update($data_array = null, $where = null)
    {
        global $DB;
        $strUpdate = false;
        self::_b_where();
        if (is_null($where)||!is_array($where)||count($where)==0){
            $strw = $this->_where;
        } else {
            $strw = " WHERE ";
            $strw .= self::__where_array($where);
        }
        if (!is_null($data_array)) {
                $strUpdate = self::__where_array($data_array,",");
        } else {
            $inDA = self::get_object_rows();
            if ($inDA) {
                $strUpdate = self::__where_array($inDA,",");
            }
        }
        if($strUpdate)
        {
            $strSql = "UPDATE $this->_table_name SET ".$strUpdate." {$strw} ";
            $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            self::reset();
            return true;
        }
       return false;
    }

    /**
     * @param null $where
     * @return mixed
     */
    public function delete($where = null)
    {
        global $DB;  self::_b_where();
        if (is_null($where)||!is_array($where)||count($where)==0){
            $strw = $this->_where;
        } else {
            $strw = " WHERE ";
            $strw .= self::__where_array($where);
        }
        $DB->StartTransaction();
        $res = $DB->Query("DELETE FROM {$this->_table_name} {$strw} {$this->_sort_by} {$this->_limit}", false, "File: ".__FILE__."<br>Line: ".__LINE__);
        if($res)
            $DB->Commit();
        else
            $DB->Rollback();
        return $res;
    }

    /**
     * @param $query
     * @return $this
     */
    public function select($query){
        $this->_select = $query;
        return $this;
    }

    /**
     * @param $value
     * @param string $offset
     * @return $this
     */
    public function limit($value, $offset = '')
    {
        $this->_ar_limit = (int)$value;
        if ($offset != '') {
            $this->_ar_offset = (int)$offset;
        }
        return $this;
    }

    /**
     * Set OFFSET
     * @param    integer    the offset value
     * @return    object
     */
    public function offset($offset)
    {
        $this->_ar_offset = $offset;
        return $this;
    }

    /**
     * @param $table
     * @param $cond
     * @param string $type
     * @return $this
     */
    public function join($table, $cond, $type = '')
    {
        if ($type != '')
        {
            $type = strtoupper(trim($type));

            if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER')))
            {
                $type = '';
            }
            else
            {
                $type .= ' ';
            }
        }
        self::_track_aliases($table);
        if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match))
        {
            $cond = $match[1].$match[2].$match[3];
        }
        $join = $type.'JOIN '.$table.' ON '.$cond;
        $this->_ar_join[] = $join;
        return $this;
    }

    /**
     * @param $table
     * @return mixed
     */
    protected function _track_aliases($table)
    {
        if (is_array($table))
        {
            foreach ($table as $t)
            {
                self::_track_aliases($t);
            }
            return true;
        }
        if (strpos($table, ',') !== FALSE)
        {
            return self::_track_aliases(explode(',', $table));
        }
        if (strpos($table, " ") !== FALSE)
        {
            $table = preg_replace('/ AS /i', ' ', $table);
            $table = trim(strrchr($table, " "));
            if ( ! in_array($table, $this->_ar_aliased_tables))
            {
                $this->_ar_aliased_tables[] = $table;
            }
        }
        return true;
    }

    /**
     * @param $orderby
     * @param string $direction
     * @param null $tprefix
     * @return $this
     */
    function order_by($orderby, $direction = '',$tprefix = null)
    {
        $tprefix = (is_null($tprefix))? $this->_table_name."." : $tprefix.".";
        if (strtolower($direction) == 'random')
        {
            $orderby = '';
            $direction = 'RAND()';
        }
        elseif (trim($direction) != '')
        {
            $direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), TRUE)) ? ' '.$direction : ' ASC';
        }
        if (strpos($orderby, ',') !== FALSE)
        {
            $temp = array();
            foreach (explode(',', $orderby) as $part)
            {
                $part = trim($part);
                $temp[] = $tprefix.$part;
            }
            $orderby = implode(', ', $temp);
        }
        else if ($direction != 'RAND()')
        {
            $orderby = $tprefix.$orderby;
        }

        $orderby_statement = $orderby.$direction;
        $this->_ar_sort_by = $orderby_statement;

        return $this;
    }

    /**
     * @return array
     */
    function get_column()
    {
        global $DB;
        $strSql = "SHOW COLUMNS FROM " . $this->_table_name;
        $res = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
        $colAr = array();
        while ($arRes = $res->NavNext(true, "f_")) {
            $colAr[] = $arRes['Field'];
        }
        return $colAr;
    }

    /**
     * @param $key
     * @param null $value
     * @param string $type
     * @return $this
     */
    function where($key, $value = NULL, $type = 'AND')
    {
        if (!is_array($key)) {
            $key = array($key => $value);
        }
        foreach ($key as $k => $v) {
            $prefix = (count($this->_ar_where) > 0) ? $type : '';
            if (!self::_has_operator($k)) {
                $k .= ' = ';
            }
            $v = self::escape($v);
            $this->_ar_where[] = ' ' . $prefix . ' ' . $k . $v;
        }
        return $this;
    }

    /**
     * @param $str
     * @return bool
     */
    function _has_operator($str)
    {
        $str = trim($str);
        if (!preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Строим sql для where и like
     */
    private function _b_where()
    {
        if (count($this->_ar_like) > 0) {
            $this->_ar_where[] = implode($this->_ar_like);
        }
        if (strlen($this->_ar_sort_by)>0) {
            $this->_sort_by = " ORDER BY ".$this->_ar_sort_by;
        }
        $whstr = (count($this->_ar_where) > 0) ? ' WHERE ' : '';
        $this->_where = $whstr . implode($this->_ar_where);

        if (is_numeric($this->_ar_limit)) {
            $offsetstr = (is_numeric($this->_ar_offset)) ? ", " . $this->_ar_offset : '';
            $this->_limit = " LIMIT " . $this->_ar_limit . $offsetstr;
        }
    }

    /**
     * @param null $key
     * @param null $values
     * @param bool $not
     * @param string $type
     * @return $this
     */
    function _where_in($key = NULL, $values = NULL, $not = FALSE, $type = 'AND ')
    {
        if ($key === NULL OR $values === NULL) {
            return false;
        }
        if (!is_array($values)) {
            $values = array($values);
        }
        $not = ($not) ? ' NOT' : '';
        foreach ($values as $value) {
            $this->_ar_wherein[] = self::escape($value);
        }
        $prefix = (count($this->_ar_where) == 0) ? '' : " " . $type . " ";
        $where_in = $prefix . $key . $not . " IN (" . implode(", ", $this->_ar_wherein) . ") ";
        $this->_ar_where[] = $where_in;
        $this->_ar_wherein = array();
        return $this;
    }

    /**
     * @param null $key
     * @param null $values
     * @return $this
     */
    function where_in($key = NULL, $values = NULL)
    {
        return self::_where_in($key, $values);
    }

    /**
     * @param null $key
     * @param null $values
     * @return $this
     */
    function or_where_in($key = NULL, $values = NULL)
    {
        return self::_where_in($key, $values, FALSE, 'OR');
    }

    /**
     * @param null $key
     * @param null $values
     * @return $this
     */
    function where_not_in($key = NULL, $values = NULL)
    {
        return self::_where_in($key, $values, TRUE);
    }

    /**
     * @param null $key
     * @param null $values
     * @return $this
     */
    function or_where_not_in($key = NULL, $values = NULL)
    {
        return self::_where_in($key, $values, TRUE, 'OR');
    }

    /**
     * Обработчик WHERE LIKE %S
     * @param $field
     * @param string $match
     * @param string $type
     * @param string $side
     * @param string $not
     * @return $this
     */
    private function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '')
    {
        if (!is_array($field)) {
            $field = array($field => $match);
        }
        foreach ($field as $k => $v) {
            $prefix = (count($this->_ar_like) > 0) ? ' ' . $type . ' ' : '';
            $v = self::escape_str($v);

            if ($side == 'before') {
                $like_statement = $prefix . " $k $not LIKE '%{$v}'";
            } elseif ($side == 'after') {
                $like_statement = $prefix . " $k $not LIKE '{$v}%'";
            } else {
                $like_statement = $prefix . " $k $not LIKE '%{$v}%'";
            }
            $this->_ar_like[] = $like_statement;
        }
        return $this;
    }

    /**
     * WHERE LIKE %S
     * @param $field
     * @param string $match
     * @param string $side
     * @return $this
     */
    function like($field, $match = '', $side = 'both')
    {
        return self::_like($field, $match, 'AND ', $side);
    }

    /**
     * WHERE NOT LIKE %S
     * @param $field
     * @param string $match
     * @param string $side
     * @return $this
     */
    function not_like($field, $match = '', $side = 'both')
    {
        return self::_like($field, $match, 'AND ', $side, 'NOT');
    }

    /**
     * WHERE LIKE %S OR LIKE %S
     * @param $field
     * @param string $match
     * @param string $side
     * @return $this
     */
    function or_like($field, $match = '', $side = 'both')
    {
        return self::_like($field, $match, 'OR ', $side);
    }

    /**
     * WHERE %S OR NOT %S
     * @param $field
     * @param string $match
     * @param string $side
     * @return $this
     */
    function or_not_like($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'OR ', $side, 'NOT');
    }

    /**
     * WHERE %S OR %S
     * @param $key
     * @param null $value
     * @return $this
     */
    function or_where($key, $value = NULL)
    {
        return self::where($key, $value, 'OR');
    }

    /**
     * Запрос на получение общего числа записей
     * @return int
     */
    private function count()
    {
        self::_b_where();
        global $DB;
        $strSql = "
  		SELECT COUNT(*) AS CNT
			FROM `{$this->_table_name}` {$this->_where}
		";
        $res = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
        if ($res_arr = $res->Fetch()) {
            return intval($res_arr["CNT"]);
        } else {
            return 0;
        }
    }

    /**
     * Оброботчик массива в SQL
     * @param $array
     * @param string $type
     * @return string
     */
    private function __where_array($array,$type = "AND"){
        $reAr = array();
        if (is_array($array)&&count($array)>0){
        foreach ($array as $k=>$v) {
            if (!self::_has_operator($k)) {
                $k .= ' = ';
            }
            $reAr[] = $k.self::escape($v);
        }
        return (count($reAr)>0)? implode($reAr," ".$type." ") : false;
        }
        return false;
    }

    /**
     * Обработчик
     * @return $this
     */
    public function get()
    {
        self::_b_where();
        $jonsrt = (count($this->_ar_join)>0)?implode($this->_ar_join," "):'';
        global $DB;
        $strSql = "
			SELECT {$this->_select} FROM `{$this->_table_name}` {$jonsrt} {$this->_where} {$this->_sort_by} {$this->_limit}
		";
        $this->_cache_id = md5($strSql);
            $result = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
            if ($this->_is_pagenav){
                $result->NavStart($this->_pagenav_limit);
                $this->_pagenav_query_result = $result;
            }
            $i = 0;
            while ($arRes = $result->NavNext(true, "f_")) {
                $this->_ar_get[] = (object)$arRes;
                $i++;
            }
            $this->_count_get = $i;

        return $this;
    }

    /**
     * Получаем массив объектов значений таблицы
     * @return array
     */
    public function result()
    {
        return $this->_ar_get;
    }

    /**
     * @return bool|null
     */
    public function cache_id(){
        return (!is_null($this->_cache_id))?$this->_cache_id:false;
    }

    /**
     * Печатаем постраничную навигацию
     * @param null $title
     * @param null $template
     * @return mixed
     */
    public function pagination_print($title=null,$template = null){
        if ($this->_is_pagenav){
            $this->_pagenav_template = (!is_null($template))?$template:$this->_pagenav_template;
            $this->_pagenav_title = (!is_null($title))? $title : $this->_pagenav_title;
          return $this->_pagenav_query_result->NavPrint($this->_pagenav_title,false,false,$this->_pagenav_template);
        }
    }

    /**
     * Еденичное получение данных.
     * @return mixed
     */
    public function row()
    {
        return $this->_ar_get[0];
    }

    /**
     * Функция получения общего числа записей
     * В зависимости от выборки.
     * @return int|null
     */
    public function count_rows()
    {
        return (is_null($this->_count_get)) ? self::count() : $this->_count_get;
    }

    /**
     * Преобразовываем объекты для получение полей таблицы.
     * @param $object
     * @return array
     */
    private function _object_to_array($object)
    {
        if (!is_object($object)) {
            return $object;
        }
        $array = array();
        foreach (get_object_vars($object) as $key => $val) {
            if (!is_object($val) && !is_array($val) && (in_array($key, self::get_column()))) {
                $array[$key] = $val;
            }
        }
        return $array;
    }

    /**
     * Обвертка для получение массива данных полей их объектов.
     * @return array|bool
     */
    private function get_object_rows()
    {
        $data = self::_object_to_array($this);
        return (count($data) > 0) ? $data : false;
    }

}
