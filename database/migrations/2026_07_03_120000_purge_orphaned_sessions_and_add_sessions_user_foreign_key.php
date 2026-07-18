<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * sessions.user_id had no foreign key, so database-driver session rows
     * survived their user's deletion as orphans. Purge the rows already
     * orphaned, then add a cascading foreign key so future account deletions
     * remove the user's session rows (guest sessions keep a null user_id).
     */
    public function up(): void
    {
        $count = DB::table('sessions')
            ->whereNotNull('user_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'sessions.user_id');
            })
            ->count();

        echo "Purging {$count} orphaned session rows.\n";

        // MySQL does not allow deleting from a table referenced in a subquery,
        // so collect IDs first then delete by primary key.
        $ids = DB::table('sessions')
            ->select('id')
            ->whereNotNull('user_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'sessions.user_id');
            })
            ->pluck('id');

        DB::table('sessions')->whereIn('id', $ids)->delete();

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Purged rows cannot be restored.
    }
};
