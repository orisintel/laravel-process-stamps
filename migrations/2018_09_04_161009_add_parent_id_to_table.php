<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('process-stamps.table'), function (Blueprint $table) {

            // In v1.1.2 we changed the timestamp of this migration.
            // This check prevents issues if someone already migrated with the old timestamp.
            if (! Schema::hasColumn(config('process-stamps.table'), 'parent_id')) {
                $table->unsignedInteger('parent_id')->nullable()->index()->after('hash');

                $table->foreign('parent_id')
                    ->references(config('process-stamps.columns.primary_key'))
                    ->on(config('process-stamps.table'));
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('process-stamps.table'), function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
}
