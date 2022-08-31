<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tmilos\ScimFilterParser\Parser;
use Tmilos\ScimFilterParser\Mode;

class ParserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test scim parser';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $parser = new Parser(Mode::PATH());


        $parser->parse('links[id eq "2"]');


        echo "Done\n";
    }
}
