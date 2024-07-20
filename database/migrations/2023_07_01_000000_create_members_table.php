<?php

namespace JobMetric\Membership\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('membership.tables.member'), function (Blueprint $table) {
            $table->morphs('personable');
            /**
             * The personable field is used to store the model that the member belongs to.
             */

            $table->morphs('memberable');
            /**
             * The memberable field is used to store the model that the member belongs to.
             */

            $table->string('collection')->nullable();
            /**
             * The collection field is used to store the collection of the members.
             */

            $table->timestamp('created_at');
            /**
             * The created_at field is used to store the date and time the member was created.
             */
        });

        cache()->forget('membership');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('membership.tables.member'));

        cache()->forget('membership');
    }
};
