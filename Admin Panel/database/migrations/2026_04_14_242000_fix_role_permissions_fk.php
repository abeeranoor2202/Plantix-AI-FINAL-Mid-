<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE role_permissions DROP FOREIGN KEY role_permissions_role_id_foreign');
        DB::statement('ALTER TABLE role_permissions ADD CONSTRAINT role_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE role_permissions DROP FOREIGN KEY role_permissions_role_id_foreign');
        DB::statement('ALTER TABLE role_permissions ADD CONSTRAINT role_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE');
    }
};
