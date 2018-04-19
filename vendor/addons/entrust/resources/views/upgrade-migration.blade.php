<?php echo '<?php' ?>

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class EntrustUpgradeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
@if ($entrust['use_teams'])
        // Create table for storing teams
        Schema::create('{{ $entrust['tables']['teams'] }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('{{ $entrust['tables']['role_user'] }}', function (Blueprint $table) {
            // Drop role foreign key and primary key
            $table->dropForeign(['{{ $entrust['foreign_keys']['role'] }}']);
            $table->dropPrimary(['{{ $entrust['foreign_keys']['user'] }}', '{{ $entrust['foreign_keys']['role'] }}', 'user_type']);

            // Add {{ $entrust['foreign_keys']['team'] }} column
            $table->unsignedInteger('{{ $entrust['foreign_keys']['team'] }}')->nullable();

            // Create foreign keys
            $table->foreign('{{ $entrust['foreign_keys']['role'] }}')->references('id')->on('{{ $entrust['tables']['roles'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('{{ $entrust['foreign_keys']['team'] }}')->references('id')->on('{{ $entrust['tables']['teams'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            // Create a unique key
            $table->unique(['{{ $entrust['foreign_keys']['user'] }}', '{{ $entrust['foreign_keys']['role'] }}', 'user_type', '{{ $entrust['foreign_keys']['team'] }}']);
        });

@endif
        Schema::table('{{ $entrust['tables']['permission_user'] }}', function (Blueprint $table) {
           // Drop permission foreign key and primary key
            $table->dropForeign(['{{ $entrust['foreign_keys']['permission'] }}']);
            $table->dropPrimary(['{{ $entrust['foreign_keys']['permission'] }}', '{{ $entrust['foreign_keys']['user'] }}', 'user_type']);

            $table->foreign('{{ $entrust['foreign_keys']['permission'] }}')->references('id')->on('{{ $entrust['tables']['permissions'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

@if ($entrust['use_teams'])
            // Add {{ $entrust['foreign_keys']['team'] }} column
            $table->unsignedInteger('{{ $entrust['foreign_keys']['team'] }}')->nullable();

            $table->foreign('{{ $entrust['foreign_keys']['team'] }}')->references('id')->on('{{ $entrust['tables']['teams'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['{{ $entrust['foreign_keys']['user'] }}', '{{ $entrust['foreign_keys']['permission'] }}', 'user_type', '{{ $entrust['foreign_keys']['team'] }}']);
@else
            $table->primary(['{{ $entrust['foreign_keys']['user'] }}', '{{ $entrust['foreign_keys']['permission'] }}', 'user_type']);
@endif
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
