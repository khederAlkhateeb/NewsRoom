<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateArticlesReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'articles:report';

    /**
     * The console command description.
     */
    protected $description = 'Generate a report with the number of published articles per writer during the current month';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        // Fetch writers with their published articles count for the current month
        $writers = User::where('role', 'writer')
            ->withCount(['articles' => function ($query) use ($startOfMonth) {
                $query->where('status', 'published')
                      ->where('published_at', '>=', $startOfMonth);
            }])
            ->get();

        if ($writers->isEmpty()) {
            $this->warn("No writers found in the system.");
            return Command::FAILURE;
        }

        $reportLines = [];
        $reportLines[] = "--- Monthly Published Articles Report (" . Carbon::now()->format('F Y') . ") ---";

        //Print result in terminal and save it inside the log file
        foreach ($writers as $writer) {
            $line = "Writer: {$writer->name} (ID: {$writer->id}) - Published Articles This Month: {$writer->articles_count}";
            $reportLines[] = $line;
            
            // Print dynamically to terminal screen
            $this->line($line);
        }

        $reportLines[] = "------------------------------------------------------------";

        // Save the full structured chunk into the log file safely
        Log::channel('single')->info(implode("\n", $reportLines));

        $this->info("Success: The monthly report has been printed above and saved safely into the log file.");
        return Command::SUCCESS;
    }
}