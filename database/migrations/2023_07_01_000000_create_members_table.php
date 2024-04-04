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
            $table->id();

            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The user_id field is used to store the user_id of the member.
             */

            $table->morphs('memberable');
            /**
             * The memberable field is used to store the model that the member belongs to.
             */

            $table->string('collection')->nullable();
            /**
             * The collection field is used to store the collection of the members.
             */

            $table->softDeletes();
            /**
             * The softDeletes trait is used to store the deleted_at field.
             */

            $table->timestamps();
            /**
             * The timestamp's trait is used to store the created_at and updated_at fields.
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
