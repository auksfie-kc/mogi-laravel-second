<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (app()->isLocal()) {
            // 開発環境のみレコードを追加
            Admin::factory()
                ->count(10)
                ->sequence(function ($sequence) {
                    return [
                        'name' => sprintf('admin_%02d', $sequence->index + 1),
                        'email' => sprintf('admin%02d@example.com', $sequence->index + 1),
                        'password' => Hash::make('admin'), // パスワード: admin  ※ 開発環境用のパスワードのためソース埋め込み
                        'created_at' => '2022-12-30 11:22:33',
                        'updated_at' => '2022-12-31 23:58:59',
                    ];
                })
                ->create();
    }
}
}