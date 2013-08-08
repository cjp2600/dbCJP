dbCJP
=====

dbCJP - ORM класс для Bitrix framework

### insert ######
Добавление записи в таблицу.
<pre>
$table = dbCJP::table("TABLE_NAME");
$table->name = "name";
$table->data = "data";
$table->insert();
</pre>

или

<pre>
dbCJP::table("TABLE_NAME")->insert(array(
        "name" => "name",
        "data" => "data"
    ));
</pre>

### update ######
Изменение записи в таблице.
<pre>
$table = dbCJP::table("TABLE_NAME");
$table->name  = "newname";
$table->data  = "newdata";
$table->where("id","345")->update();
</pre>

или

<pre>
dbCJP::table("TABLE_NAME")
    ->where("id","345")
    ->update(array("name"=>"newname","data"=>"newdata"));
</pre>

или

<pre>
dbCJP::table("TABLE_NAME")
    ->update(array("name"=>"newname","data"=>"newdata"),array("id"=>"345"));
</pre>

### delete ######
Удаление записи из таблици.
<pre>
$tabale = dbCJP::table("TABLE_NAME");
$tabale->id = 345;
$tabale->delete();
</pre>

или

<pre>
dbCJP::table("TABLE_NAME")
    ->where("id","345")
    ->where("name","newname")
    ->or_where("date","newdate")
    ->delete();
</pre>

или

<pre>
dbCJP::table("TABLE_NAME")
    ->where(array(
        "id"   => "345",
        "name" => "newname",
    ))
    ->limit(1)->delete();
</pre>

или

<pre>
dbCJP::table("TABLE_NAME")->delete(array("id"=>345));
</pre>

### select Примеры: ######
Выборка данных из таблици.
<pre>
$query = dbCJP::table("TABLE_NAME")->get();
</pre>

или

<pre>
$query = dbCJP::table("TABLE_NAME")
            ->like("name","Стас")
            ->not_like("fam","Иванов")
            ->get();
</pre>

или

<pre>
$query = dbCJP::table("TABLE_NAME")
            ->where("id>=",5)
            ->get();
</pre>

или

<pre>
$query = dbCJP::table("TABLE_ONE")
            ->select("
                        TABLE_ONE.*,
                        TABLE_ONE.id as RID,
                           (
                           (SELECT SUM(`value`) FROM TABLE_ONE as TR WHERE (TR.type = 'oneparam' OR TR.type = 'twoparam' OR TR.type = 'threeparam') AND TR.elid = TABLE_ONE.elid ) /
                           (SELECT COUNT(*) FROM TABLE_ONE as CTR WHERE (CTR.type = 'oneparam' OR CTR.type = 'twoparam' OR CTR.type = 'threeparam') AND CTR.elid = TABLE_ONE.elid)
                            ) as PRES,
                        TABLE_TWO.IBLOCK_ID,
                        TABLE_TWO.NAME,
                        TABLE_TWO.ID,
                        TABLE_THREE.PROPERTY_111 as data")
            ->where("type","set_text")
            ->or_where("type","oneparam")
            ->or_where("type","twoparam")
            ->or_where("type","threeparam")
            ->or_where("type","fourparam")
            ->or_where("type","fiveparam")
            ->where("TABLE_TWO.IBLOCK_ID","1")
            ->join("TABLE_TWO","TABLE_ONE.elid = TABLE_TWO.NAME ")
            ->join("TABLE_THREE","TABLE_TWO.ID = TABLE_THREE.IBLOCK_ELEMENT_ID AND TABLE_THREE.PROPERTY_111 LIKE '%".$var."%'")
            ->group_by("elid")
            ->order_by("PRES","DESC")
            ->get();
</pre>

### Вывод и обработка результата ######
<pre>
foreach ($query->result() as $row ){
    
    echo $row->name.' '.$row->fam ;
    
}
</pre>

### Вывод еденичного результата ######
<pre>
 $user = dbCJP::table("USERS")->where("id",1)->get()->row();

 echo $user->name.' '.$user->fam;
</pre>

### Вывод количества записей ######
<pre>
$users = dbCJP::table("USERS")->get();
echo $users->count_rows();
</pre>
