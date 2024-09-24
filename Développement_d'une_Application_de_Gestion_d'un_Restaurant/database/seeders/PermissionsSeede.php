<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeede extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'show table']);
        Permission::create(['name' => 'store table']);
        Permission::create(['name' => 'update table']);
        Permission::create(['name' => 'delete table']);

        Permission::create(['name' => 'store category']);
        Permission::create(['name' => 'update category']);
        Permission::create(['name' => 'delete category']);

        Permission::create(['name' => 'store subcategory']);
        Permission::create(['name' => 'update subcategory']);
        Permission::create(['name' => 'delete subcategory']);

        Permission::create(['name' => 'store driver']);
        Permission::create(['name' => 'show driver']);
        Permission::create(['name' => 'update driver']);
        Permission::create(['name' => 'delete driver']);

        Permission::create(['name' => 'show contract']);
        Permission::create(['name' => 'update contract']);
        Permission::create(['name' => 'store contract']);
        Permission::create(['name' => 'delete contract']);

        Permission::create(['name' => 'update service']);
        Permission::create(['name' => 'store service']);
        Permission::create(['name' => 'delete service']);

        Permission::create(['name' => 'update item']);
        Permission::create(['name' => 'store item']);
        Permission::create(['name' => 'delete item']);

        Permission::create(['name' => 'show order']);
        Permission::create(['name' => 'update order']);
        Permission::create(['name' => 'store order']);
        Permission::create(['name' => 'delete order']);

        Permission::create(['name' => 'show delivery']);
        Permission::create(['name' => 'add driver delivery']);
        Permission::create(['name' => 'update delivery']);

        Permission::create(['name' => 'show booking']);
        Permission::create(['name' => 'update booking']);
        Permission::create(['name' => 'store booking']);
        Permission::create(['name' => 'delete booking']);

        Permission::create(['name' => 'show user']);
        Permission::create(['name' => 'delete user']);

        Permission::create(['name' => 'show role']);
        Permission::create(['name' => 'update role']);
        Permission::create(['name' => 'store role']);
        Permission::create(['name' => 'delete role']);

        Permission::create(['name' => 'show employ']);
        Permission::create(['name' => 'update employ']);
        Permission::create(['name' => 'store employ']);
        Permission::create(['name' => 'delete employ']);










        $role= Role::create(['name' => 'Super-Admin']);
        $user = Admin::create([
            'name' => 'Example Super-Admin User',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('12345678'),
        ]);
        $user->assignRole($role);


        //
    }
}
