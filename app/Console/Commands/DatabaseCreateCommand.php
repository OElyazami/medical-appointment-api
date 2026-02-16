<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDOException;

class DatabaseCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:database-create-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $database = env('DB_DATABASE', 'laravel');
        
        try {
            config(['database.connections.pgsql.database' => 'postgres']);
            DB::purge('pgsql');
            DB::reconnect('pgsql');
            
            $query = "SELECT 1 FROM pg_database WHERE datname = ?";
            $exists = DB::select($query, [$database]);
            
            if (empty($exists)) {
                DB::statement("CREATE DATABASE \"{$database}\"");
                $this->info("Database '{$database}' created successfully!");
            } else {
                $this->info("Database '{$database}' already exists.");
            }
            
            config(['database.connections.pgsql.database' => $database]);
            DB::purge('pgsql');
            DB::reconnect('pgsql');
            
        } catch (PDOException $e) {
            $this->error('Database connection failed: ' . $e->getMessage());
            return 1;
        } catch (\Exception $e) {
            $this->error('Failed to create database: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    
    }
}
