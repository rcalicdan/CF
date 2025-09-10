<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class GeocodeClients extends Command
{
    protected $signature = 'clients:geocode 
                           {--force : Force re-geocode all clients}
                           {--limit=10 : Number of clients to process at once}';

    protected $description = 'Geocode client addresses to get coordinates';

    public function handle()
    {
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        $clients = $this->getClientsToProcess($force, $limit);

        $totalClients = Client::count();
        $clientsToProcess = $this->countClientsNeedingGeocoding();

        $this->displaySummary($totalClients, $clientsToProcess);

        if ($clientsToProcess === 0) {
            $this->showSampleClients();
            return;
        }

        $this->processClients($clients, $force);
    }

    private function getClientsToProcess(bool $force, int $limit)
    {
        $query = Client::query();

        if (!$force) {
            $query->where($this->getGeocodeWhereClause());
        }

        return $query->limit($limit)->get();
    }

    private function countClientsNeedingGeocoding(): int
    {
        return Client::where($this->getGeocodeWhereClause())->count();
    }

    private function getGeocodeWhereClause(): \Closure
    {
        return function ($q) {
            $q->whereNull('latitude')
              ->orWhereNull('longitude')
              ->orWhere('latitude', 0)
              ->orWhere('longitude', 0);
        };
    }

    private function displaySummary(int $totalClients, int $clientsToProcess): void
    {
        $this->info("Total clients in database: {$totalClients}");
        $this->info("Clients that need geocoding: {$clientsToProcess}");
    }

    private function showSampleClients(): void
    {
        $this->info('No clients found that need geocoding.');

        $this->info('Sample client coordinate data:');
        $samples = Client::select('id', 'first_name', 'last_name', 'latitude', 'longitude')
                        ->take(3)
                        ->get();

        foreach ($samples as $sample) {
            $lat = $sample->latitude ?? 'NULL';
            $lng = $sample->longitude ?? 'NULL';
            $this->line("ID: {$sample->id}, Name: {$sample->first_name} {$sample->last_name}, Lat: {$lat}, Lng: {$lng}");
        }
    }

    private function processClients($clients, bool $force): void
    {
        $this->info("Processing {$clients->count()} clients...");

        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($clients as $client) {
            try {
                $this->newLine();
                $this->info("Processing: {$client->full_name} - {$client->full_address}");

                $result = $force ? $client->forceGeocode() : $client->geocodeAddress();

                if ($result) {
                    $client->save();
                    $success++;
                    $this->info("✅ Success: Lat {$client->latitude}, Lng {$client->longitude}");
                } else {
                    $failed++;
                    $this->warn("❌ Failed to geocode: {$client->full_name}");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("❌ Error geocoding {$client->full_name}: {$e->getMessage()}");
            }

            $bar->advance();
            sleep(1);
        }

        $bar->finish();
        $this->newLine();

        $this->info("Geocoding completed!");
        $this->info("✅ Success: {$success}");
        $this->info("❌ Failed: {$failed}");
    }
}