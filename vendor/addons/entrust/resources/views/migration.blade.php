<?php echo '<?php' ?>

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class EntrustSetupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create table for storing roles
        Schema::create('{{ $entrust['tables']['roles'] }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create table for storing permissions
        Schema::create('{{ $entrust['tables']['permissions'] }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

@if ($entrust['use_teams'])
        // Create table for storing teams
        Schema::create('{{ $entrust['tables']['teams'] }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

@endif
        // Create table for associating roles to users and teams (Many To Many Polymorphic)
        Schema::create('{{ $entrust['tables']['role_user'] }}', function (Blueprint $table) {
            $table->unsignedInteger('{{ $entrust['foreign_keys']['role'] }}');
            $table->unsignedInteger('{{ $entrust['foreign_keys']['user'] }}');
            $table->string('user_type');
@if ($entrust['use_teams'])
            $table->unsignedInteger('{{ $entrust['foreign_keys']['team'] }}')->nullable();
@endif

            $table->foreign('{{ $entrust['foreign_keys']['role'] }}')->references('id')->on('{{ $entrust['tables']['roles'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
@if ($entrust['use_teams'])
            $table->foreign('{{ $entrust['foreign_keys']['team'] }}')->references('id')->on('{{ $entrust['tables']['teams'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['{{ $entrust['foreign_keys']['user'] }}', '{{ $entrust['foreign_keys']['role'] }}', 'user_type', '{{ $entrust['foreign_keys']['team'] }}']);
@else

            $table->primary(['{{ $entrust['foreign_keys']['user'] }}', '{{ $entrust['foreign_keys']['role'] }}', 'user_type']);
@endif
        });

        // Create table for associating permissions to users (Many To Many Polymorphic)
        Schema::create('{{ $entrust['tables']['permission_user'] }}', function (Blueprint $table) {
            $table->unsignedInteger('{{ $entrust['foreign_keys']['permission'] }}');
            $table->unsignedInteger('{{ $entrust['foreign_keys']['user'] }}');
            $table->string('user_type');
@if ($entrust['use_teams'])
            $table->unsignedInteger('{{ $entrust['foreign_keys']['team'] }}')->nullable();
@endif

            $table->foreign('{{ $entrust['foreign_keys']['permission'] }}')->references('id')->on('{{ $entrust['tables']['permissions'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
@if ($entrust['use_teams'])
            $table->foreign('{{ $entrust['foreign_keys']['team'] }}')->references('id')->on('{{ $entrust['tables']['teams'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['{{ $entrust['foreign_keys']['user'] }}', '{{ $entrust['foreign_keys']['permission'] }}', 'user_type', '{{ $entrust['foreign_keys']['team'] }}']);
@else

            $table->primary(['{{ $entrust['foreign_keys']['user'] }}', '{{ $entrust['foreign_keys']['permission'] }}', 'user_type']);
@endif
        });

        // Create table for associating permissions to roles (Many-to-Many)
        Schema::create('{{ $entrust['tables']['permission_role'] }}', function (Blueprint $table) {
            $table->unsignedInteger('{{ $entrust['foreign_keys']['permission'] }}');
            $table->unsignedInteger('{{ $entrust['foreign_keys']['role'] }}');

            $table->foreign('{{ $entrust['foreign_keys']['permission'] }}')->references('id')->on('{{ $entrust['tables']['permissions'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('{{ $entrust['foreign_keys']['role'] }}')->references('id')->on('{{ $entrust['tables']['roles'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['{{ $entrust['foreign_keys']['permission'] }}', '{{ $entrust['foreign_keys']['role'] }}']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('{{ $entrust['tables']['permission_user'] }}');
        Schema::dropIfExists('{{ $entrust['tables']['permission_role'] }}');
        Schema::dropIfExists('{{ $entrust['tables']['permissions'] }}');
        Schema::dropIfExists('{{ $entrust['tables']['role_user'] }}');
        Schema::dropIfExists('{{ $entrust['tables']['roles'] }}');
@if ($entrust['use_teams'])
        Schema::dropIfExists('{{ $entrust['tables']['teams'] }}');
@endif
    }
}
