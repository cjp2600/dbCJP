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
dbCJP::table("PIJEY_NOWLOOK")->insert(array(
        "name" => "name",
        "data" => "data"
    ));
</pre>
