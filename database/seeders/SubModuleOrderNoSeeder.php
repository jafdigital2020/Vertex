<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubModuleOrderNoSeeder extends Seeder
{
    public function run(): void
    { 
        $moduleIds = DB::table('sub_modules')->select('module_id')->distinct()->pluck('module_id');

        foreach ($moduleIds as $moduleId) { 
            $subModules = DB::table('sub_modules')
                            ->where('module_id', $moduleId)
                            ->orderBy('id')
                            ->get();
 
            foreach ($subModules as $index => $subModule) {
                DB::table('sub_modules')
                    ->where('id', $subModule->id)
                    ->update(['order_no' => $index + 1]);
            }
        }
    }
}
