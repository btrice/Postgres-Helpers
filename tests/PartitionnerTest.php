 #!/usr/bin/env php
 <?php 
  require dirname(__FILE__) . '/../Partitionner.php';
 // parent_table_name, check_field, start_date, number of child table, months step
 $chiltable = new Partionner("projection","start_datetime","2017-01-01",4,1);
/* $chiltable->disable_Trigger();
 $chiltable->disable_Index();
 $chiltable->disable_Trigger_function();*/
 $chiltable->generate_child_table();
 ?>