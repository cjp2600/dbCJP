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
