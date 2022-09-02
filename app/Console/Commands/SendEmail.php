<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\StandardMail;
use App\Mailer;

class SendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ice:sendemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //

        //Mail::to('arietimmerman+test@gmail.com')->send(new StandardMail('https://asdgsagd'));

        // Use blocks => https://github.com/bobthecow/mustache.php/wiki/BLOCKS-pragma

        $m = new \Mustache_Engine(
            [
                'pragmas' => [\Mustache_Engine::PRAGMA_BLOCKS],
                'partials' => [
                    'parent' => '
                        Parent! Hello {{$ planet }} 
                        planet{{/ planet }} en nu {{# button }}<button>{{ button }}</button>{{/ button }} 
                        de footer: {{$ footer }}{{/ footer }}'
                ],
            ]
        );

        // if has parent: (1) prepend template with {{<parent}} and append with {{/parent}}, at parent to options.
        //

        echo $m->render(
            '{{<parent}}{{$planet}}test 123 !{{/planet }} {{$ footer }}greetings{{/ footer }} {{/ parent }}',
            [
            'button' => ''
            ]
        );


        // echo "Done\n";
    }
}
