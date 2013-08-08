dbCJP
=====

dbCJP - ORM класс для Bitrix framework

### insert (способ №1) ######
Добавление записи в таблицу.
<pre>
$table = dbCJP::table("TABLE_NAME");
$table->name = "name";
$table->data = "data";
$table->insert();
</pre>

### insert (способ №2) ######
Добавление записи в таблицу.
<pre>
dbCJP::table("TABLE_NAME")->insert(array(
        "name" => "name",
        "data" => "data"
    ));
</pre>

### update (способ №1) ######
Изменение записи в таблице.
<pre>
$table = dbCJP::table("TABLE_NAME");
$table->name  = "newname";
$table->data  = "newdata";
$table->where("id","345")->update();
</pre>

### update (способ №2) ######
Изменение записи в таблице.
<pre>
dbCJP::table("TABLE_NAME")
    ->where("id","345")
    ->update(array("name"=>"newname","data","newdata"));
</pre>

или

<pre>
dbCJP::table("TABLE_NAME")
    ->update(array("name"=>"newname","data","newdata"),array("id"=>"345"));
</pre>
