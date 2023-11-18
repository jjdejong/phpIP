<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS `update_expired`');
        DB::unprepared("CREATE PROCEDURE `update_expired`()
  BEGIN
  	DECLARE vmatter_id INTEGER;
      DECLARE vexpire_date DATE;
      DECLARE done INT DEFAULT FALSE;
      DECLARE cur_expired CURSOR FOR
  		SELECT matter.id, matter.expire_date FROM matter WHERE expire_date < Now() AND dead=0;
  	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

      OPEN cur_expired;

      read_loop: LOOP
  		FETCH cur_expired INTO vmatter_id, vexpire_date;
          IF done THEN
  			LEAVE read_loop;
  		END IF;
  		INSERT IGNORE INTO `event` (code, matter_id, event_date, created_at, creator, updated_at) VALUES ('EXP', vmatter_id, vexpire_date, Now(), 'system', Now());
  	END LOOP;
  END"
        );
    }

    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS `update_expired`');
    }
};
