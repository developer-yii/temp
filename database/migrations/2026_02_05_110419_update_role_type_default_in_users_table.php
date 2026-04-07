<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateRoleTypeDefaultInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update comment to reflect new role types: 0-super_admin, 1-admin, 2-user
        DB::statement("ALTER TABLE users MODIFY COLUMN role_type TINYINT NOT NULL DEFAULT 2 COMMENT '0-super_admin, 1-admin, 2-user'");

        // Upgrade the first admin (id=1) to super admin if exists
        DB::table('users')
            // ->where('id', 1)
            ->where('role_type', User::ROLE_ADMIN)
            ->update(['role_type' => User::ROLE_SUPER_ADMIN]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Downgrade super admins back to admin
        DB::table('users')
            ->where('role_type', User::ROLE_SUPER_ADMIN)
            ->update(['role_type' => User::ROLE_ADMIN]);

        // Revert column comment
        DB::statement("ALTER TABLE users MODIFY COLUMN role_type TINYINT NOT NULL DEFAULT 2 COMMENT '1-admin, 2-user'");
    }
}
