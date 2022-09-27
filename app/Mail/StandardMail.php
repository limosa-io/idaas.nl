<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\EmailTemplate;

class StandardMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $templateId = null,
        $data = null,
        $fallbackType = EmailTemplate::TYPE_GENERIC,
        $preferredLanguage = null
    ) {
        if ($templateId == null) {
            $template = EmailTemplate::where(['default' => true, 'type' => $fallbackType])->first();
        } else {
            $template = EmailTemplate::findOrFail($templateId);
        }

        $this->html = $template->render($data, $preferredLanguage);
        $this->subject = $template->renderSubject($data, $preferredLanguage);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // TODO: set text mail
        return $this->html;
    }
}
